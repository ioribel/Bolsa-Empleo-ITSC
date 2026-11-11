<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-[#3f4041] leading-tight">
            Crear administrador
        </h1>
    </x-slot>

    <div class="itsc-page py-6 sm:py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <section class="itsc-surface p-5 sm:p-8">
                <div class="mb-6">
                    <p class="text-sm text-itsc-slate-600">Administración de usuarios</p>
                    <h2 class="mt-1 text-lg font-semibold text-itsc-slate-900">Datos de la nueva cuenta</h2>
                    <p class="mt-2 text-sm text-itsc-slate-600">
                        El nuevo administrador recibirá un correo con su correo de acceso y un enlace seguro para cambiar su contraseña.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.administradores.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-itsc-slate-800">Nombre completo</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255"
                            autocomplete="name" autofocus @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        @error('name')
                            <p id="name-error" role="alert" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-itsc-slate-800">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255"
                            autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        @error('email')
                            <p id="email-error" role="alert" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-itsc-slate-800">Contraseña inicial</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                            @error('password') aria-invalid="true" aria-describedby="password-hint password-error" @enderror
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                        <p id="password-hint" class="mt-2 text-sm text-itsc-slate-600">Mínimo 8 caracteres, incluyendo letras y números.</p>
                        @error('password')
                            <p id="password-error" role="alert" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-itsc-slate-800">Confirmar contraseña</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-itsc-slate-100 pt-5 sm:flex-row sm:justify-end">
                        <a href="{{ route('admin.usuarios.index') }}"
                            class="inline-flex min-h-10 items-center justify-center rounded-lg border border-itsc-slate-200 px-4 text-sm font-medium text-itsc-slate-700 transition-colors hover:bg-itsc-slate-50">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex min-h-10 items-center justify-center rounded-lg bg-itsc-slate-700 px-5 text-sm font-semibold text-white transition-colors hover:bg-itsc-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Crear administrador
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
