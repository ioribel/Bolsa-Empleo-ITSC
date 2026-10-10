<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Empresas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                            <th class="px-6 py-3">Empresa</th>
                            <th class="px-6 py-3">RNC</th>
                            <th class="px-6 py-3">Correo</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                        @forelse ($empresas as $empresa)
                            <tr>
                                <td class="px-6 py-4">{{ $empresa->nombre_empresa }}</td>
                                <td class="px-6 py-4">{{ $empresa->rnc }}</td>
                                <td class="px-6 py-4">{{ $empresa->user->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($empresa->estado === 'aprobada') bg-green-100 text-green-800
                                        @elseif($empresa->estado === 'pendiente') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($empresa->estado) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($empresa->estado === 'pendiente')
                                            <form method="POST" action="{{ route('admin.empresas.aprobar', $empresa) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-sm underline text-green-600">Aprobar</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.empresas.rechazar', $empresa) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-sm underline text-red-600">Rechazar</button>
                                            </form>
                                        @elseif ($empresa->estado === 'aprobada')
                                            <form method="POST" action="{{ route('admin.empresas.bloquear', $empresa) }}" class="inline"
                                                  onsubmit="return confirm('¿Bloquear esta empresa? No podrá publicar vacantes.');">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-sm underline text-orange-600">Bloquear</button>
                                            </form>
                                        @elseif ($empresa->estado === 'bloqueada')
                                            <form method="POST" action="{{ route('admin.empresas.aprobar', $empresa) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-sm underline text-green-600">Reactivar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No hay empresas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>