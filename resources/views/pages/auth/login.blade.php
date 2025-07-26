<?php
    use function Laravel\Folio\{name, middleware};

    name('login');

    middleware('guest');
?>

<x-layouts.app>
    <div class="flex items-center justify-center">   
        <x-ui.card id="auth-form">
            <x-ui.image-title
                :src="asset('images/datastar.jpg')"
                :title="__('Login')"
            />

            <div class="pb-4 border-b border-gray-300 space-y-2">
                <x-ui.input
                    name="email"
                    type="email"
                    :placeholder="__('Enter your email address')"
                    :label="__('Email Address')"
                    field_validates_controller="AuthController"
                />

                <x-ui.input
                    name="password"
                    type="password"
                    :placeholder="__('Enter your password')"
                    :label="__('Password')"
                    field_validates_controller="AuthController"
                />

                <button
                    class="btn"
                    data-on-click="{{ datastar()->post(['AuthController', 'login']) }}"
                    data-attr-disabled="!($email && $password)"
                >
                    {{ __('Login') }}
                </button>
            </div>

            <div class="flex justify-between items-center w-full">
                <a class="link" href="{{ route('register') }}">Create An account</a>
                <a class="link" href="{{ route('forgot-password') }}">Forgot Password?</a>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>