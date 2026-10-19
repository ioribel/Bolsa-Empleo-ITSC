<x-guest-layout>

    <div class="mb-4 text-lg font-semibold text-gray-500 dark:text-gray-500">
        {{ __('Registro de Estudiante/Egresado') }}
    </div>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="!text-gray-500" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" class="!text-gray-500" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Matrícula -->
        <div class="mt-4">
            <x-input-label for="matricula" :value="__('Matrícula')" class="!text-gray-500" />
            <x-text-input id="matricula" class="block mt-1 w-full" type="text" name="matricula" :value="old('matricula')" required />
            <x-input-error :messages="$errors->get('matricula')" class="mt-2" />
        </div>

        <!-- Carrera -->
        <div class="mt-4">
            <x-input-label for="carrera_id" :value="__('Carrera')" class="!text-gray-500" />
            <select id="carrera_id" name="carrera_id" required
                class="block mt-1 w-full border-gray-300 focus:border-[#042C53] focus:ring-[#A32D2D] rounded-md shadow-sm">
                <option value="">Selecciona tu carrera</option>
                @foreach ($carreras as $carrera)
                    <option value="{{ $carrera->id }}" @selected(old('carrera_id') == $carrera->id)>
                        {{ $carrera->nombre }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('carrera_id')" class="mt-2" />
        </div>

        <!-- Tipo -->
        <div class="mt-4">
            <x-input-label for="tipo" :value="__('Condición')" class="!text-gray-500" />
            <select id="tipo" name="tipo" required
                class="block mt-1 w-full border-gray-300 focus:border-[#042C53] focus:ring-[#A32D2D] rounded-md shadow-sm">
                <option value="estudiante" @selected(old('tipo') == 'estudiante')>Estudiante activo</option>
                <option value="egresado" @selected(old('tipo') == 'egresado')>Egresado</option>
            </select>
            <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="!text-gray-500" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="!text-gray-500" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#A32D2D]"
                href="{{ route('register.empresa') }}">
                {{ __('¿Eres una empresa? Regístrate aquí') }}
            </a>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#A32D2D]" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>