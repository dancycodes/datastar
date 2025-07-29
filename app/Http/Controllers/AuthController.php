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

        return $this->addLocation(route('verification.notice'))->sendEvents();
    }

    public function logout()
    {
        auth()->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return $this->addLocation(route('home'))->sendEvents();
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
                return $this->addPatchElements(view('components.auth.forgot-password')->fragment('otp-field'))
                    ->addPatchSignals($validated)
                    ->addToastify('info', __('An OTP code has already been sent to your email.'))
                    ->sendEvents();
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

        return $this->addPatchElements(view('components.auth.forgot-password')->fragment('otp-field'))
            ->addPatchSignals($validated)
            ->addToastify('success', $result['message'])
            ->sendEvents();
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
                $this->addPatchElements(view('components.auth.forgot-password')->fragment('password-field'))
                    ->addPatchSignals($validated)
                    ->addToastify('success', $result['message']);
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
        return $this->addPatchElements(view('components.auth.forgot-password')->fragment('email-field'))->sendEvents();
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

                $this->addToastify('success', __('Password reset successfully! You can now login with your new password.'))
                    ->addLocation(route('login'));
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
            return $this->addToastify('info', __('Your email is already verified.'))->sendEvents();
        }

        // Check if user already has a valid OTP
        if ($this->otpService->hasValidOtp($user, OtpService::TYPE_EMAIL)) {
            return $this->addToastify('info', __('An email verification code has already been sent to your email.'))->sendEvents();
        }

        // Generate and send email verification OTP
        $result = $this->otpService->generateAndSendOtp($user, OtpService::TYPE_EMAIL);

        return $this->addToastify('success', $result['message'])->sendEvents();
    }

    public function verifyEmailOtp()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'otp' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return $this->addToastify('info', __('Your email is already verified.'))
                ->addLocation(route('todos.index'))
                ->sendEvents();
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
            return $this->addToastify('info', __('Your email is already verified.'))->sendEvents();
        }

        // Resend email verification OTP
        $result = $this->otpService->resendOtp($user, OtpService::TYPE_EMAIL);

        return $this->addToastify('success', $result['message'])->sendEvents();
    }
}