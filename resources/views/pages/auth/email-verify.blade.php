<?php
    use function Laravel\Folio\{name, middleware, render};
    use Illuminate\View\View;

    name('verification.notice');
    middleware('auth');

    render(function (View $view) {
        // Redirect to todos.index if email is already verified
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('todos.index');
        }
        
        return $view;
    });
?>

<x-layouts.app>
    <div 
        class="flex justify-center"
    >   
        <x-ui.card>
            <x-ui.image-title
                :src="asset('images/datastar.jpg')"
                :title="__('Email Verification')"
            />

            <div class="space-y-4">
                <div class="text-center space-y-2 p-2">
                    <p class="text-sm text-gray-600">
                        {{ __('We\'ve sent a 6-digit verification code to') }}<br>
                        <span class="font-medium">{{ auth()->user()->email }}</span>
                    </p>
                </div>

                <div class="pb-4 border-b border-gray-300 space-y-4">
                    <x-ui.input
                        name="otp"
                        type="text"
                        :placeholder="__('Enter 6-digit code')"
                        :label="__('Verification Code')"
                    />

                    <button
                        class="btn"
                        data-on-click="{{ datastar()->post(['AuthController', 'verifyEmailOtp']) }}"
                        data-attr-disabled="!($otp && $otp.length === 6)"
                    >
                        {{ __('Verify Email') }}
                    </button>
                </div>

                <div class="flex flex-col space-y-3">
                    <div class="text-center text-sm text-gray-600">
                        {{ __('Didn\'t receive the code?') }}
                    </div>
                    
                    <div class="flex gap-2">
                        <button
                            class="btn flex-1"
                            data-on-click="{{ datastar()->post(['AuthController', 'resendEmailVerificationOtp']) }}"
                        >
                            {{ __('Resend Code') }}
                        </button>
                        
                        <button
                            class="btn bg-gray-500 hover:bg-gray-600 flex-1"
                            data-on-click="{{ datastar()->post(['AuthController', 'sendEmailVerificationOtp']) }}"
                        >
                            {{ __('Send New Code') }}
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <button 
                            class="link text-sm"
                            data-on-click="{{ datastar()->post(['AuthController','logout']) }}"
                        >
                            {{ __('Sign out and use different email') }}
                        </button>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm text-yellow-800">
                            <p class="font-medium">{{ __('Security Notice') }}</p>
                            <p class="mt-1">{{ __('The verification code expires in 10 minutes. If you don\'t receive it, check your spam folder.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>