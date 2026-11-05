<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-xl font-semibold leading-tight text-slate-700 dark:text-slate-200">
                {{ __('Perfil de la empresa') }}
            </h1>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-6 dark:border-slate-700 dark:bg-slate-800/80">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                        <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white text-2xl font-bold text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            @if ($empresa->logo_path)
                                <img src="{{ asset('storage/' . $empresa->logo_path) }}" alt="Logo de {{ $empresa->nombre_empresa }}" width="80" height="80" class="h-full w-full object-cover">
                            @else
                                {{ strtoupper(substr($empresa->nombre_empresa, 0, 1)) }}
                            @endif
                        </div>

                        <div class="space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-700 dark:text-blue-300">
                                Organización
                            </p>
                            <h2 class="text-3xl font-bold text-slate-900 dark:text-white">
                                {{ $empresa->nombre_empresa }}
                            </h2>
                            @if ($empresa->sector)
                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    {{ $empresa->sector }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-[1.35fr_0.65fr]">
                    <div class="space-y-6">
                        <section class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Sobre la empresa</h2>
                            <p class="mt-3 text-sm leading-7 text-slate-700 dark:text-slate-300">
                                {{ $empresa->descripcion ?: 'La empresa aún no ha publicado una descripción institucional.' }}
                            </p>
                        </section>

                        <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
                            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Vacantes activas</h2>

                            @if ($vacantes->isEmpty())
                                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                                    Actualmente no hay vacantes activas relacionadas con tu carrera en esta empresa.
                                </p>
                            @else
                                <div class="mt-4 space-y-3">
                                    @foreach ($vacantes as $vacante)
                                        <article class="rounded-xl border border-blue-100 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/30">
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                                                        {{ $vacante->titulo }}
                                                    </h3>
                                                    <div class="mt-1 flex flex-wrap gap-2 text-xs text-slate-600 dark:text-slate-300">
                                                        <span class="rounded-full bg-white px-2 py-1 dark:bg-slate-800">
                                                            {{ ucfirst(str_replace('_', ' ', $vacante->tipo_empleo)) }}
                                                        </span>
                                                        <span class="rounded-full bg-white px-2 py-1 dark:bg-slate-800">
                                                            {{ ucfirst($vacante->modalidad) }}
                                                        </span>
                                                        @if ($vacante->ubicacion)
                                                            <span class="rounded-full bg-white px-2 py-1 dark:bg-slate-800">
                                                                {{ $vacante->ubicacion }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <a href="{{ route('estudiante.vacantes.show', $vacante) }}" class="inline-flex items-center justify-center rounded-md bg-blue-700 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                    Ver vacante
                                                </a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    </div>

                    <aside class="space-y-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Datos de contacto</h2>
                            <ul class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-300">
                                @if ($empresa->telefono)
                                    <li class="flex items-center gap-3">
                                        <span class="font-medium text-slate-500 dark:text-slate-400">Teléfono:</span>
                                        <span>{{ $empresa->telefono }}</span>
                                    </li>
                                @endif
                                @if ($empresa->sitio_web)
                                    <li class="flex items-center gap-3">
                                        <span class="font-medium text-slate-500 dark:text-slate-400">Sitio web:</span>
                                        <a href="{{ str_starts_with($empresa->sitio_web, ['http://', 'https://']) ? $empresa->sitio_web : 'https://' . $empresa->sitio_web }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline decoration-2 underline-offset-2 hover:text-blue-600 dark:text-blue-300">
                                            {{ $empresa->sitio_web }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Resumen</h2>
                            <dl class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-300">
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-slate-500 dark:text-slate-400">Estado</dt>
                                    <dd class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900 dark:text-emerald-200">
                                        Aprobada
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-slate-500 dark:text-slate-400">Vacantes activas</dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">{{ $vacantes->count() }}</dd>
                                </div>
                            </dl>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
