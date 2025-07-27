<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\DatastarHelpers;

class AuthController extends Controller
{
    use DatastarHelpers;

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

        $user = \App\Models\User::create($validated);

        // Authentication must happen before the response is sent
        auth()->login($user);

        return $this->getStreamedResponse(function() {
            $this->location(route('email.verify'));
        });
    }

    public function logout()
    {
        auth()->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->location(route('login'));
    }

    public function login()
    {
        $signals = $this->readSignals();

        $credentials = $this->validate($signals, [
            'email' => 'required|email|max:100',
            'password' => 'required|string',
        ]);

        // Authentication must happen before the response is sent
        $success =  auth()->attempt($credentials, $signals['remember'] ?? false);

        return $this->getStreamedResponse(function() use ($success) {
            if ($success) {
                $this->location(route('home'));
            } else {
                $this->toastify('error', __('Invalid credentials.'));
            }
        });
    }

    public function sendOtp()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'email' => 'required|email|max:100',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            // Check if the user has an otp of type 'password'
            $existingOtp = $user->otps()->where('type', 'password')->first();
            if ($existingOtp) {
                if ($existingOtp->expires_at > now()) {
                    $this->patchElements(view('components.auth.forgot-password')->fragment('otp-field'));

                    $this->patchSignals($validated);

                    $this->toastify('info', __('An OTP code has already been sent to your email.'));

                    return;
                } else {
                    $existingOtp->delete();
                }
            }

            // Logic to send OTP code to the user's email
            $otp = rand(100000, 999999);

            $user->otps()->create([
                'type' => 'password',
                'otp' => $otp,
                'expires_at' => now()->addMinutes(10),
            ]);

            $user->notifyNow(new \App\Notifications\Auth\SendOTPPasswordReset($otp));
        }

        $this->patchElements(view('components.auth.forgot-password')->fragment('otp-field'));

        $this->patchSignals($validated);

        $this->toastify('success', __('OTP code sent to your email.'));
    }

    public function verifyOtp()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'email' => 'required|email|max:100',
            'otp' => 'required',
        ]);

        $user = User::where('email', $validated['email'])
            ->whereHas('otps', function ($query) use ($validated) {
                $query->where('type', 'password')
                    ->where('otp', $validated['otp'])
                    ->where('expires_at', '>', now());
            })
            ->first();

        if ($user) {
            $this->patchElements(view('components.auth.forgot-password')->fragment('password-field'));

            $this->patchSignals($validated);

            $this->toastify('success', __('OTP verified successfully. You can now reset your password.'));
        } else {
            $this->toastify('error', __('Invalid or expired OTP.'));
        }
    }

    public function resendOtp()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'email' => 'required|email|max:100',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            // Check if the user has an otp of type 'password'
            $existingOtp = $user->otps()->where('type', 'password')->first();
            if ($existingOtp) {
                $existingOtp->delete();
            }

            // Logic to resend OTP code to the user's email
            $otp = rand(100000, 999999);

            $user->otps()->create([
                'type' => 'password',
                'otp' => $otp,
                'expires_at' => now()->addMinutes(10),
            ]);

            $user->notifyNow(new \App\Notifications\Auth\SendOTPPasswordReset($otp));
        }

        $this->toastify('success', __('OTP code resent to your email.'));
    }

    public function getForgotPasswordEmailField()
    {
        $this->patchElements(view('components.auth.forgot-password')->fragment('email-field'));
    }

    public function resetPassword()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, [
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:8|confirmed',
            'otp' => 'required',
        ]);

        $user = User::where('email', $validated['email'])
            ->whereHas('otps', function ($query) use ($validated) {
                $query->where('type', 'password')
                    ->where('otp', $validated['otp']);
            })
            ->first();

        if ($user) {
            $user->update(['password' => $validated['password']]);

            $this->location(route('login'));
        } else {
            $this->toastify('error', __('Something went wrong.'));
        }
    }
}
