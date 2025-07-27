
<x-layouts.app>
    <div class="flex items-center justify-center">
        <x-ui.card>
            <x-ui.image-title
                :src="asset('images/datastar.jpg')"
                :title="__('Login')"
            />

            <form method="POST" action="test/login">
                @csrf
                <input name="email">
                <input name="password">
                <button
                    class="btn"
                >
                    {{ __('Login') }}
                </button>
            </form>
            <div class="flex justify-between items-center w-full">
                <a class="link" href="{{ route('register') }}">Create An account</a>
                <a class="link" href="{{ route('forgot-password') }}">Forgot Password?</a>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
