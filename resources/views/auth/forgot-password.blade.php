<x-guest-layout>
    <h1 class="mb-2 text-lg font-semibold text-gray-900">
        Restablecer contraseña
    </h1>
    <p class="mb-4 text-sm text-gray-600">
        ¿Olvidaste tu contraseña? Indícanos tu correo electrónico y, si está registrado, te enviaremos un enlace para crear una nueva.
    </p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Enviar enlace de recuperación
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
