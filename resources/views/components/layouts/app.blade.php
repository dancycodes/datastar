<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="bg-light text-sm"
>
<div id="toast-notifier" class="fixed top-5 right-5 z-50 space-y-3 max-w-sm w-full"></div>
    <div id="app">
        <!-- Main Content -->
        <main class="w-full my-8 p-4">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top w-full">
            <div class="w-full py-3">
                <p class="text-muted mb-0 text-center">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
