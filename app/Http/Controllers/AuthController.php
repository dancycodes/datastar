<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use App\Traits\DatastarHelpers;

class AuthController extends Controller
{
    use DatastarHelpers;

    public function __construct(
        private OtpService $otpService
    ) {
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function register()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, $this->rules());

        $user = User::create($validated);

        $this->otpService->generateAndSendOtp($user, OtpService::TYPE_EMAIL);

        auth()->login($user);

        $this->addLocation(route('verification.notice'));

        return $this->sendEvents();
    }

    public function logout()
    {
        auth()->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->addLocation(route('home'));

        return $this->sendEvents();
    }

    public function login()
    {
        $signals = $this->readSignals();

        $credentials = $this->validate($signals, [
            'email' => 'required|email|max:100',
            'password' => 'required|string',
        ]);

        $success = auth()->attempt($credentials, $signals['remember'] ?? false);

        if ($success) {
            $this->addLocation(route('todos.index'));
        } else {
            $this->addToastify('error', __('Invalid credentials.'));
        }

        return $this->sendEvents();
    }

    public function sendOtp()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'email' => 'required|email|max:100',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            // Check if user already has a valid OTP
            if ($this->otpService->hasValidOtp($user, OtpService::TYPE_PASSWORD)) {
                $this->addPatchElements(view('components.auth.forgot-password')->fragment('otp-field'));
                $this->addPatchSignals($validated);
                $this->addToastify('info', __('An OTP code has already been sent to your email.'));
                return $this->sendEvents();
            }

            // Generate and send new OTP
            $result = $this->otpService->generateAndSendOtp($user, OtpService::TYPE_PASSWORD);
        } else {
            // Still show success for security (don't reveal if email exists)
            $result = [
                'success' => true,
                'message' => __('If an account with this email exists, an OTP code has been sent.')
            ];
        }

        $this->addPatchElements(view('components.auth.forgot-password')->fragment('otp-field'));
        $this->addPatchSignals($validated);
        $this->addToastify('success', $result['message']);

        return $this->sendEvents();
    }

    public function verifyOtp()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'email' => 'required|email|max:100',
            'otp' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $result = $this->otpService->verifyOtp($user, $validated['otp'], OtpService::TYPE_PASSWORD);

            if ($result['success']) {
                $this->addPatchElements(view('components.auth.forgot-password')->fragment('password-field'));
                $this->addPatchSignals($validated);
                $this->addToastify('success', $result['message']);
            } else {
                $this->addToastify('error', $result['message']);
            }
        } else {
            $this->addToastify('error', __('Invalid or expired OTP.'));
        }

        return $this->sendEvents();
    }

    public function resendOtp()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'email' => 'required|email|max:100',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $result = $this->otpService->resendOtp($user, OtpService::TYPE_PASSWORD);
            $this->addToastify('success', $result['message']);
        } else {
            // Still show success for security
            $this->addToastify('success', __('If an account with this email exists, a new OTP code has been sent.'));
        }

        return $this->sendEvents();
    }

    public function getForgotPasswordEmailField()
    {
        $this->addPatchElements(view('components.auth.forgot-password')->fragment('email-field'));

        return $this->sendEvents();
    }

    public function resetPassword()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:8|confirmed',
            'otp' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            // Verify OTP one more time before password reset
            $otpResult = $this->otpService->verifyOtp($user, $validated['otp'], OtpService::TYPE_PASSWORD);

            if ($otpResult['success']) {
                // Update password
                $user->update(['password' => $validated['password']]);

                // Clean up password OTPs
                $user->otps()->where('type', OtpService::TYPE_PASSWORD)->delete();

                $this->addToastify('success', __('Password reset successfully! You can now login with your new password.'));
                $this->addLocation(route('login'));
            } else {
                $this->addToastify('error', $otpResult['message']);
            }
        } else {
            $this->addToastify('error', __('Something went wrong.'));
        }

        return $this->sendEvents();
    }

    public function sendEmailVerificationOtp()
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            $this->addToastify('info', __('Your email is already verified.'));
            return $this->sendEvents();
        }

        // Check if user already has a valid OTP
        if ($this->otpService->hasValidOtp($user, OtpService::TYPE_EMAIL)) {
            $this->addToastify('info', __('An email verification code has already been sent to your email.'));
            return $this->sendEvents();
        }

        // Generate and send email verification OTP
        $result = $this->otpService->generateAndSendOtp($user, OtpService::TYPE_EMAIL);

        $this->addToastify('success', $result['message']);

        return $this->sendEvents();
    }

    public function verifyEmailOtp()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'otp' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            $this->addToastify('info', __('Your email is already verified.'));
            $this->addLocation(route('todos.index'));
            return $this->sendEvents();
        }

        // Verify the OTP
        $otpResult = $this->otpService->verifyOtp($user, $validated['otp'], OtpService::TYPE_EMAIL);

        if ($otpResult['success']) {
            // Complete email verification
            $this->otpService->completeEmailVerification($user);

            $this->addLocation(route('todos.index'));
        } else {
            $this->addToastify('error', $otpResult['message']);
        }

        return $this->sendEvents();
    }

    public function resendEmailVerificationOtp()
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            $this->addToastify('info', __('Your email is already verified.'));
            return $this->sendEvents();
        }

        // Resend email verification OTP
        $result = $this->otpService->resendOtp($user, OtpService::TYPE_EMAIL);

        $this->addToastify('success', $result['message']);

        return $this->sendEvents();
    }
}