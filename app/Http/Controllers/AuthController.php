<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use App\Traits\DatastarHelpers;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function register(): StreamedResponse
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, $this->rules());

        $user = User::create($validated);

        $this->otpService->generateAndSendOtp($user, OtpService::TYPE_EMAIL);

        auth()->login($user);

        return $this->location(route('verification.notice'))->getEventStream();
    }

    public function logout(): StreamedResponse
    {
        auth()->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return $this->location(route('home'))->getEventStream();
    }

    public function login(): StreamedResponse
    {
        $signals = $this->readSignals();

        $credentials = $this->validate($signals, [
            'email' => 'required|email|max:100',
            'password' => 'required|string',
        ]);

        $success = auth()->attempt($credentials, $signals['remember'] ?? false);

        if ($success) {
            $this->location(route('todos.index'));
        } else {
            $this->toastify('error', __('Invalid credentials.'));
        }

        return $this->getEventStream();
    }

    public function sendOtp(): StreamedResponse
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'email' => 'required|email|max:100',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            // Check if user already has a valid OTP
            if ($this->otpService->hasValidOtp($user, OtpService::TYPE_PASSWORD)) {
                return $this->patchElements(view('components.auth.forgot-password')->fragment('otp-field'))
                    ->patchSignals($validated)
                    ->toastify('info', __('An OTP code has already been sent to your email.'))
                    ->getEventStream();
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

        return $this->patchElements(view('components.auth.forgot-password')->fragment('otp-field'))
            ->patchSignals($validated)
            ->toastify('success', $result['message'])
            ->getEventStream();
    }

    public function verifyOtp(): StreamedResponse
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
                $this->patchElements(view('components.auth.forgot-password')->fragment('password-field'))
                    ->patchSignals($validated)
                    ->toastify('success', $result['message']);
            } else {
                $this->toastify('error', $result['message']);
            }
        } else {
            $this->toastify('error', __('Invalid or expired OTP.'));
        }

        return $this->getEventStream();
    }

    public function resendOtp(): StreamedResponse
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'email' => 'required|email|max:100',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $result = $this->otpService->resendOtp($user, OtpService::TYPE_PASSWORD);
            $this->toastify('success', $result['message']);
        } else {
            // Still show success for security
            $this->toastify('success', __('If an account with this email exists, a new OTP code has been sent.'));
        }

        return $this->getEventStream();
    }

    public function getForgotPasswordEmailField(): StreamedResponse
    {
        return $this->patchElements(view('components.auth.forgot-password')->fragment('email-field'))->getEventStream();
    }

    public function resetPassword(): StreamedResponse
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

                $this->toastify('success', __('Password reset successfully! You can now login with your new password.'))
                    ->location(route('login'));
            } else {
                $this->toastify('error', $otpResult['message']);
            }
        } else {
            $this->toastify('error', __('Something went wrong.'));
        }

        return $this->getEventStream();
    }

    public function sendEmailVerificationOtp(): StreamedResponse
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return $this->toastify('info', __('Your email is already verified.'))->getEventStream();
        }

        // Check if user already has a valid OTP
        if ($this->otpService->hasValidOtp($user, OtpService::TYPE_EMAIL)) {
            return $this->toastify('info', __('An email verification code has already been sent to your email.'))->getEventStream();
        }

        // Generate and send email verification OTP
        $result = $this->otpService->generateAndSendOtp($user, OtpService::TYPE_EMAIL);

        return $this->toastify('success', $result['message'])->getEventStream();
    }

    public function verifyEmailOtp(): StreamedResponse
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'otp' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return $this->toastify('info', __('Your email is already verified.'))
                ->location(route('todos.index'))
                ->getEventStream();
        }

        // Verify the OTP
        $otpResult = $this->otpService->verifyOtp($user, $validated['otp'], OtpService::TYPE_EMAIL);

        if ($otpResult['success']) {
            // Complete email verification
            $this->otpService->completeEmailVerification($user);

            $this->location(route('todos.index'));
        } else {
            $this->toastify('error', $otpResult['message']);
        }

        return $this->getEventStream();
    }

    public function resendEmailVerificationOtp(): StreamedResponse
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return $this->toastify('info', __('Your email is already verified.'))
                ->getEventStream();
        }

        // Resend email verification OTP
        $result = $this->otpService->resendOtp($user, OtpService::TYPE_EMAIL);

        return $this->toastify('success', $result['message'])
            ->getEventStream();
    }
}
