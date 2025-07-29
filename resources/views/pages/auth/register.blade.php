<?php
    use function Laravel\Folio\{name, middleware};

    name('register');

    middleware('guest');
?>

<x-layouts.app>
    <div class="flex items-center justify-center">
        <x-ui.card>
            <x-ui.image-title
                :src="asset('images/datastar.jpg')"
                :title="__('Register')"
            />

            <div class="pb-4 border-b border-gray-300 space-y-2">
                <x-ui.input
                    name="name"
                    type="text"
                    :placeholder="__('Enter your name')"
                    :label="__('Name')"
                    field_validates_controller="AuthController"
                />

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

                <x-ui.input
                    name="password_confirmation"
                    type="password"
                    :placeholder="__('Confirm your password')"
                    :label="__('Confirm Password')"
                />

                <button
                    class="btn"
                    data-on-click="{{ datastar()->post(['AuthController', 'register']) }}"
                    data-attr-disabled="!($name && $email && $password && $password_confirmation)"
                >
                    {{ __('Register') }}
                </button>
            </div>

            <a class="link w-full text-center" href="{{ route('login') }}">Sign In</a>
        </x-ui.card>
    </div>
</x-layouts.app>
