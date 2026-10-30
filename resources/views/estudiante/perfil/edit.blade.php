<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-700 dark:text-gray-200 leading-tight">
            {{ __('Mi perfil profesional') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="mx-auto max-w-5xl space-y-5 px-3 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-100 p-4 text-green-800" role="status">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-100 p-4 text-red-800" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <p class="font-semibold">Revisa los datos del formulario:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Completitud del perfil</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Un perfil completo ayuda a las empresas a conocer tu experiencia.</p>
                    </div>
                    <span class="shrink-0 text-lg font-bold text-[#042C53] dark:text-white">{{ $estudiante->porcentajeCompletitud() }}%</span>
                </div>
                <div class="mt-3 h-3 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700" role="progressbar"
                    aria-label="Completitud del perfil" aria-valuemin="0" aria-valuemax="100"
                    aria-valuenow="{{ $estudiante->porcentajeCompletitud() }}">
                    <div class="h-full rounded-full bg-[#042C53] transition-all"
                        style="width: {{ $estudiante->porcentajeCompletitud() }}%"></div>
                </div>
                @if ($estudiante->perfilPendienteCompletar())
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                        Para completar tu perfil: {{ implode('; ', $estudiante->perfilPendienteCompletar()) }}.
                    </p>
                @else
                    <p class="mt-3 text-sm font-medium text-green-700">¡Tu perfil cumple todos los criterios de completitud!</p>
                @endif
            </section>

            <section class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Datos básicos y enlaces</h3>
                <div class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                    <p><span class="text-gray-500">Nombre:</span> {{ $estudiante->user->name }}</p>
                    <p><span class="text-gray-500">Correo:</span> {{ $estudiante->user->email }}</p>
                    <p><span class="text-gray-500">Matrícula:</span> {{ $estudiante->matricula }}</p>
                    <p><span class="text-gray-500">Carrera:</span> {{ $estudiante->carrera->nombre }}</p>
                </div>

                <form method="POST" action="{{ route('estudiante.perfil.update') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="titular" value="Titular profesional" />
                        <x-text-input id="titular" name="titular" maxlength="120" class="mt-1 block w-full"
                            placeholder="Ej. Estudiante de Software, 8vo cuatrimestre"
                            :value="old('titular', $estudiante->titular)" />
                        <x-input-error :messages="$errors->get('titular')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="telefono" value="Teléfono" />
                        <x-text-input id="telefono" name="telefono" class="mt-1 block w-full"
                            :value="old('telefono', $estudiante->telefono)" />
                        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="resumen" value="Resumen profesional" />
                        <textarea id="resumen" name="resumen" rows="4" maxlength="1000"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('resumen', $estudiante->resumen) }}</textarea>
                        <x-input-error :messages="$errors->get('resumen')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="linkedin_url" value="LinkedIn" />
                            <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="mt-1 block w-full"
                                placeholder="https://www.linkedin.com/in/tu-perfil"
                                :value="old('linkedin_url', $estudiante->linkedin_url)" />
                            <x-input-error :messages="$errors->get('linkedin_url')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="github_url" value="GitHub" />
                            <x-text-input id="github_url" name="github_url" type="url" class="mt-1 block w-full"
                                placeholder="https://github.com/usuario"
                                :value="old('github_url', $estudiante->github_url)" />
                            <x-input-error :messages="$errors->get('github_url')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="portafolio_url" value="Portafolio (HTTPS)" />
                            <x-text-input id="portafolio_url" name="portafolio_url" type="url" class="mt-1 block w-full"
                                placeholder="https://tuportafolio.com"
                                :value="old('portafolio_url', $estudiante->portafolio_url)" />
                            <x-input-error :messages="$errors->get('portafolio_url')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="cv" value="Currículum (PDF, máximo 2 MB)" />
                        @if ($estudiante->cv_path)
                            <p class="mt-1 break-words text-sm text-gray-600 dark:text-gray-400">
                                @if ($cvDetails)
                                    Archivo actual: {{ $cvDetails['name'] }} ({{ number_format($cvDetails['size'] / 1024, 0) }} KB).
                                @else
                                    Hay un currículum registrado, pero el archivo no está disponible.
                                @endif
                                <a href="{{ route('estudiante.perfil.cv') }}" target="_blank" rel="noopener" class="ml-1 underline text-indigo-600">Ver CV</a>
                            </p>
                        @endif
                        <input id="cv" type="file" name="cv" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300">
                        <x-input-error :messages="$errors->get('cv')" class="mt-2" />
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                        @if ($estudiante->cv_path)
                            <button type="submit" form="delete-profile-cv"
                                class="text-left text-sm font-medium text-red-600 underline">Eliminar currículum</button>
                        @endif
                        <x-primary-button class="justify-center">Guardar datos básicos</x-primary-button>
                    </div>
                </form>

                @if ($estudiante->cv_path)
                    <form id="delete-profile-cv" method="POST" action="{{ route('estudiante.perfil.cv.destroy') }}"
                        class="hidden" onsubmit="return confirm('¿Eliminar tu currículum? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            </section>

            @include('estudiante.perfil.partials.formaciones')
            @include('estudiante.perfil.partials.experiencias')
            @include('estudiante.perfil.partials.habilidades')
            @include('estudiante.perfil.partials.idiomas')
            @include('estudiante.perfil.partials.certificaciones')
        </div>
    </div>
</x-app-layout>
