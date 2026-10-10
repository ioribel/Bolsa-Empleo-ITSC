<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Mis Vacantes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            @if (auth()->user()->empresa->estado !== 'aprobada')
                <div class="mb-4 p-4 bg-yellow-100 text-yellow-800 rounded-md">
                    @if (auth()->user()->empresa->estado === 'pendiente')
                        Tu empresa está pendiente de aprobación por el administrador. Podrás publicar vacantes una vez sea aprobada.
                    @elseif (auth()->user()->empresa->estado === 'bloqueada')
                        Tu empresa ha sido bloqueada por el administrador. Contacta al ITSC para más información.
                    @else
                        Tu empresa no está habilitada para publicar vacantes en este momento.
                    @endif
                </div>
            @else
                <div class="mb-4">
                    <a href="{{ route('empresa.vacantes.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700">
                        {{ __('Nueva Vacante') }}
                    </a>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                            <th class="px-6 py-3">Título</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Postulaciones</th>
                            <th class="px-6 py-3">Cierre</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                        @forelse ($vacantes as $vacante)
                            <tr>
                                <td class="px-6 py-4">{{ $vacante->titulo }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($vacante->estado === 'publicada') bg-green-100 text-green-800
                                        @elseif($vacante->estado === 'borrador') bg-yellow-100 text-yellow-800
                                        @else bg-gray-200 text-gray-700 @endif">
                                        {{ ucfirst($vacante->estado) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ $vacante->postulaciones_count }}</td>
                                <td class="px-6 py-4">{{ $vacante->fecha_cierre ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('empresa.vacantes.show', $vacante) }}" class="text-sm underline text-gray-600 dark:text-gray-400">Ver</a>
                                        <a href="{{ route('empresa.vacantes.edit', $vacante) }}" class="text-sm underline text-indigo-600">Editar</a>
                                        <a href="{{ route('empresa.vacantes.postulaciones.index', $vacante) }}" class="text-sm underline text-blue-600">Postulantes ({{ $vacante->postulaciones_count }})</a>

                                        @if ($vacante->estado === 'borrador')
                                            <form method="POST" action="{{ route('empresa.vacantes.publicar', $vacante) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-sm underline text-green-600">Publicar</button>
                                            </form>
                                        @elseif ($vacante->estado === 'publicada')
                                            <form method="POST" action="{{ route('empresa.vacantes.cerrar', $vacante) }}" class="inline"
                                                  onsubmit="return confirm('¿Cerrar esta vacante? Dejará de aceptar postulaciones.');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-sm underline text-orange-600">Cerrar</button>
                                            </form>
                                        @endif

                                        @if ($vacante->estado === 'borrador')
                                            <form method="POST" action="{{ route('empresa.vacantes.destroy', $vacante) }}" class="inline"
                                                  onsubmit="return confirm('¿Eliminar esta vacante? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm underline text-red-600">Eliminar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    Todavía no has creado ninguna vacante.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>