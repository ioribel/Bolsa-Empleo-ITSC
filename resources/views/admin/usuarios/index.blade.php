<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-[#3f4041] leading-tight">
            {{ __('Usuarios') }}
        </h1>
    </x-slot>

    <div class="itsc-page py-6 sm:py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" aria-live="polite" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <section class="itsc-surface mb-4 p-4 sm:p-5">
                <form method="GET" action="{{ route('admin.usuarios.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Buscar
                        <input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Nombre o correo"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    </label>
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Rol
                        <select name="rol" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Todos los roles</option>
                            <option value="admin" @selected(($filters['rol'] ?? '') === 'admin')>Administrador</option>
                            <option value="estudiante" @selected(($filters['rol'] ?? '') === 'estudiante')>Estudiante</option>
                        </select>
                    </label>
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Estado
                        <select name="estado" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Todos los estados</option>
                            <option value="activo" @selected(($filters['estado'] ?? '') === 'activo')>Activo</option>
                            <option value="bloqueado" @selected(($filters['estado'] ?? '') === 'bloqueado')>Bloqueado</option>
                        </select>
                    </label>
                    <div class="flex items-end gap-3">
                        <button type="submit" class="inline-flex min-h-10 items-center rounded-lg bg-itsc-slate-700 px-4 text-sm font-medium text-white transition-colors hover:bg-itsc-slate-900">Filtrar</button>
                        <a href="{{ route('admin.usuarios.index') }}" class="inline-flex min-h-10 items-center text-sm font-medium text-itsc-slate-700 hover:text-itsc-red-700">Limpiar</a>
                    </div>
                </form>
                @if ($errors->any())
                    <p role="alert" class="mt-3 text-sm text-red-600">Revisa los filtros seleccionados e inténtalo de nuevo.</p>
                @endif
            </section>

            <p class="itsc-count mb-3 text-sm">{{ $usuarios->total() }} {{ $usuarios->total() === 1 ? 'usuario' : 'usuarios' }}</p>
            <div class="space-y-3 lg:hidden">
                @forelse ($usuarios as $usuario)
                    <article class="itsc-surface p-4">
                        <h3 class="break-words text-sm font-medium text-itsc-slate-900">{{ $usuario->name }}</h3>
                        <p class="mt-1 break-all text-sm text-gray-600 dark:text-gray-300">{{ $usuario->email }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="rounded-full bg-gray-200 px-2 py-1 text-xs text-gray-800 dark:bg-gray-700 dark:text-gray-100">
                                {{ ['admin' => 'Administrador', 'estudiante' => 'Estudiante'][$usuario->rol] ?? ucfirst($usuario->rol) }}
                            </span>
                            @if ($usuario->email_verified_at)
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs text-green-800">Activo</span>
                            @else
                                <span class="rounded-full bg-red-100 px-2 py-1 text-xs text-red-800">Bloqueado</span>
                            @endif
                        </div>
                        <div class="mt-4 border-t border-itsc-slate-100 pt-3">
                            @if ($usuario->email_verified_at)
                                <form method="POST" action="{{ route('admin.usuarios.bloquear', $usuario) }}"
                                      onsubmit="return confirm('¿Bloquear a este usuario?');">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-sm font-medium text-red-600 underline">Bloquear</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.usuarios.activar', $usuario) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-sm font-medium text-green-600 underline">Activar</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="itsc-surface p-6 text-center text-sm text-itsc-slate-500">
                        No se encontraron usuarios con esos filtros.
                    </p>
                @endforelse
            </div>

            <div class="itsc-surface hidden overflow-hidden lg:block">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-itsc-slate-100">
                    <thead>
                        <tr class="text-left text-xs text-itsc-slate-500">
                            <th class="px-6 py-3">Nombre</th>
                            <th class="px-6 py-3">Correo</th>
                            <th class="px-6 py-3">Rol</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-itsc-slate-100 text-itsc-slate-900">
                        @forelse ($usuarios as $usuario)
                            <tr class="transition-colors hover:bg-itsc-slate-50">
                                <td class="px-6 py-4">{{ $usuario->name }}</td>
                                <td class="px-6 py-4">{{ $usuario->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-700 rounded-full">{{ ['admin' => 'Administrador', 'estudiante' => 'Estudiante'][$usuario->rol] ?? ucfirst($usuario->rol) }}</span>
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
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No se encontraron usuarios con esos filtros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            <div class="mt-4">{{ $usuarios->links() }}</div>
        </div>
    </div>
</x-app-layout>