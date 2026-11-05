<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-500 dark:text-gray-500 leading-tight">
            {{ $vacante->titulo }}
        </h1>
    </x-slot>

    <div class="itsc-page py-8 sm:py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="itsc-surface space-y-4 p-4 sm:p-5">
                @if (session('status'))
                    <div role="status" aria-live="polite" class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div role="alert" class="p-4 bg-red-100 text-red-800 rounded-md">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div role="alert" class="p-4 bg-red-100 text-red-800 rounded-md">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="flex flex-col gap-2 border-b border-itsc-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-medium text-itsc-slate-700">{{ $vacante->empresa->nombre_empresa }}</p>
                    <a href="{{ route('estudiante.empresas.show', $vacante->empresa) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800 transition-colors hover:bg-amber-100">
                        Ver perfil de la empresa
                    </a>
                </div>

                <div>
                    <h3 class="text-[15px] font-medium text-itsc-slate-900">Descripción</h3>
                    <p class="text-sm leading-6 text-itsc-slate-700">{{ $vacante->descripcion }}</p>
                </div>

                @if ($vacante->requisitos)
                    <div>
                        <h3 class="text-[15px] font-medium text-itsc-slate-900">Requisitos</h3>
                        <p class="text-sm leading-6 text-itsc-slate-700">{{ $vacante->requisitos }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div><span class="text-itsc-slate-500">Tipo:</span> {{ ucfirst(str_replace('_', ' ', $vacante->tipo_empleo)) }}</div>
                    <div><span class="text-itsc-slate-500">Modalidad:</span> {{ ucfirst($vacante->modalidad) }}</div>
                    <div><span class="text-itsc-slate-500">Ubicación:</span> {{ $vacante->ubicacion ?? '—' }}</div>
                    <div><span class="text-itsc-slate-500">Cierra:</span> {{ $vacante->fecha_cierre?->format('d/m/Y') ?? '—' }}</div>
                </div>

                <hr class="border-itsc-slate-100">

                <section class="rounded-xl border border-itsc-slate-100 bg-itsc-slate-50 p-4">
                    <h3 class="text-[15px] font-medium text-itsc-slate-900">Asistente inteligente de empleabilidad</h3>
                    @if (! $dirigidaASuCarrera)
                        <p class="mt-1 text-sm text-itsc-slate-700">
                            Esta vacante no está dirigida a tu carrera, por lo que no se puede analizar desde tu perfil.
                        </p>
                    @else
                        <p class="mt-1 text-sm text-itsc-slate-700">
                            Recibirás una comparación orientativa y una propuesta editable de CV. No es una probabilidad de contratación.
                        </p>
                        <p class="mt-2 text-sm text-itsc-slate-700">
                            @if ($tieneCvPerfil)
                                Puedes subir otro PDF o usar el de tu perfil. Si ese CV no es legible y tu perfil tiene información suficiente, el análisis continuará solo con tu perfil.
                            @elseif ($puedeAnalizarSinCv)
                                Puedes analizar tu perfil profesional estructurado sin subir un PDF, o adjuntar un CV para complementar la evidencia.
                            @else
                                Sube un PDF para analizar esta vacante. También puedes completar tu perfil para analizarlo sin CV.
                            @endif
                        </p>
                        <div class="mt-3 rounded-lg border border-itsc-slate-100 bg-white p-3 text-xs leading-5 text-itsc-slate-700">
                            Al continuar se enviarán a un servicio externo (Google Gemini) el texto extraído del CV y los datos profesionales de tu perfil (formación, experiencia, habilidades, idiomas y certificaciones), junto con los datos de esta vacante.
                            Se omiten nombre, correo, teléfono, matrícula y URLs; también se redactan datos de contacto detectados en el CV. Un PDF que adjuntes se utilizará solo para este análisis y no se guardará en tu perfil.
                            Google puede conservar solicitudes y respuestas hasta 55 días para monitoreo de abuso.
                        </div>
                        <form id="cv-analysis-form" method="POST" action="{{ route('estudiante.vacantes.analizar-cv', $vacante) }}" enctype="multipart/form-data" class="mt-4">
                            @csrf
                            <input type="hidden" name="consent_version" value="{{ \App\Http\Controllers\Estudiante\EmployabilityAnalysisController::CONSENT_VERSION }}">
                            <label for="analysis-cv" class="block text-xs text-itsc-slate-500">
                                PDF para este análisis ({{ $tieneCvPerfil || $puedeAnalizarSinCv ? 'opcional' : 'requerido' }}, máximo 2 MB)
                            </label>
                            <input id="analysis-cv" type="file" name="cv" accept="application/pdf,.pdf" @unless ($tieneCvPerfil || $puedeAnalizarSinCv) required @endunless
                                class="mt-1 block min-h-10 w-full rounded-lg border border-itsc-slate-300 bg-white px-3 py-2 text-sm text-itsc-slate-700">
                            <x-input-error :messages="$errors->get('cv')" class="mt-2" />
                            <label class="flex items-start gap-2 text-sm text-itsc-slate-700">
                                <input type="checkbox" name="consent" value="1" required class="mt-1 rounded border-itsc-slate-300 text-itsc-slate-700 shadow-sm focus:ring-itsc-red-600/20">
                                <span>Consiento que se envíen a Gemini el contenido de mi CV y los datos profesionales de mi perfil indicados arriba, sin datos de contacto, para generar este análisis.</span>
                            </label>
                            <button type="submit" class="mt-3 inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-itsc-slate-700 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-itsc-slate-900 sm:w-auto">
                                Analizar mi CV y perfil con IA
                            </button>
                        </form>
                        <div id="cv-processing-modal" class="hidden" style="position: fixed; inset: 0; z-index: 1055; align-items: center; justify-content: center; background: rgba(17, 24, 39, .5); padding: 1rem;">
                            <div class="w-full max-w-lg overflow-hidden rounded-lg border border-gray-200 bg-white text-gray-900 shadow-xl" style="margin: auto;" role="dialog" aria-modal="true" aria-labelledby="cv-processing-title" aria-describedby="cv-processing-message">
                                <div class="border-b border-gray-200 px-5 py-4">
                                    <h2 id="cv-processing-title" class="text-lg font-semibold">Analizando tu CV y perfil</h2>
                                </div>
                                <div class="flex items-center gap-4 px-5 py-5" aria-live="polite">
                                    <span class="h-8 w-8 shrink-0 animate-spin rounded-full border-4 border-indigo-100 border-r-indigo-600 motion-reduce:animate-none" aria-hidden="true"></span>
                                    <p id="cv-processing-message" class="m-0 text-sm text-gray-700">Estamos procesando la información profesional con Gemini. Puede tardar unos segundos.</p>
                                </div>
                            </div>
                        </div>
                        <script>
                            const cvAnalysisForm = document.getElementById('cv-analysis-form');
                            const cvProcessingModal = document.getElementById('cv-processing-modal');
                            const cvAnalysisSubmit = cvAnalysisForm.querySelector('button[type="submit"]');
                            document.body.appendChild(cvProcessingModal);

                            cvAnalysisForm.addEventListener('submit', function (event) {
                                if (!this.reportValidity()) {
                                    event.preventDefault();
                                    return;
                                }

                                this.setAttribute('aria-busy', 'true');
                                cvAnalysisSubmit.disabled = true;
                                cvProcessingModal.classList.remove('hidden');
                                cvProcessingModal.style.display = 'flex';
                            });

                            window.addEventListener('pageshow', function () {
                                cvProcessingModal.classList.add('hidden');
                                cvProcessingModal.style.display = 'none';
                                cvAnalysisForm.removeAttribute('aria-busy');
                                cvAnalysisSubmit.disabled = false;
                            });
                        </script>
                    @endif
                </section>

                <hr class="dark:border-gray-700">

                @if ($yaPostulado)
                    <div class="rounded-xl border border-itsc-slate-100 bg-itsc-slate-50 p-4 text-sm text-itsc-slate-700">
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
                                class="block min-h-10 mt-1 w-full rounded-lg border border-itsc-slate-300 bg-white px-3 py-2 text-sm text-itsc-slate-700">
                            <x-input-error :messages="$errors->get('cv')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="carta_presentacion" :value="__('Carta de presentación (opcional)')" />
                            <textarea id="carta_presentacion" name="carta_presentacion" rows="4"
                                class="block mt-1 w-full rounded-lg border border-itsc-slate-300 bg-white px-3 py-2 text-sm text-itsc-slate-900 focus:border-itsc-red-600 focus:ring-4 focus:ring-itsc-red-600/15">{{ old('carta_presentacion') }}</textarea>
                        </div>
                        <x-primary-button class="mt-4">{{ __('Postularme') }}</x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>