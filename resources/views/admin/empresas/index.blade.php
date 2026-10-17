<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#3f4041] leading-tight">
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

            <section class="mb-6 rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800 sm:p-6">
                <form method="GET" action="{{ route('admin.empresas.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Buscar
                        <input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Empresa, RNC o correo"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    </label>
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Estado
                        <select name="estado" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" @selected(($filters['estado'] ?? '') === 'pendiente')>Pendiente</option>
                            <option value="aprobada" @selected(($filters['estado'] ?? '') === 'aprobada')>Aprobada</option>
                            <option value="rechazada" @selected(($filters['estado'] ?? '') === 'rechazada')>Rechazada</option>
                            <option value="bloqueada" @selected(($filters['estado'] ?? '') === 'bloqueada')>Bloqueada</option>
                        </select>
                    </label>
                    <div class="flex items-end gap-3">
                        <button type="submit" class="inline-flex min-h-10 items-center rounded-md bg-[#042C53] px-4 text-sm font-semibold text-white hover:bg-[#0a3d70] focus:bg-[#0a3d70] focus:outline-none focus:ring-2 focus:ring-[#A32D2D] focus:ring-offset-2 active:bg-[#021d38]">Filtrar</button>
                        <a href="{{ route('admin.empresas.index') }}" class="inline-flex min-h-10 items-center text-sm font-medium text-gray-900 hover:underline dark:text-gray-300">Limpiar</a>
                    </div>
                </form>
                @if ($errors->any())
                    <p class="mt-3 text-sm text-red-600">Revisa los filtros seleccionados e inténtalo de nuevo.</p>
                @endif
            </section>

            <p class="mb-3 text-sm text-gray-900 dark:text-gray-100">{{ $empresas->total() }} {{ $empresas->total() === 1 ? 'empresa' : 'empresas' }}</p>
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
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No se encontraron empresas con esos filtros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $empresas->links() }}</div>
        </div>
    </div>
</x-app-layout>