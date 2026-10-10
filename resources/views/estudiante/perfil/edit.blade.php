<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mi Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
                @endif

                <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Nombre:</span> {{ $estudiante->user->name }}</div>
                    <div><span class="text-gray-500">Correo:</span> {{ $estudiante->user->email }}</div>
                    <div><span class="text-gray-500">Matrícula:</span> {{ $estudiante->matricula }}</div>
                    <div><span class="text-gray-500">Carrera:</span> {{ $estudiante->carrera->nombre }}</div>
                </div>

                <form method="POST" action="{{ route('estudiante.perfil.update') }}" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <x-input-label for="telefono" :value="__('Teléfono')" />
                        <x-text-input id="telefono" class="block mt-1 w-full" type="text" name="telefono" :value="old('telefono', $estudiante->telefono)" />
                        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="resumen" :value="__('Resumen profesional')" />
                        <textarea id="resumen" name="resumen" rows="4"
                            class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('resumen', $estudiante->resumen) }}</textarea>
                        <x-input-error :messages="$errors->get('resumen')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="cv" :value="__('Currículum (PDF, máx. 2MB)')" />
                        @if ($estudiante->cv_path)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Actual: <a href="{{ Storage::url($estudiante->cv_path) }}" target="_blank" class="underline text-indigo-600">Ver CV</a>
                            </p>
                        @endif
                        <input id="cv" type="file" name="cv" accept="application/pdf"
                            class="block mt-1 w-full text-sm text-gray-700 dark:text-gray-300">
                        <x-input-error :messages="$errors->get('cv')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <x-primary-button>{{ __('Guardar cambios') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>