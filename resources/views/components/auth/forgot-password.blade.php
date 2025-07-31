@fragment('otp-field')
    <div id="form" class="pb-4 border-b border-gray-300 space-y-2">
        <x-ui.input
            name="otp"
            type="text"
            :placeholder="__('Enter the OTP code')"
            :label="__('OTP Code')"
        />

        <div class="flex justify-between items-center w-full gap-1">
            <p class="link w-full text-center" data-on-click="{{ datastar()->get(['AuthController', 'getForgotPasswordEmailField']) }}">Back</p>

            <button
                class="btn"
                data-on-click="{{ datastar()->action(['AuthController', 'verifyOtp']) }}"
                data-attr-disabled="!($email && $otp)"
            >
                {{ __('Verify') }}
            </button>

            <button
                class="btn"
                data-on-click="{{ datastar()->action(['AuthController', 'resendOtp']) }}"
            >
                {{ __('Resend OTP') }}
            </button>
        </div>
    </div>
@endfragment

@fragment('email-field')
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
@endfragment

@fragment('password-field')
    <div id="form" class="pb-4 border-b border-gray-300 space-y-2">
        <x-ui.input
            name="password"
            type="password"
            :placeholder="__('Enter your new password')"
            :label="__('New Password')"
        />

        <x-ui.input
            name="password_confirmation"
            type="password"
            :placeholder="__('Confirm your new password')"
            :label="__('Confirm Password')"
        />

        <button
            class="btn"
            data-on-click="{{ datastar()->action(['AuthController', 'resetPassword']) }}"
            data-attr-disabled="!($email && $otp && $password && $password_confirmation)"
        >
            {{ __('Reset Password') }}
        </button>
    </div>
@endfragment
