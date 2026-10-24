<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-500 dark:text-gray-500 leading-tight">
            {{ __('Mis Postulaciones') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <section class="mb-6 rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800 sm:p-6">
                <form method="GET" action="{{ route('estudiante.postulaciones.index') }}" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Buscar
                        <input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Vacante o empresa"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    </label>
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Estado
                        <select name="estado" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Todos los estados</option>
                            <option value="recibida" @selected(($filters['estado'] ?? '') === 'recibida')>Recibida</option>
                            <option value="en_revision" @selected(($filters['estado'] ?? '') === 'en_revision')>En revisión</option>
                            <option value="aceptada" @selected(($filters['estado'] ?? '') === 'aceptada')>Aceptada</option>
                            <option value="rechazada" @selected(($filters['estado'] ?? '') === 'rechazada')>Rechazada</option>
                        </select>
                    </label>
                    <div class="flex items-end gap-3">
                        <button type="submit" class="inline-flex min-h-10 items-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Filtrar</button>
                        <a href="{{ route('estudiante.postulaciones.index') }}" class="inline-flex min-h-10 items-center text-sm font-medium text-gray-900 hover:underline dark:text-gray-300">Limpiar</a>
                    </div>
                </form>
                @if ($errors->any())
                    <p class="mt-3 text-sm text-red-600">Revisa los filtros seleccionados e inténtalo de nuevo.</p>
                @endif
            </section>

            <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">
                {{ $postulaciones->total() }} {{ $postulaciones->total() === 1 ? 'postulación' : 'postulaciones' }}
            </p>
            <div class="space-y-3 lg:hidden">
                @forelse ($postulaciones as $p)
                    <article class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                        <h3 class="break-words font-semibold text-gray-900 dark:text-gray-100">
                            {{ $p->vacante->titulo }}
                        </h3>
                        <p class="mt-1 break-words text-sm text-gray-600 dark:text-gray-300">
                            {{ $p->vacante->empresa->nombre_empresa }}
                        </p>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-3 dark:border-gray-700">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($p->estado === 'aceptada') bg-green-100 text-green-800
                                @elseif($p->estado === 'rechazada') bg-red-100 text-red-800
                                @elseif($p->estado === 'en_revision') bg-blue-100 text-blue-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $p->estado)) }}
                            </span>
                            <time class="text-sm text-gray-500 dark:text-gray-400" datetime="{{ $p->created_at->toDateString() }}">
                                {{ $p->created_at->format('d/m/Y') }}
                            </time>
                        </div>
                    </article>
                @empty
                    <p class="rounded-lg bg-white p-6 text-center text-sm text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-400">
                        {{ count($filters) ? 'No se encontraron postulaciones con esos filtros.' : 'Todavía no te has postulado a ninguna vacante.' }}
                    </p>
                @endforelse
            </div>

            <div class="hidden overflow-hidden bg-white shadow-sm dark:bg-gray-800 lg:block lg:rounded-lg">
                <table class="hidden min-w-full divide-y divide-gray-200 dark:divide-gray-700 lg:table">
                    <thead>
                        <tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                            <th class="px-6 py-3">Vacante</th>
                            <th class="px-6 py-3">Empresa</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                        @forelse ($postulaciones as $p)
                            <tr>
                                <td class="px-6 py-4">{{ $p->vacante->titulo }}</td>
                                <td class="px-6 py-4">{{ $p->vacante->empresa->nombre_empresa }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($p->estado === 'aceptada') bg-green-100 text-green-800
                                        @elseif($p->estado === 'rechazada') bg-red-100 text-red-800
                                        @elseif($p->estado === 'en_revision') bg-blue-100 text-blue-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $p->estado)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $p->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    {{ count($filters) ? 'No se encontraron postulaciones con esos filtros.' : 'Todavía no te has postulado a ninguna vacante.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $postulaciones->links() }}</div>
        </div>
    </div>
</x-app-layout>