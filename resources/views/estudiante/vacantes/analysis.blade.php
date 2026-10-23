<x-app-layout>
    <x-slot name="header">
        <h2 class="break-words font-semibold text-xl text-gray-700 leading-tight">
            Análisis de CV: {{ $vacante->titulo }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <main class="mx-auto max-w-5xl space-y-5 px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950">
                Este análisis es orientativo, puede contener errores y no representa una probabilidad de contratación.
                Revisa las evidencias y verifica que cada sugerencia describa tu experiencia real. El informe no se guarda como historial.
            </div>

            <section class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">A. Compatibilidad con la vacante</h3>
                @if ($analysis['compatibility_percentage'] === null)
                    <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                        No hay requisitos textuales suficientes para calcular un porcentaje verificable.
                    </p>
                @else
                    <p class="mt-3 text-3xl font-bold text-indigo-700 dark:text-indigo-300">
                        {{ number_format($analysis['compatibility_percentage'], 1) }}%
                    </p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ count(array_filter($analysis['criteria'], fn ($criterion) => $criterion['matched'])) }} de {{ count($analysis['criteria']) }}
                        criterios tienen una cita textual que contiene al menos la mitad de sus palabras clave verificables en el CV.
                    </p>
                @endif

                @if ($analysis['criteria'])
                    <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($analysis['criteria'] as $criterion)
                            <li class="py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $criterion['requirement'] }}</p>
                                    <span class="rounded-full px-2 py-1 text-xs {{ $criterion['matched'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $criterion['matched'] ? 'Coincidencia documentada' : 'No identificado en el CV' }}
                                    </span>
                                </div>
                                @if ($criterion['matched'])
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Evidencia: “{{ $criterion['evidence'] }}”</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">B. Información identificada en el CV</h3>
                @php
                    $sectionLabels = [
                        'education' => 'Formación académica',
                        'skills' => 'Habilidades profesionales',
                        'experience' => 'Experiencia laboral',
                        'projects' => 'Proyectos',
                        'certifications' => 'Certificaciones',
                        'technologies' => 'Tecnologías y herramientas',
                    ];
                @endphp
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ($sectionLabels as $section => $label)
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200">{{ $label }}</h4>
                            @forelse ($analysis['cv_information'][$section] as $item)
                                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $item['text'] }}
                                    <span class="block text-xs text-gray-500">Evidencia: “{{ $item['evidence'] }}”</span>
                                </p>
                            @empty
                                <p class="mt-1 text-sm text-gray-500">No identificado en el documento.</p>
                            @endforelse
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">C. Requisitos no identificados</h3>
                @forelse ($analysis['not_identified'] as $item)
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">• {{ $item['requirement'] }}</p>
                @empty
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                        Todos los criterios comparables tienen al menos una evidencia citada. Esto no garantiza que la empresa los considere suficientes.
                    </p>
                @endforelse
                <p class="mt-3 text-xs text-gray-500">“No identificado” significa que no apareció evidencia en este documento, no que carezcas de esa competencia.</p>
            </section>

            <section class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">D. Recomendaciones personalizadas</h3>
                @forelse ($analysis['recommendations'] as $recommendation)
                    <div class="mt-3">
                        <p class="text-sm text-gray-800 dark:text-gray-200">{{ $recommendation['text'] }}</p>
                        <p class="mt-1 text-xs text-gray-500">
                            Basada en {{ $recommendation['source'] === 'cv' ? 'tu CV' : 'la vacante' }}: “{{ $recommendation['evidence'] }}”
                        </p>
                    </div>
                @empty
                    <p class="mt-2 text-sm text-gray-500">No se generaron recomendaciones verificables para este documento.</p>
                @endforelse
            </section>

            <section class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">E. Perfil profesional sugerido</h3>
                @if ($analysis['suggested_profile']['text'] !== '')
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-800 dark:text-gray-200">{{ $analysis['suggested_profile']['text'] }}</p>
                    <ul class="mt-3 list-inside list-disc text-xs text-gray-500">
                        @foreach ($analysis['suggested_profile']['evidence'] as $evidence)
                            <li>Basado en: “{{ $evidence }}”</li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">El CV no contiene información suficiente para proponer un perfil profesional sin hacer suposiciones.</p>
                @endif
            </section>

            <section class="rounded-lg border border-indigo-200 bg-indigo-50 p-5 shadow-sm dark:border-indigo-900 dark:bg-indigo-950/30 sm:p-6">
                <h3 class="text-lg font-semibold text-indigo-950 dark:text-indigo-100">Revisar y descargar una versión adaptada</h3>
                <p class="mt-2 text-sm text-indigo-950 dark:text-indigo-200">
                    Edita la propuesta antes de descargarla. Las citas muestran de dónde proviene cada dato; confirma que el texto final sea verdadero.
                    El PDF se genera en esta solicitud y no se guarda en el servidor.
                </p>

                @if ($analysis['adapted_cv']['profile']['text'] !== '')
                    <form method="POST" action="{{ route('estudiante.vacantes.cv-adaptado', $vacante) }}" class="mt-4 space-y-5">
                        @csrf
                        <input type="hidden" name="cv_fingerprint" value="{{ $analysis['cv_fingerprint'] }}">
                        @if (! empty($analysis['cv_source']))
                            <input type="hidden" name="cv_source" value="{{ $analysis['cv_source'] }}">
                        @endif

                        <div>
                            <label for="adapted-profile" class="block text-sm font-medium text-gray-800 dark:text-gray-200">Perfil profesional</label>
                            <textarea id="adapted-profile" name="adapted_cv[profile][text]" rows="4" required
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">{{ $analysis['adapted_cv']['profile']['text'] }}</textarea>
                            @foreach ($analysis['adapted_cv']['profile']['evidence'] as $evidence)
                                <input type="hidden" name="adapted_cv[profile][evidence][]" value="{{ $evidence }}">
                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Fuente en tu CV: “{{ $evidence }}”</p>
                            @endforeach
                        </div>

                        @foreach ($sectionLabels as $section => $label)
                            @if ($analysis['adapted_cv'][$section])
                                <fieldset class="space-y-3">
                                    <legend class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $label }}</legend>
                                    @foreach ($analysis['adapted_cv'][$section] as $index => $item)
                                        <div>
                                            <label for="{{ $section }}-{{ $index }}" class="sr-only">{{ $label }} {{ $index + 1 }}</label>
                                            <textarea id="{{ $section }}-{{ $index }}" name="adapted_cv[{{ $section }}][{{ $index }}][text]" rows="2" required
                                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">{{ $item['text'] }}</textarea>
                                            <input type="hidden" name="adapted_cv[{{ $section }}][{{ $index }}][evidence]" value="{{ $item['evidence'] }}">
                                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Fuente en tu CV: “{{ $item['evidence'] }}”</p>
                                        </div>
                                    @endforeach
                                </fieldset>
                            @endif
                        @endforeach

                        <label class="flex items-start gap-2 text-sm text-gray-800 dark:text-gray-200">
                            <input type="checkbox" name="consent_confirmed" value="1" required class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm">
                            <span>Revisé y confirmé que la información del CV adaptado representa fielmente mis antecedentes.</span>
                        </label>
                        <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600 sm:w-auto">
                            Descargar CV adaptado en PDF
                        </button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">No hay suficiente información verificable para generar una versión adaptada.</p>
                @endif
            </section>

            <a href="{{ route('estudiante.vacantes.show', $vacante) }}" class="inline-flex text-sm font-medium text-indigo-700 underline dark:text-indigo-300">
                Volver a la vacante
            </a>
        </main>
    </div>
</x-app-layout>
