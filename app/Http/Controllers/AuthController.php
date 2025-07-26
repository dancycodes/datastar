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

    public function getLoginForm()
    {
        $this->patchElements(view('components.auth.login-form'));
    }

    public function register()
    {
        $signals = $this->readSignals();

        $validated = $this->validate($signals, $this->rules());

        $user = \App\Models\User::create($validated);

        auth()->login($user);

        $this->location(route('email.verify'));

        $this->toastify('success', __('Registration successful! Please verify your email address.'));
    }

    public function logout()
    {

        auth()->logout();

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
}
