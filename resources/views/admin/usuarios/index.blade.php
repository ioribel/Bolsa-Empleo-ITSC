<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                            <th class="px-6 py-3">Nombre</th>
                            <th class="px-6 py-3">Correo</th>
                            <th class="px-6 py-3">Rol</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                        @forelse ($usuarios as $usuario)
                            <tr>
                                <td class="px-6 py-4">{{ $usuario->name }}</td>
                                <td class="px-6 py-4">{{ $usuario->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-700 rounded-full">{{ ucfirst($usuario->rol) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($usuario->email_verified_at)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Activo</span>
                                    @else
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Bloqueado</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($usuario->email_verified_at)
                                        <form method="POST" action="{{ route('admin.usuarios.bloquear', $usuario) }}"
                                              onsubmit="return confirm('¿Bloquear a este usuario?');">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-sm underline text-red-600">Bloquear</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.usuarios.activar', $usuario) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-sm underline text-green-600">Activar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No hay otros usuarios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>