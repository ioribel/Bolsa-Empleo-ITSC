<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Carreras') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Agregar carrera</h3>
                <form method="POST" action="{{ route('admin.carreras.store') }}" class="flex gap-3">
                    @csrf
                    <input type="text" name="nombre" placeholder="Nombre de la carrera" required
                        class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                    <x-primary-button>Agregar</x-primary-button>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                            <th class="px-6 py-3">Nombre</th>
                            <th class="px-6 py-3">Estudiantes</th>
                            <th class="px-6 py-3">Vacantes</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                        @forelse ($carreras as $carrera)
                            <tr>
                                <td class="px-6 py-4">
                                    <span id="texto-{{ $carrera->id }}">{{ $carrera->nombre }}</span>
                                    <form id="form-{{ $carrera->id }}" method="POST" action="{{ route('admin.carreras.update', $carrera) }}" class="hidden">
                                        @csrf @method('PATCH')
                                        <input type="text" name="nombre" value="{{ $carrera->nombre }}" required
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
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">No hay carreras registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleEdit(id) {
            document.getElementById('texto-' + id).classList.toggle('hidden');
            document.getElementById('form-' + id).classList.toggle('hidden');
        }
    </script>
</x-app-layout>