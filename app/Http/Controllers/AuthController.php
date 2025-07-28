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

        auth()->login($user);

        $this->addLocation(route('email.verify'));

        return $this->sendEvents();
    }

    public function logout()
    {
        auth()->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->addLocation(route('login'));

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
            $this->addLocation(route('home'));
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
            // Check if the user has an otp of type 'password'
            $existingOtp = $user->otps()->where('type', 'password')->first();
            if ($existingOtp) {
                if ($existingOtp->expires_at > now()) {
                    $this->addPatchElements(view('components.auth.forgot-password')->fragment('otp-field'));
                    $this->addPatchSignals($validated);
                    $this->addToastify('info', __('An OTP code has already been sent to your email.'));

                    return $this->sendEvents();
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

        $this->addPatchElements(view('components.auth.forgot-password')->fragment('otp-field'));
        $this->addPatchSignals($validated);
        $this->addToastify('success', __('OTP code sent to your email.'));

        return $this->sendEvents();
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
            $this->addPatchElements(view('components.auth.forgot-password')->fragment('password-field'));
            $this->addPatchSignals($validated);
            $this->addToastify('success', __('OTP verified successfully. You can now reset your password.'));
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

        $this->addToastify('success', __('OTP code resent to your email.'));

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

        $user = User::where('email', $validated['email'])
            ->whereHas('otps', function ($query) use ($validated) {
                $query->where('type', 'password')
                    ->where('otp', $validated['otp']);
            })
            ->first();

        if ($user) {
            $user->update(['password' => $validated['password']]);
            $this->addLocation(route('login'));
        } else {
            $this->addToastify('error', __('Something went wrong.'));
        }

        return $this->sendEvents();
    }
}