<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Bolsa de Empleo ITSC') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo-itsc.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <a href="#main-content" class="sr-only z-50 rounded-md bg-white px-4 py-2 text-sm font-semibold text-[#042C53] shadow focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:ring-2 focus:ring-[#A32D2D] focus:ring-offset-2">
            Saltar al contenido
        </a>
        <main id="main-content" tabindex="-1" class="page-in min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 sm:py-0 {{ request()->routeIs('login') ? 'bg-slate-100' : 'bg-gray-100' }}">
            <div>
                <a href="/">
                    <x-application-logo class="{{ request()->routeIs('login') ? 'h-36 w-36' : 'h-12 w-12' }}" />
                </a>
            </div>

            <div class="w-full mt-6 {{ request()->routeIs('login') ? 'sm:max-w-[36.4rem] px-5 py-6 sm:px-8 sm:py-8 overflow-hidden rounded-2xl border border-white/10 border-t-4 border-t-[#A32D2D] bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800 shadow-2xl shadow-slate-900/15' : 'sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg border-t-4 border-[#A32D2D]' }}">
                {{ $slot }}
            </div>
        </main>
        <script>
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        </script>
    </body>
</html>