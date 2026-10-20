<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-500 dark:text-gray-500 leading-tight">
            {{ __('Mis Postulaciones') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
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
                        Todavía no te has postulado a ninguna vacante.
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
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Todavía no te has postulado a ninguna vacante.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>