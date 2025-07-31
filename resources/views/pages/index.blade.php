<?php
    use function Laravel\Folio\{name};

    name('home');
?>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Datastar Demo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    <div id="toast-notifier" class="fixed top-5 right-5 z-50 space-y-3 max-w-sm w-full"></div>

    <!-- Main Content -->
    <main class="w-full max-w-4xl mx-auto p-4 space-y-8">
        <!-- Top Navigation & Logo -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 mb-12">
            <div class="flex items-center space-x-3">
                <a href="/">
                    <img src="{{ asset('images/datastar.jpg') }}" alt="" class="rounded-full shadow-lg size-16 hover:scale-105 transition-transform">
                </a>
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Datastar in Laravel</h1>
                    <p class="text-sm text-gray-600">Reactive web apps without JavaScript frameworks</p>
                </div>
            </div>

            <!-- Auth Navigation -->
            <nav class="flex flex-wrap items-center justify-center gap-3">
                @auth
                    <a href="{{ route('todos.index') }}" class="link">
                        {{ __('My Todos') }}
                    </a>
                    <button
                        class="btn !w-auto !px-3 !py-1.5 !text-sm"
                        data-on-click="{{ datastar()->action(['AuthController','logout']) }}"
                    >
                        {{ __('Logout') }}
                    </button>
                @else
                    <a href="{{ route('todos.index') }}" class="link">
                        {{ __('Try Demo') }}
                    </a>
                    <a href="{{ route('login') }}" class="link">
                        {{ __('Login') }}
                    </a>
                    <a href="{{ route('register') }}" class="btn !w-auto !px-3 !py-1.5 !text-sm">
                        {{ __('Sign Up') }}
                    </a>
                @endauth
            </nav>
        </div>

        <!-- Hero Section -->
        <div class="text-center space-y-6 mb-12">
            <div class="space-y-4">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900">
                    Datastar + Laravel
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Server-sent events meet Laravel elegance. Build reactive UIs with zero JavaScript complexity.
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4">
                @auth
                    <a href="{{ route('todos.index') }}" class="btn !w-auto !px-6">
                        {{ __('View Your Todos') }}
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn !w-auto !px-6">
                        {{ __('Get Started') }}
                    </a>
                    <a href="{{ route('todos.index') }}" class="btn !w-auto !px-6 !bg-gray-600 hover:!bg-gray-700">
                        {{ __('Try Demo') }}
                    </a>
                @endauth
            </div>
        </div>

        <!-- Feature Cards -->
        <div class="grid md:grid-cols-3 gap-6 mb-12">
            <div class="p-4 bg-white rounded shadow space-y-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Real-time with SSE</h3>
                    <p class="text-gray-600 text-sm">Server-sent events power instant updates. No WebSocket complexity, just Laravel magic.</p>
                </div>
            </div>

            <div class="p-4 bg-white rounded shadow space-y-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Blade-Driven</h3>
                    <p class="text-gray-600 text-sm">Write reactive UIs in familiar Blade templates. Controllers stay in control.</p>
                </div>
            </div>

            <div class="p-4 bg-white rounded shadow space-y-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">data-* Attributes</h3>
                    <p class="text-gray-600 text-sm">Simple HTML attributes trigger server actions. Reactive without React.</p>
                </div>
            </div>
        </div>

        <!-- Tech Stack -->
        <div class="p-4 bg-white rounded shadow space-y-4 mb-12">
            <div class="text-center space-y-6">
                <h3 class="text-2xl font-semibold text-gray-900">Built With</h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="text-center space-y-2">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto">
                            <span class="text-blue-600 font-bold text-sm">DS</span>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 text-sm">Datastar</div>
                            <div class="text-xs text-gray-500">Hypermedia Framework</div>
                        </div>
                    </div>

                    <div class="text-center space-y-2">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mx-auto">
                            <span class="text-red-600 font-bold text-sm">L12</span>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 text-sm">Laravel 12</div>
                            <div class="text-xs text-gray-500">Backend Framework</div>
                        </div>
                    </div>

                    <div class="text-center space-y-2">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto">
                            <span class="text-green-600 font-bold text-sm">F</span>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 text-sm">Folio</div>
                            <div class="text-xs text-gray-500">File-based Routes</div>
                        </div>
                    </div>

                    <div class="text-center space-y-2">
                        <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center mx-auto">
                            <span class="text-cyan-600 font-bold text-sm">T4</span>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 text-sm">Tailwind 4</div>
                            <div class="text-xs text-gray-500">CSS Framework</div>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <p class="text-sm text-gray-600 max-w-2xl mx-auto">
                        Experience reactive web development the Laravel way. Server-driven, hypermedia-powered, developer-friendly.
                        @auth
                            <a href="{{ route('todos.index') }}" class="text-blue-600 hover:text-blue-700 font-medium">Start building →</a>
                        @else
                            <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-medium">Get started →</a>
                        @endauth
                    </p>
                </div>
            </div>
        </div>

        <!-- Documentation Links -->
        <div class="p-4 bg-white rounded shadow space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 text-center">Learn More</h3>
            <div class="flex flex-wrap items-center justify-center gap-6">
                <a href="https://github.com/putyourlightson/laravel-datastar" target="_blank" class="link text-sm">
                    Laravel-Datastar Docs
                </a>
                <a href="https://data-star.dev" target="_blank" class="link text-sm">
                    Datastar Framework
                </a>
                <a href="https://laravel.com/docs/folio" target="_blank" class="link text-sm">
                    Laravel Folio
                </a>
                <a href="https://laravel.com" target="_blank" class="link text-sm">
                    Laravel Framework
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="max-w-4xl mx-auto px-4 py-8 mt-16 border-t">
        <div class="text-center space-y-4">
            <div class="flex items-center justify-center space-x-2">
                <span class="text-sm text-gray-600">Built with ❤️ by</span>
                <span class="text-sm font-medium text-gray-900">DancyCodes</span>
            </div>
            <p class="text-xs text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}. A demonstration of reactive Laravel development.
            </p>
        </div>
    </footer>
</body>
</html>
