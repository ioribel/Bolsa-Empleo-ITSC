<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Vacantes para tu carrera') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($vacantes as $vacante)
                <a href="{{ route('estudiante.vacantes.show', $vacante) }}"
                   class="block bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $vacante->titulo }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $vacante->empresa->nombre_empresa }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full">
                            {{ ucfirst($vacante->modalidad) }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">{{ $vacante->descripcion }}</p>
                </a>
            @empty
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    No hay vacantes publicadas para tu carrera en este momento.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>