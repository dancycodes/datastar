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

        $this->location(route('email.verify'));
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

        if (auth()->attempt($credentials, $signals['remember'] ?? false)) {
            $this->location(route('home'));
        } else {
            $this->toastify('error', __('Invalid credentials.'));
        }
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
                    $this->toastify('info', __('An OTP code has already been sent to your email.'));

                    $this->patchElements(view('components.auth.forgot-password')->fragment('otp-field'));

                    $this->patchSignals([
                        'email' => $validated['email'],
                    ]);

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

        $this->toastify('success', __('OTP code sent to your email.'));

        $this->patchElements(view('components.auth.forgot-password')->fragment('otp-field'));

        $this->patchSignals([
            'email' => $validated['email'],
        ]);
    }
}
