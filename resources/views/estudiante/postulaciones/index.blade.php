<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-500 dark:text-gray-500 leading-tight">
            {{ __('Mis Postulaciones') }}
        </h1>
    </x-slot>

    <div class="itsc-page py-6 sm:py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <section class="itsc-surface mb-4 p-4 sm:p-5">
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
                        <button type="submit" class="inline-flex min-h-10 items-center rounded-lg bg-itsc-slate-700 px-4 text-sm font-medium text-white transition-colors hover:bg-itsc-slate-900">Filtrar</button>
                        <a href="{{ route('estudiante.postulaciones.index') }}" class="inline-flex min-h-10 items-center text-sm font-medium text-itsc-slate-700 hover:text-itsc-red-700">Limpiar</a>
                    </div>
                </form>
                @if ($errors->any())
                    <p role="alert" class="mt-3 text-sm text-red-600">Revisa los filtros seleccionados e inténtalo de nuevo.</p>
                @endif
            </section>

            <p class="itsc-count mb-3 text-sm">
                {{ $postulaciones->total() }} {{ $postulaciones->total() === 1 ? 'postulación' : 'postulaciones' }}
            </p>
            <div class="space-y-3 lg:hidden">
                @forelse ($postulaciones as $p)
                    <article class="itsc-surface p-4">
                        <h3 class="break-words text-sm font-medium text-itsc-slate-900">
                            {{ $p->vacante->titulo }}
                        </h3>
                        <p class="mt-1 break-words text-sm text-itsc-slate-500">
                            {{ $p->vacante->empresa->nombre_empresa }}
                        </p>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-itsc-slate-100 pt-3">
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
                    <p class="itsc-surface p-6 text-center text-sm text-itsc-slate-500">
                        {{ count($filters) ? 'No se encontraron postulaciones con esos filtros.' : 'Todavía no te has postulado a ninguna vacante.' }}
                    </p>
                @endforelse
            </div>

            <div class="itsc-surface hidden overflow-hidden lg:block">
                <table class="hidden min-w-full divide-y divide-itsc-slate-100 lg:table">
                    <thead>
                        <tr class="text-left text-xs text-itsc-slate-500">
                            <th class="px-6 py-3">Vacante</th>
                            <th class="px-6 py-3">Empresa</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-itsc-slate-100 text-itsc-slate-900">
                        @forelse ($postulaciones as $p)
                            <tr class="transition-colors hover:bg-itsc-slate-50">
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
                                <td colspan="4" class="px-6 py-8 text-center text-itsc-slate-500">
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