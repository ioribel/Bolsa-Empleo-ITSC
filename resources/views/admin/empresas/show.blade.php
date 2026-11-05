<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-xl font-semibold leading-tight text-slate-700 dark:text-slate-200">
                {{ __('Perfil de empresa') }}
            </h1>
            <a href="{{ route('admin.empresas.index') }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Volver a empresas
            </a>
        </div>
    </x-slot>

    <div class="itsc-page py-8 sm:py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @php
                $estadoBadge = match ($empresa->estado) {
                    'aprobada' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'darkBg' => 'dark:bg-emerald-900', 'darkText' => 'dark:text-emerald-200'],
                    'pendiente' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'darkBg' => 'dark:bg-amber-900', 'darkText' => 'dark:text-amber-200'],
                    default => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'darkBg' => 'dark:bg-red-900', 'darkText' => 'dark:text-red-200'],
                };
            @endphp

            <div class="itsc-surface overflow-hidden">
                <div class="border-b border-itsc-slate-100 bg-itsc-slate-50 px-4 py-5 sm:px-6">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                        <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white text-2xl font-bold text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            @if ($empresa->logo_path)
                                <img src="{{ asset('storage/' . $empresa->logo_path) }}" alt="Logo de {{ $empresa->nombre_empresa }}" width="80" height="80" class="h-full w-full object-cover">
                            @else
                                {{ strtoupper(substr($empresa->nombre_empresa, 0, 1)) }}
                            @endif
                        </div>

                        <div class="space-y-2">
                            <p class="text-xs font-medium text-itsc-slate-500">
                                Registro corporativo
                            </p>
                            <h2 class="text-xl font-medium text-itsc-slate-900">
                                {{ $empresa->nombre_empresa }}
                            </h2>
                            @if ($empresa->sector)
                                <p class="text-sm text-itsc-slate-500">
                                    {{ $empresa->sector }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 p-4 sm:p-5 lg:grid-cols-[1.35fr_0.65fr]">
                    <div class="space-y-3">
                        <section class="rounded-xl border border-itsc-slate-100 bg-white p-4">
                            <h2 class="text-[15px] font-medium text-itsc-slate-900">Descripción institucional</h2>
                            <p class="mt-3 text-sm leading-6 text-itsc-slate-700">
                                {{ $empresa->descripcion ?: 'Esta empresa aún no ha publicado una descripción institucional.' }}
                            </p>
                        </section>

                        <section class="rounded-xl border border-itsc-slate-100 bg-white p-4">
                            <h2 class="text-[15px] font-medium text-itsc-slate-900">Vacantes registradas</h2>

                            @if ($empresa->vacantes->isEmpty())
                                <p class="mt-3 text-sm text-itsc-slate-500">
                                    Esta empresa no tiene vacantes registradas.
                                </p>
                            @else
                                <div class="mt-3 space-y-3">
                                    @foreach ($empresa->vacantes as $vacante)
                                        <article class="rounded-xl border border-itsc-slate-100 bg-itsc-slate-50 p-3">
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <h3 class="text-sm font-medium text-itsc-slate-900">
                                                        {{ $vacante->titulo }}
                                                    </h3>
                                                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-itsc-slate-500">
                                                        <span class="rounded-full bg-white px-2 py-1">
                                                            {{ ucfirst($vacante->estado) }}
                                                        </span>
                                                        <span class="rounded-full bg-white px-2 py-1">
                                                            {{ ucfirst(str_replace('_', ' ', $vacante->tipo_empleo)) }}
                                                        </span>
                                                        <span class="rounded-full bg-white px-2 py-1">
                                                            {{ ucfirst($vacante->modalidad) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    </div>

                    <aside class="space-y-3">
                        <div class="rounded-xl border border-itsc-slate-100 bg-white p-4">
                            <h2 class="text-[15px] font-medium text-itsc-slate-900">Datos de la empresa</h2>
                            <ul class="mt-4 space-y-3 text-sm text-itsc-slate-700">
                                <li class="flex justify-between gap-3">
                                    <span class="text-slate-500 dark:text-slate-400">Estado</span>
                                    <span class="rounded-full {{ $estadoBadge['bg'] }} {{ $estadoBadge['text'] }} {{ $estadoBadge['darkBg'] }} {{ $estadoBadge['darkText'] }} px-2 py-1 text-xs font-semibold">
                                        {{ ucfirst($empresa->estado) }}
                                    </span>
                                </li>
                                @if ($empresa->rnc)
                                    <li class="flex justify-between gap-3">
                                        <span class="text-slate-500 dark:text-slate-400">RNC</span>
                                        <span>{{ $empresa->rnc }}</span>
                                    </li>
                                @endif
                                @if ($empresa->telefono)
                                    <li class="flex justify-between gap-3">
                                        <span class="text-slate-500 dark:text-slate-400">Teléfono</span>
                                        <span>{{ $empresa->telefono }}</span>
                                    </li>
                                @endif
                                @if ($empresa->sitio_web)
                                    <li class="flex justify-between gap-3">
                                        <span class="text-slate-500 dark:text-slate-400">Web</span>
                                        <a href="{{ str_starts_with($empresa->sitio_web, ['http://', 'https://']) ? $empresa->sitio_web : 'https://' . $empresa->sitio_web }}" target="_blank" rel="noopener noreferrer" class="max-w-[12rem] truncate text-blue-700 underline decoration-2 underline-offset-2 hover:text-blue-600 dark:text-blue-300">
                                            {{ $empresa->sitio_web }}
                                        </a>
                                    </li>
                                @endif
                                @if ($empresa->user)
                                    <li class="flex justify-between gap-3">
                                        <span class="text-slate-500 dark:text-slate-400">Correo</span>
                                        <span class="max-w-[12rem] truncate">{{ $empresa->user->email }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
