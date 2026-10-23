<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-500 dark:text-gray-500 leading-tight">
            {{ $vacante->titulo }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                @if (session('status'))
                    <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="p-4 bg-red-100 text-red-800 rounded-md">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="p-4 bg-red-100 text-red-800 rounded-md">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $vacante->empresa->nombre_empresa }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Descripción</h3>
                    <p class="text-gray-900 dark:text-gray-100">{{ $vacante->descripcion }}</p>
                </div>

                @if ($vacante->requisitos)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Requisitos</h3>
                        <p class="text-gray-900 dark:text-gray-100">{{ $vacante->requisitos }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Tipo:</span> {{ ucfirst(str_replace('_', ' ', $vacante->tipo_empleo)) }}</div>
                    <div><span class="text-gray-500">Modalidad:</span> {{ ucfirst($vacante->modalidad) }}</div>
                    <div><span class="text-gray-500">Ubicación:</span> {{ $vacante->ubicacion ?? '—' }}</div>
                    <div><span class="text-gray-500">Cierra:</span> {{ $vacante->fecha_cierre?->format('d/m/Y') ?? '—' }}</div>
                </div>

                <hr class="dark:border-gray-700">

                <section class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-900 dark:bg-indigo-950/30">
                    <h3 class="font-semibold text-indigo-950 dark:text-indigo-100">Asistente Inteligente de Empleabilidad</h3>
                    @if (! $dirigidaASuCarrera)
                        <p class="mt-1 text-sm text-indigo-900 dark:text-indigo-200">
                            Esta vacante no está dirigida a tu carrera, por lo que no se puede analizar desde tu perfil.
                        </p>
                    @else
                        <p class="mt-1 text-sm text-indigo-900 dark:text-indigo-200">
                            Recibirás una comparación orientativa y una propuesta editable de CV. No es una probabilidad de contratación.
                        </p>
                        <p class="mt-2 text-sm text-indigo-900 dark:text-indigo-200">
                            @if ($tieneCvPerfil)
                                Puedes subir un PDF para este análisis; si no seleccionas uno, se usará el CV de tu perfil.
                            @else
                                Sube un PDF para analizar esta vacante. El archivo se utilizará solo para este análisis y no se guardará en tu perfil.
                            @endif
                        </p>
                        <div class="mt-3 rounded-md bg-white/70 p-3 text-xs leading-5 text-gray-700 dark:bg-gray-900/60 dark:text-gray-300">
                            Al continuar se enviará a Google Gemini el texto extraído del PDF que subas o, si no subes uno, del CV guardado en tu perfil, junto con los datos de esta vacante.
                            Se omiten el nombre, correo y matrícula del perfil, además de teléfonos y correos detectados en el texto.
                            Google puede conservar solicitudes y respuestas hasta 55 días para monitoreo de abuso.
                        </div>
                        <form method="POST" action="{{ route('estudiante.vacantes.analizar-cv', $vacante) }}" enctype="multipart/form-data" class="mt-4">
                            @csrf
                            <label for="analysis-cv" class="block text-sm font-medium text-gray-800 dark:text-gray-200">
                                PDF para este análisis ({{ $tieneCvPerfil ? 'opcional' : 'requerido' }}, máximo 2 MB)
                            </label>
                            <input id="analysis-cv" type="file" name="cv" accept="application/pdf,.pdf" @unless ($tieneCvPerfil) required @endunless
                                class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300">
                            <x-input-error :messages="$errors->get('cv')" class="mt-2" />
                            <label class="flex items-start gap-2 text-sm text-gray-800 dark:text-gray-200">
                                <input type="checkbox" name="consent" value="1" required class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm">
                                <span>Consiento que el texto de mi CV se procese con Gemini para generar este análisis.</span>
                            </label>
                            <button type="submit" class="mt-3 inline-flex min-h-10 w-full items-center justify-center rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600 sm:w-auto">
                                Analizar mi CV con IA
                            </button>
                        </form>
                    @endif
                </section>

                <hr class="dark:border-gray-700">

                @if ($yaPostulado)
                    <div class="p-4 bg-blue-100 text-blue-800 rounded-md">
                        Ya te has postulado a esta vacante. Revisa el estado en "Mis Postulaciones".
                    </div>
                @else
                    <form method="POST" action="{{ route('estudiante.vacantes.postular', $vacante) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <x-input-label for="cv" :value="__('Currículum para esta postulación (PDF, máx. 2MB)')" />
                            @if ($tieneCvPerfil)
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Si no seleccionas otro archivo, se usará una copia del CV guardado en tu perfil.</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Adjunta tu CV para completar la postulación.</p>
                            @endif
                            <input id="cv" type="file" name="cv" accept="application/pdf,.pdf" @unless ($tieneCvPerfil) required @endunless
                                class="block mt-1 w-full text-sm text-gray-700 dark:text-gray-300">
                            <x-input-error :messages="$errors->get('cv')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="carta_presentacion" :value="__('Carta de presentación (opcional)')" />
                            <textarea id="carta_presentacion" name="carta_presentacion" rows="4"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('carta_presentacion') }}</textarea>
                        </div>
                        <x-primary-button class="mt-4">{{ __('Postularme') }}</x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>