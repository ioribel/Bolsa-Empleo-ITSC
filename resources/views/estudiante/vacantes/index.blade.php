<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-black dark:text-black leading-tight">
            {{ __('Vacantes para tu carrera') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <main class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800 sm:p-6">
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
                        <button type="submit" class="inline-flex min-h-10 items-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Filtrar</button>
                        <a href="{{ route('estudiante.vacantes.index') }}" class="inline-flex min-h-10 items-center text-sm font-medium text-gray-900 hover:underline dark:text-gray-300">Limpiar</a>
                    </div>
                </form>
                @if ($errors->any())
                    <p class="mt-3 text-sm text-red-600">Revisa los filtros seleccionados e inténtalo de nuevo.</p>
                @endif
            </section>

            <p class="text-sm text-black dark:text-black">{{ $vacantes->total() }} {{ $vacantes->total() === 1 ? 'vacante activa' : 'vacantes activas' }}</p>
            @forelse ($vacantes as $vacante)
                <a href="{{ route('estudiante.vacantes.show', $vacante) }}"
                   class="block rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md dark:bg-gray-800">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $vacante->titulo }}</h3>
                            <p class="text-sm text-gray-700 dark:text-gray-400">{{ $vacante->empresa->nombre_empresa }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full">
                            {{ ucfirst($vacante->modalidad) }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-900 dark:text-gray-300 line-clamp-2">{{ $vacante->descripcion }}</p>
                </a>
            @empty
                <div class="rounded-lg bg-white p-6 text-center text-gray-900 shadow-sm dark:bg-gray-800 dark:text-gray-300">
                    {{ count($filters) ? 'No se encontraron vacantes con esos filtros.' : 'No hay vacantes publicadas para tu carrera en este momento.' }}
                </div>
            @endforelse
            <div>{{ $vacantes->links() }}</div>
        </main>
    </div>
</x-app-layout>