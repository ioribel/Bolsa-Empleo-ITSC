<x-app-layout>
    <x-slot name="header">
        <h1 class="page-heading text-xl font-medium leading-tight">{{ __('Mi perfil profesional') }}</h1>
    </x-slot>

    <div class="profile-view bg-itsc-slate-50 px-3 py-4 sm:px-6 sm:py-6">
        <div class="mx-auto max-w-4xl space-y-3 rounded-xl bg-itsc-slate-50 p-1 sm:p-2">
            @if (session('status'))
                <div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 5000)" x-show="visible"
                    role="status" aria-live="polite" class="fixed right-4 top-4 z-50 rounded-xl border border-itsc-slate-100 bg-white px-4 py-3 text-sm text-itsc-slate-700">
                    {{ str_contains(mb_strtolower(session('status')), 'actualiz') ? 'Cambios guardados' : session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <p class="font-medium">Revisa los datos del formulario:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php($completitud = $estudiante->porcentajeCompletitud())
            @php($pendientes = $estudiante->perfilPendienteCompletar())
            @php($destinoPendiente = match ($pendientes[0] ?? null) {
                'Agregar un titular profesional' => '#titular',
                'Escribir un resumen profesional' => '#resumen',
                'Cargar el currículum' => '#cv',
                'Agregar formación académica' => '#formaciones',
                'Agregar experiencia o pasantía' => '#experiencia-laboral',
                'Agregar al menos 3 habilidades' => '#habilidades',
                'Agregar un idioma' => '#idiomas',
                'Agregar al menos un enlace profesional' => '#linkedin_url',
                default => '#formaciones',
            })

            <x-profile-card class="flex flex-col items-center gap-4 sm:flex-row">
                <x-profile-progress-ring :percentage="$completitud" />
                <div class="min-w-0 flex-1 text-center sm:text-left">
                    <h2 class="text-[15px] font-medium text-itsc-slate-900">Completitud del perfil</h2>
                    <p class="mt-1 text-sm text-itsc-slate-500">Un perfil completo ayuda a las empresas a conocer tu experiencia.</p>
                </div>
                @if (count($pendientes))
                    <a href="{{ $destinoPendiente }}" class="inline-flex min-h-10 items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-sm text-amber-800 transition-colors hover:bg-amber-100 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-amber-200">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18.6A1.6 1.6 0 0 0 3.2 21h17.6a1.6 1.6 0 0 0 1.4-2.4L13.7 3.9a1.9 1.9 0 0 0-3.4 0Z" />
                        </svg>
                        <span>Falta: {{ $pendientes[0] }}</span>
                    </a>
                @else
                    <span class="rounded-full bg-amber-50 px-3 py-1.5 text-sm text-amber-800">Perfil completo</span>
                @endif
            </x-profile-card>

            <x-profile-card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <svg class="h-[18px] w-[18px] text-itsc-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 4 18.5V20m14-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM12 5.5a4 4 0 1 0-8 0 4 4 0 0 0 8 0Zm6 5a4.5 4.5 0 0 1 4 4.5V17" />
                        </svg>
                        <h2 class="text-[15px] font-medium text-itsc-slate-900">Datos básicos y enlaces</h2>
                    </div>
                </x-slot>
                <x-slot name="actions">
                    <span class="inline-flex items-center gap-1 text-xs text-itsc-slate-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <rect x="4" y="10" width="16" height="11" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 10V7a4 4 0 1 1 8 0v3" />
                        </svg>
                        Campos de la institución
                    </span>
                </x-slot>

                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-full bg-itsc-slate-700 text-sm font-medium text-white" aria-hidden="true">
                        {{ collect(explode(' ', trim($estudiante->user->name)))->filter()->take(2)->map(fn ($parte) => mb_substr($parte, 0, 1))->implode('') }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-base font-medium text-itsc-slate-900">{{ $estudiante->user->name }}</p>
                        <p class="truncate text-sm text-itsc-slate-500">{{ $estudiante->carrera->nombre }} · {{ $estudiante->matricula }}</p>
                    </div>
                </div>

                <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                    <x-profile-input label="Correo electrónico" id="perfil-correo" :value="$estudiante->user->email" readonly>
                        <x-slot name="icon">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8 6 8-6" />
                            </svg>
                        </x-slot>
                    </x-profile-input>
                    <x-profile-input label="Matrícula" id="perfil-matricula" :value="$estudiante->matricula" readonly>
                        <x-slot name="icon">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <rect x="4" y="3" width="16" height="18" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 8h8M8 12h8M8 16h4" />
                            </svg>
                        </x-slot>
                    </x-profile-input>
                </div>

                <form method="POST" action="{{ route('estudiante.perfil.update') }}" enctype="multipart/form-data" class="space-y-4"
                    x-data="{ saving: false, summaryLength: {{ mb_strlen(old('resumen', $estudiante->resumen ?? '')) }} }"
                    @submit="saving = true">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <x-profile-input id="titular" name="titular" label="Titular profesional" maxlength="120"
                            placeholder="Ej. Estudiante de software, 8vo cuatrimestre"
                            :value="old('titular', $estudiante->titular)" :error="$errors->first('titular')" />
                        <x-profile-input id="telefono" name="telefono" label="Teléfono"
                            :value="old('telefono', $estudiante->telefono)" :error="$errors->first('telefono')" />
                    </div>

                    <div>
                        <x-profile-input id="resumen" name="resumen" label="Resumen profesional" as="textarea" rows="3" maxlength="1000"
                            placeholder="Comparte un resumen de tu perfil profesional" :value="old('resumen', $estudiante->resumen)"
                            :error="$errors->first('resumen')" x-on:input="summaryLength = $event.target.value.length" />
                        <p class="mt-1 text-right text-[11px] text-itsc-slate-500"><span x-text="summaryLength"></span> / 1000</p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <x-profile-input id="linkedin_url" name="linkedin_url" type="url" label="LinkedIn"
                            placeholder="https://www.linkedin.com/in/tu-perfil"
                            :value="old('linkedin_url', $estudiante->linkedin_url)" :error="$errors->first('linkedin_url')" />
                        <x-profile-input id="github_url" name="github_url" type="url" label="GitHub"
                            placeholder="https://github.com/usuario"
                            :value="old('github_url', $estudiante->github_url)" :error="$errors->first('github_url')" />
                        <div class="md:col-span-2">
                            <x-profile-input id="portafolio_url" name="portafolio_url" type="url" label="Portafolio"
                                placeholder="https://tuportafolio.com"
                                :value="old('portafolio_url', $estudiante->portafolio_url)" :error="$errors->first('portafolio_url')" />
                        </div>
                    </div>

                    <div>
                        <label for="cv" class="mb-1 block text-xs text-itsc-slate-500">Currículum (PDF, máximo 2 MB)</label>
                        @if ($estudiante->cv_path)
                            <p class="mb-2 break-words text-sm text-itsc-slate-500">
                                @if ($cvDetails)
                                    Archivo actual: {{ $cvDetails['name'] }} ({{ number_format($cvDetails['size'] / 1024, 0) }} KB).
                                @else
                                    Hay un currículum registrado, pero el archivo no está disponible.
                                @endif
                                <a href="{{ route('estudiante.perfil.cv') }}" target="_blank" rel="noopener" class="ml-1 text-itsc-red-600 hover:text-itsc-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-600">Ver CV</a>
                            </p>
                        @endif
                        <input id="cv" type="file" name="cv" accept="application/pdf"
                            @if ($errors->first('cv')) aria-describedby="cv-error" @endif
                            class="block min-h-10 w-full rounded-lg border border-itsc-slate-300 bg-white px-3 py-2 text-sm text-itsc-slate-700 file:mr-3 file:border-0 file:bg-itsc-slate-50 file:text-itsc-slate-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/15">
                        @if ($errors->first('cv'))
                            <p id="cv-error" role="alert" class="mt-1 text-xs text-red-800">{{ $errors->first('cv') }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-itsc-slate-100 pt-4 sm:flex-row sm:justify-end">
                        <a href="{{ route('estudiante.dashboard') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-itsc-slate-300 bg-white px-3.5 py-2 text-[13px] font-medium text-itsc-slate-700 transition-colors hover:bg-itsc-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/15">Cancelar</a>
                        @if ($estudiante->cv_path)
                            <button type="submit" form="delete-profile-cv" class="inline-flex min-h-10 items-center justify-center rounded-lg px-3.5 py-2 text-[13px] font-medium text-red-800 transition-colors hover:bg-red-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-red-200">
                                Eliminar currículum
                            </button>
                        @endif
                        <button type="submit" :disabled="saving" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-itsc-slate-700 px-3.5 py-2 text-[13px] font-medium text-white transition-colors hover:bg-itsc-slate-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/20 disabled:cursor-wait disabled:opacity-70">
                            <svg x-cloak x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
                            </svg>
                            <span x-text="saving ? 'Guardando…' : 'Guardar cambios'"></span>
                        </button>
                    </div>
                </form>

                @if ($estudiante->cv_path)
                    <form id="delete-profile-cv" method="POST" action="{{ route('estudiante.perfil.cv.destroy') }}"
                        class="hidden" onsubmit="return confirm('¿Eliminar tu currículum? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            </x-profile-card>

            @include('estudiante.perfil.partials.formaciones')
            @include('estudiante.perfil.partials.experiencias')
            @include('estudiante.perfil.partials.habilidades')

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @include('estudiante.perfil.partials.idiomas')
                @include('estudiante.perfil.partials.certificaciones')
            </div>
        </div>
    </div>
</x-app-layout>
