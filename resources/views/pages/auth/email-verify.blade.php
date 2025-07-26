<?php
    use function Laravel\Folio\{name, middleware};

    name('email.verify');

    middleware('auth');
?>

<x-layouts.app>

    <div class="container mx-auto">
        <button 
            class="btn"
            data-on-click="{{ datastar()->post(['AuthController','logout']) }}"
        >
            {{ __('Logout') }}
        </button>
    </div>
</x-layouts.app>
