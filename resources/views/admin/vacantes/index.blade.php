<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-black">Vacantes activas</h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <main class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800 sm:p-6">
                <form method="GET" action="{{ route('admin.vacantes.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                        Carrera
                        <select name="carrera" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Todas las carreras</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id }}" @selected(($filters['carrera'] ?? '') == $carrera->id)>{{ $carrera->nombre }}</option>
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
                        <a href="{{ route('admin.vacantes.index') }}" class="inline-flex min-h-10 items-center text-sm font-medium text-gray-900 hover:underline dark:text-gray-300">Limpiar</a>
                    </div>
                </form>
                @if ($errors->any())
                    <p class="mt-3 text-sm text-red-600">Revisa los filtros seleccionados e inténtalo de nuevo.</p>
                @endif
            </section>

            <div class="flex items-center justify-between gap-3">
                <p class="text-sm text-black">{{ $vacantes->total() }} {{ $vacantes->total() === 1 ? 'vacante activa' : 'vacantes activas' }}</p>
            </div>

            <section class="space-y-4">
                @forelse ($vacantes as $vacante)
                    <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $vacante->titulo }}</h3>
                                <p class="mt-1 text-sm font-medium text-blue-700 dark:text-blue-300">{{ $vacante->empresa->nombre_empresa }}</p>
                            </div>
                            <span class="inline-flex w-fit items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Publicada</span>
                        </div>

                        <p class="mt-4 whitespace-pre-line text-sm leading-6 text-gray-900 dark:text-gray-300">{{ $vacante->descripcion }}</p>
                        @if ($vacante->requisitos)
                            <div class="mt-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Requisitos</h4>
                                <p class="mt-1 whitespace-pre-line text-sm leading-6 text-gray-900 dark:text-gray-300">{{ $vacante->requisitos }}</p>
                            </div>
                        @endif

                        <dl class="mt-5 grid grid-cols-1 gap-x-6 gap-y-3 border-t border-gray-200 pt-4 text-sm dark:border-gray-700 sm:grid-cols-2 lg:grid-cols-3">
                            <div><dt class="font-medium text-gray-700 dark:text-gray-400">Tipo de empleo</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">{{ ['tiempo_completo' => 'Tiempo completo', 'medio_tiempo' => 'Medio tiempo', 'pasantia' => 'Pasantía'][$vacante->tipo_empleo] }}</dd></div>
                            <div><dt class="font-medium text-gray-700 dark:text-gray-400">Modalidad</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">{{ ['presencial' => 'Presencial', 'remoto' => 'Remoto', 'hibrido' => 'Híbrido'][$vacante->modalidad] }}</dd></div>
                            <div><dt class="font-medium text-gray-700 dark:text-gray-400">Ubicación</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $vacante->ubicacion ?: 'No especificada' }}</dd></div>
                            <div>
                                <dt class="font-medium text-gray-700 dark:text-gray-400">Salario</dt>
                                <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                    @if ($vacante->salario_min || $vacante->salario_max)
                                        {{ $vacante->salario_min ? 'RD$ '.number_format((float) $vacante->salario_min, 2) : 'Sin mínimo' }}
                                        @if ($vacante->salario_max) - RD$ {{ number_format((float) $vacante->salario_max, 2) }} @endif
                                    @else
                                        No especificado
                                    @endif
                                </dd>
                            </div>
                            <div><dt class="font-medium text-gray-700 dark:text-gray-400">Cierre</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $vacante->fecha_cierre?->format('d/m/Y') ?? 'Sin fecha definida' }}</dd></div>
                            <div><dt class="font-medium text-gray-700 dark:text-gray-400">Postulaciones</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $vacante->postulaciones_count }}</dd></div>
                            <div class="sm:col-span-2 lg:col-span-3">
                                <dt class="font-medium text-gray-700 dark:text-gray-400">Carreras</dt>
                                <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $vacante->carreras->pluck('nombre')->join(', ') ?: 'Sin carreras asociadas' }}</dd>
                            </div>
                        </dl>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        No se encontraron vacantes activas con esos filtros.
                    </div>
                @endforelse
            </section>

            <div>{{ $vacantes->links() }}</div>
        </main>
    </div>
</x-app-layout>