<?php
    use function Laravel\Folio\{name, middleware};

    name('forgot-password');

    middleware('guest');
?>

<x-layouts.app>
    <div class="flex items-center justify-center">
        <x-ui.card>
            <x-ui.image-title
                :src="asset('images/datastar.jpg')"
                :title="__('Forgot Password')"
            />

            <div id="form" class="pb-4 border-b border-gray-300 space-y-2">
                <x-ui.input
                    name="email"
                    type="email"
                    :placeholder="__('Enter your email address')"
                    :label="__('Email Address')"
                    field_validates_controller="AuthController"
                />

                <button
                    class="btn"
                    data-on-click="{{ datastar()->action(['AuthController', 'sendOtp']) }}"
                    data-attr-disabled="!($email)"
                >
                    {{ __('Send OTP code') }}
                </button>
            </div>

            <a class="link w-full text-center" href="{{ route('login') }}">Sign In</a>
        </x-ui.card>
    </div>
</x-layouts.app>
