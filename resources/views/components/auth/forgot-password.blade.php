@fragment('otp-field')
    <div id="form" class="pb-4 border-b border-gray-300 space-y-2">
        <x-ui.input
            name="otp"
            type="text"
            :placeholder="__('Enter the OTP code')"
            :label="__('OTP Code')"
        />

        <div class="flex justify-between items-center w-full">
            <button
            class="btn"
            data-on-click="{{ datastar()->post(['AuthController', 'verifyOtp']) }}"
            data-attr-disabled="!($email && $otp)"
        >
            {{ __('Verify OTP code') }}
        </button>
        </div>
    </div>
@endfragment
