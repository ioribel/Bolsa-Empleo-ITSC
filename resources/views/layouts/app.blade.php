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
    <body class="font-sans antialiased">
        <a href="#main-content" class="sr-only z-50 rounded-md bg-white px-4 py-2 text-sm font-semibold text-[#042C53] shadow focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:ring-2 focus:ring-[#A32D2D] focus:ring-offset-2">
            Saltar al contenido
        </a>
        <div class="min-h-screen bg-gray-50">
            @if (! request()->routeIs('dashboard', '*.dashboard'))
                <div class="bg-itsc-slate-50 px-3 pt-3 sm:px-6">
                    <x-profile-navigation />
                </div>
            @else
                @include('layouts.navigation')
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow border-b-2 border-[#A32D2D]">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex items-center gap-3">
                        @php
                            $backUrl = request()->routeIs('estudiante.empresas.show')
                                ? route('estudiante.vacantes.index')
                                : route('dashboard');
                        @endphp
                        @if (!request()->routeIs('dashboard'))
                            <a href="{{ $backUrl }}"
                                onclick="if (history.length > 1 && document.referrer && new URL(document.referrer).origin === location.origin) { event.preventDefault(); history.back(); }"
                                aria-label="Volver a la página anterior"
                                class="inline-flex shrink-0 items-center gap-2 rounded-md p-1.5 text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-900 focus-visible:ring-2 focus-visible:ring-[#A32D2D] focus-visible:ring-offset-2">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span>Volver</span>
                            </a>
                        @endif
                        <div class="page-heading">
                            {{ $header }}
                        </div>
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main id="main-content" tabindex="-1" class="page-in">
                {{ $slot }}
            </main>
            @if ($errors->any())
                <script>
                    (() => {
                        const field = [...document.querySelectorAll('[aria-invalid="true"]')]
                            .find(element => element.getClientRects().length > 0);

                        if (field) field.focus();
                    })();
                </script>
            @endif
        </div>
        <script>
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        </script>
    </body>
</html>