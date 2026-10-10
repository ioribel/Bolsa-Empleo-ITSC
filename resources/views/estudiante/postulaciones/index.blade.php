<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mis Postulaciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
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