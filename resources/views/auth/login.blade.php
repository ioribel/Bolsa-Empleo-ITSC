<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 border-b border-white/10 pb-5">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-400">Bolsa de Empleo ITSC</p>
        <h1 class="mt-2 text-2xl font-bold text-white">Inicia sesión</h1>
        <p class="mt-2 text-sm text-slate-400">Accede a tu cuenta para continuar.</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo electrónico" class="!text-slate-200" />
            <x-text-input id="email" class="mt-1 block w-full border-slate-300 bg-white text-slate-900 focus:border-sky-400 focus:ring-sky-400" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" class="!text-slate-200" />

            <x-text-input id="password" class="mt-1 block w-full border-slate-300 bg-white text-slate-900 focus:border-sky-400 focus:ring-sky-400"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-500 text-sky-500 shadow-sm focus:ring-sky-400" name="remember">
                <span class="ms-2 text-sm text-slate-300">Recordarme</span>
            </label>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-sky-300 underline hover:text-white focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2 focus:ring-offset-slate-900" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif

            <x-primary-button class="ms-auto !bg-blue-700 hover:!bg-blue-600 focus:!bg-blue-600 active:!bg-blue-800 focus:!ring-blue-400 focus:!ring-offset-slate-900">
                Iniciar sesión
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>