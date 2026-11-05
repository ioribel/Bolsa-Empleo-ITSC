<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-[#3f4041] leading-tight">
            {{ __('Carreras') }}
        </h1>
    </x-slot>

    <div class="itsc-page py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div role="status" aria-live="polite" class="itsc-surface mb-4 p-4 text-sm text-itsc-slate-700">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div role="alert" class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="itsc-surface mb-4 p-4 sm:p-5">
                <h3 class="mb-3 text-[15px] font-medium text-itsc-slate-900">Agregar carrera</h3>
                <form method="POST" action="{{ route('admin.carreras.store') }}" class="flex flex-col gap-3 sm:flex-row">
                    @csrf
                    <label for="nueva-carrera" class="sr-only">Nombre de la carrera</label>
                    <input id="nueva-carrera" type="text" name="nombre" required
                        class="w-full min-w-0 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm sm:flex-1">
                    <x-primary-button class="itsc-primary w-full justify-center whitespace-nowrap sm:w-auto">Agregar</x-primary-button>
                </form>
            </div>

            <section class="itsc-surface mb-4 p-4 sm:p-5">
                <form method="GET" action="{{ route('admin.carreras.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="text-sm font-medium text-gray-900 dark:text-gray-200">
                        Buscar
                        <input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Nombre de la carrera…"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    </label>
                    <div class="flex items-stretch gap-3 self-end">
                        <x-primary-button class="itsc-primary min-h-10">Filtrar</x-primary-button>
                        <a href="{{ route('admin.carreras.index') }}" class="inline-flex min-h-10 items-center text-sm font-medium text-gray-900 hover:underline dark:text-gray-300">Limpiar</a>
                    </div>
                </form>
                @if ($errors->any())
                    <p role="alert" class="mt-3 text-sm text-red-600">Revisa los filtros seleccionados e inténtalo de nuevo.</p>
                @endif
            </section>

            <p class="itsc-count mb-3 text-sm">{{ $carreras->total() }} {{ $carreras->total() === 1 ? 'carrera' : 'carreras' }}</p>
            <div class="space-y-3 lg:hidden">
                @forelse ($carreras as $carrera)
                    <article class="itsc-surface p-4">
                        <div id="texto-mobile-{{ $carrera->id }}" class="break-words font-semibold text-gray-900 dark:text-gray-100">
                            {{ $carrera->nombre }}
                        </div>
                        <form id="form-mobile-{{ $carrera->id }}" method="POST" action="{{ route('admin.carreras.update', $carrera) }}" class="hidden space-y-3">
                            @csrf @method('PATCH')
                            <label for="career-name-mobile-{{ $carrera->id }}" class="sr-only">Nombre de la carrera</label>
                            <input id="career-name-mobile-{{ $carrera->id }}" type="text" name="nombre" value="{{ $carrera->nombre }}" required
                                class="w-full min-w-0 rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <button type="submit" class="min-h-10 rounded-lg bg-itsc-slate-700 px-3 text-[13px] font-medium text-white hover:bg-itsc-slate-900">Guardar</button>
                        </form>
                        <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Estudiantes</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $carrera->estudiantes_count }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Vacantes</dt>
                                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $carrera->vacantes_count }}</dd>
                            </div>
                        </dl>
                        <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 border-t border-itsc-slate-100 pt-3">
                            <button type="button" onclick="toggleEdit({{ $carrera->id }}, 'mobile')" class="min-h-10 rounded-lg px-3 text-[13px] font-medium text-itsc-slate-700 hover:bg-itsc-slate-50">Editar</button>
                            <form method="POST" action="{{ route('admin.carreras.destroy', $carrera) }}"
                                  onsubmit="return confirm('¿Eliminar esta carrera?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="min-h-10 rounded-lg px-3 text-[13px] font-medium text-red-800 hover:bg-red-50">Eliminar</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="itsc-surface p-6 text-center text-sm text-itsc-slate-500">
                        No se encontraron carreras con esos filtros.
                    </p>
                @endforelse
            </div>

            <div class="itsc-surface hidden overflow-hidden lg:block">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-itsc-slate-100">
                    <thead>
                        <tr class="text-left text-xs text-itsc-slate-500">
                            <th class="px-6 py-3">Nombre</th>
                            <th class="px-6 py-3">Estudiantes</th>
                            <th class="px-6 py-3">Vacantes</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-itsc-slate-100 text-itsc-slate-900">
                        @forelse ($carreras as $carrera)
                            <tr class="transition-colors hover:bg-itsc-slate-50">
                                <td class="px-6 py-4">
                                    <span id="texto-{{ $carrera->id }}">{{ $carrera->nombre }}</span>
                                    <form id="form-{{ $carrera->id }}" method="POST" action="{{ route('admin.carreras.update', $carrera) }}" class="hidden">
                                        @csrf @method('PATCH')
                                        <label for="career-name-{{ $carrera->id }}" class="sr-only">Nombre de la carrera</label>
                                        <input id="career-name-{{ $carrera->id }}" type="text" name="nombre" value="{{ $carrera->nombre }}" required
                                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                        <button type="submit" class="text-sm underline text-green-600 ml-2">Guardar</button>
                                    </form>
                                </td>
                                <td class="px-6 py-4">{{ $carrera->estudiantes_count }}</td>
                                <td class="px-6 py-4">{{ $carrera->vacantes_count }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <button type="button" onclick="toggleEdit({{ $carrera->id }})" class="text-sm underline text-indigo-600">Editar</button>
                                        <form method="POST" action="{{ route('admin.carreras.destroy', $carrera) }}"
                                              onsubmit="return confirm('¿Eliminar esta carrera?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-sm underline text-red-600">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">No se encontraron carreras con esos filtros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            <div class="mt-4">{{ $carreras->links() }}</div>
        </div>
    </div>

    <script>
        function toggleEdit(id, view = '') {
            const prefix = view ? view + '-' : '';
            document.getElementById('texto-' + prefix + id).classList.toggle('hidden');
            document.getElementById('form-' + prefix + id).classList.toggle('hidden');
        }
    </script>
</x-app-layout>