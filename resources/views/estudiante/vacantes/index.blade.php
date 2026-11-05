<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-500 dark:text-gray-500 leading-tight">
            {{ __('Vacantes para tu carrera') }}
        </h1>
    </x-slot>

    <div class="itsc-page py-8 sm:py-10">
        <section class="mx-auto max-w-7xl space-y-3 px-4 sm:px-6 lg:px-8">
            <section class="itsc-surface p-4 sm:p-5">
                <form method="GET" action="{{ route('estudiante.vacantes.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Buscar
                        <input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Título, empresa o descripción"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    </label>
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Empresa
                        <select name="empresa" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Todas las empresas</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(($filters['empresa'] ?? '') == $empresa->id)>{{ $empresa->nombre_empresa }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Modalidad
                        <select name="modalidad" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Todas</option>
                            <option value="presencial" @selected(($filters['modalidad'] ?? '') === 'presencial')>Presencial</option>
                            <option value="remoto" @selected(($filters['modalidad'] ?? '') === 'remoto')>Remoto</option>
                            <option value="hibrido" @selected(($filters['modalidad'] ?? '') === 'hibrido')>Híbrido</option>
                        </select>
                    </label>
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Tipo de empleo
                        <select name="tipo_empleo" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Todos</option>
                            <option value="tiempo_completo" @selected(($filters['tipo_empleo'] ?? '') === 'tiempo_completo')>Tiempo completo</option>
                            <option value="medio_tiempo" @selected(($filters['tipo_empleo'] ?? '') === 'medio_tiempo')>Medio tiempo</option>
                            <option value="pasantia" @selected(($filters['tipo_empleo'] ?? '') === 'pasantia')>Pasantía</option>
                        </select>
                    </label>
                    <div class="flex items-end gap-3">
                        <button type="submit" class="inline-flex min-h-10 items-center rounded-lg bg-itsc-slate-700 px-4 text-sm font-medium text-white transition-colors hover:bg-itsc-slate-900">Filtrar</button>
                        <a href="{{ route('estudiante.vacantes.index') }}" class="inline-flex min-h-10 items-center text-sm font-medium text-itsc-slate-700 hover:text-itsc-red-700">Limpiar</a>
                    </div>
                </form>
                @if ($errors->any())
                    <p role="alert" class="mt-3 text-sm text-red-600">Revisa los filtros seleccionados e inténtalo de nuevo.</p>
                @endif
            </section>

            <p class="text-sm text-itsc-slate-500">{{ $vacantes->total() }} {{ $vacantes->total() === 1 ? 'vacante activa' : 'vacantes activas' }}</p>
            @forelse ($vacantes as $vacante)
            <div class="itsc-surface p-4 transition-colors hover:border-itsc-slate-300 sm:p-5">
                    <a href="{{ route('estudiante.vacantes.show', $vacante) }}" class="block">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <h3 class="text-base font-medium text-itsc-slate-900">{{ $vacante->titulo }}</h3>
                                <p class="text-sm text-itsc-slate-500">{{ $vacante->empresa->nombre_empresa }}</p>
                            </div>
                            <span class="rounded-full bg-itsc-slate-100 px-2 py-1 text-xs text-itsc-slate-700">
                                {{ ucfirst($vacante->modalidad) }}
                            </span>
                        </div>
                        <p class="mt-2 line-clamp-2 text-sm text-itsc-slate-700">{{ $vacante->descripcion }}</p>
                    </a>
                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('estudiante.empresas.show', $vacante->empresa) }}" class="inline-flex min-h-10 items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800 transition-colors hover:bg-amber-100">
                            Ver perfil de la empresa
                        </a>
                    </div>
                </div>
            @empty
                <div class="itsc-surface p-6 text-center text-sm text-itsc-slate-500">
                    {{ count($filters) ? 'No se encontraron vacantes con esos filtros.' : 'No hay vacantes publicadas para tu carrera en este momento.' }}
                </div>
            @endforelse
            <div>{{ $vacantes->links() }}</div>
        </section>
    </div>
</x-app-layout>