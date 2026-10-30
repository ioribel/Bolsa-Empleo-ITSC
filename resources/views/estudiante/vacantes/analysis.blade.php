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

            @if ($analysis['profile_only'])
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-200">
                    No se encontró un CV legible. Este análisis se realizó únicamente con la información verificada de tu perfil profesional.
                </div>
            @endif

            <section class="rounded-lg border border-teal-200 bg-teal-50 p-4 text-sm text-teal-950 dark:border-teal-900 dark:bg-teal-950/30 dark:text-teal-100">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="font-semibold">Completitud de tu perfil</h3>
                    <span class="font-bold">{{ $analysis['profile_completion_percentage'] }}%</span>
                </div>
                <p class="mt-1">Un perfil más completo puede aportar evidencia adicional al análisis.</p>
                @if ($analysis['profile_sections_to_complete'])
                    <h4 class="mt-3 font-medium">Completa tu perfil para mejorar tu análisis</h4>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($analysis['profile_sections_to_complete'] as $section)
                            <li>{{ $section }}</li>
                        @endforeach
                    </ul>
                @endif
                <a href="{{ route('estudiante.perfil.edit') }}" class="mt-2 inline-flex font-semibold underline">Editar mi perfil</a>
            </section>

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
                        El cálculo pondera coincidencias directas como completas y coincidencias relacionadas como parciales (50%). No es una probabilidad de contratación.
                    </p>
                @endif

                @if ($analysis['criteria'])
                    <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($analysis['criteria'] as $criterion)
                            <li class="py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $criterion['requirement'] }}</p>
                                    <span class="rounded-full px-2 py-1 text-xs {{ $criterion['match'] === 'direct' ? 'bg-green-100 text-green-800' : ($criterion['match'] === 'related' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ $criterion['match'] === 'direct' ? 'Coincidencia directa' : ($criterion['match'] === 'related' ? 'Coincidencia relacionada' : 'No identificado en CV ni perfil') }}
                                    </span>
                                </div>
                                @if ($criterion['matched'])
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Evidencia <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">{{ $criterion['source'] === 'cv' ? 'CV' : 'Perfil' }}</span>:
                                        “{{ $criterion['evidence'] }}”
                                    </p>
                                @endif
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $criterion['explanation'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">B. Información identificada en tu CV y perfil</h3>
                @php
                    $sectionLabels = [
                        'education' => 'Formación académica',
                        'skills' => 'Habilidades profesionales',
                        'experience' => 'Experiencia laboral',
                        'projects' => 'Proyectos',
                        'certifications' => 'Certificaciones',
                        'technologies' => 'Tecnologías y herramientas',
                        'languages' => 'Idiomas',
                    ];
                @endphp
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ($sectionLabels as $section => $label)
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-gray-200">{{ $label }}</h4>
                            @forelse ($analysis['cv_information'][$section] as $item)
                                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $item['text'] }}
                                    @if ($item['evidence_valid'])
                                        <span class="mt-1 inline-flex rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">{{ $item['source'] === 'cv' ? 'CV' : 'Perfil' }}</span>
                                        <span class="block text-xs text-gray-500">Evidencia: “{{ $item['evidence'] }}”</span>
                                    @else
                                        <span class="mt-1 inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">
                                            Sin evidencia verificable
                                        </span>
                                    @endif
                                </p>
                            @empty
                                <p class="mt-1 text-sm text-gray-500">No identificado en el CV ni en el perfil.</p>
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
                <p class="mt-3 text-xs text-gray-500">“No identificado” significa que no apareció evidencia en el CV ni en el perfil, no que carezcas de esa competencia.</p>
            </section>

            <section class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">D. Recomendaciones personalizadas</h3>
                @forelse ($analysis['recommendations'] as $recommendation)
                    <div class="mt-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-300">
                            {{ ['highlight' => 'Ya lo tienes, destácalo', 'rephrase' => 'Reformula esto', 'gap' => 'Brecha'][$recommendation['kind']] }}
                        </p>
                        <p class="text-sm text-gray-800 dark:text-gray-200">{{ $recommendation['text'] }}</p>
                        <p class="mt-1 text-xs text-gray-500">
                            Basada en {{ ['cv' => 'tu CV', 'perfil' => 'tu perfil', 'vacante' => 'la vacante'][$recommendation['source']] }}:
                            @if ($recommendation['source'] !== 'vacante')
                                <span class="rounded-full bg-indigo-100 px-2 py-0.5 font-medium text-indigo-800">{{ $recommendation['source'] === 'cv' ? 'CV' : 'Perfil' }}</span>
                            @endif
                            “{{ $recommendation['evidence'] }}”
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
                        @foreach ($analysis['suggested_profile']['evidence'] as $citation)
                            <li>
                                Basado en <span class="rounded-full bg-indigo-100 px-2 py-0.5 font-medium text-indigo-800">{{ $citation['source'] === 'cv' ? 'CV' : 'Perfil' }}</span>:
                                “{{ $citation['quote'] }}”
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No hay información verificable suficiente en el CV ni en el perfil para proponer un perfil profesional sin hacer suposiciones.</p>
                @endif
            </section>

            <section class="rounded-lg border border-indigo-200 bg-indigo-50 p-5 shadow-sm dark:border-indigo-900 dark:bg-indigo-950/30 sm:p-6">
                <h3 class="text-lg font-semibold text-indigo-950 dark:text-indigo-100">Revisar y descargar una versión adaptada</h3>
                <p class="mt-2 text-sm text-indigo-950 dark:text-indigo-200">
                    Edita la propuesta antes de descargarla. Las citas muestran de dónde proviene cada dato; confirma que el texto final sea verdadero.
                    El PDF se genera en esta solicitud y no se guarda en el servidor.
                </p>

                    <form method="POST" action="{{ route('estudiante.vacantes.cv-adaptado', $vacante) }}" class="mt-4 space-y-5">
                        @csrf
                        <input type="hidden" name="cv_fingerprint" value="{{ $analysis['cv_fingerprint'] }}">
                        <input type="hidden" name="profile_only" value="{{ $analysis['profile_only'] ? '1' : '0' }}">
                        @if (! empty($analysis['cv_source']))
                            <input type="hidden" name="cv_source" value="{{ $analysis['cv_source'] }}">
                        @endif

                        <div>
                            <label for="adapted-profile" class="block text-sm font-medium text-gray-800 dark:text-gray-200">Perfil profesional</label>
                            <textarea id="adapted-profile" name="adapted_cv[profile][text]" rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">{{ $analysis['adapted_cv']['profile']['text'] }}</textarea>
                            @if ($analysis['adapted_cv']['profile']['text'] === '')
                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">No se generó un perfil verificable. Puedes redactarlo aquí; se marcará como contenido confirmado por ti.</p>
                            @endif
                            @foreach ($analysis['adapted_cv']['profile']['evidence'] as $index => $citation)
                                <input type="hidden" name="adapted_cv[profile][evidence][{{ $index }}][source]" value="{{ $citation['source'] }}">
                                <input type="hidden" name="adapted_cv[profile][evidence][{{ $index }}][quote]" value="{{ $citation['quote'] }}">
                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Fuente en {{ $citation['source'] === 'cv' ? 'tu CV' : 'tu perfil' }}: “{{ $citation['quote'] }}”</p>
                            @endforeach
                        </div>

                        @foreach ($sectionLabels as $section => $label)
                            @php
                                $verifiedItems = array_filter(
                                    $analysis['adapted_cv'][$section],
                                    fn ($item) => $item['evidence_valid'],
                                );
                            @endphp
                            <fieldset class="space-y-3">
                                <legend class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $label }}</legend>
                                @forelse ($verifiedItems as $index => $item)
                                    <div>
                                        <label for="{{ $section }}-{{ $index }}" class="sr-only">{{ $label }} {{ $index + 1 }}</label>
                                        <textarea id="{{ $section }}-{{ $index }}" name="adapted_cv[{{ $section }}][{{ $index }}][text]" rows="2"
                                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">{{ $item['text'] }}</textarea>
                                        <input type="hidden" name="adapted_cv[{{ $section }}][{{ $index }}][evidence]" value="{{ $item['evidence'] }}">
                                        <input type="hidden" name="adapted_cv[{{ $section }}][{{ $index }}][source]" value="{{ $item['source'] }}">
                                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Fuente en {{ $item['source'] === 'cv' ? 'tu CV' : 'tu perfil' }}: “{{ $item['evidence'] }}”</p>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-600 dark:text-gray-400">No se encontraron elementos verificables para esta sección. Puedes completarla si la información es verdadera.</p>
                                @endforelse
                                <label for="{{ $section }}-manual" class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Agregar o completar {{ strtolower($label) }} (opcional)
                                </label>
                                <textarea id="{{ $section }}-manual" name="adapted_cv[manual][{{ $section }}]" rows="2"
                                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
                            </fieldset>
                        @endforeach

                        <label class="flex items-start gap-2 text-sm text-gray-800 dark:text-gray-200">
                            <input type="checkbox" name="consent_confirmed" value="1" required class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm">
                            <span>Confirmo que todo el contenido, incluidas las partes agregadas o editadas sin evidencia verificable, refleja mis antecedentes reales.</span>
                        </label>
                        <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600 sm:w-auto">
                            Descargar CV adaptado en PDF
                        </button>
                    </form>
            </section>

            <a href="{{ route('estudiante.vacantes.show', $vacante) }}" class="inline-flex text-sm font-medium text-indigo-700 underline dark:text-indigo-300">
                Volver a la vacante
            </a>
        </main>
    </div>
</x-app-layout>
