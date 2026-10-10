<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $vacante->titulo }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                @if (session('status'))
                    <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="p-4 bg-red-100 text-red-800 rounded-md">{{ session('error') }}</div>
                @endif

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $vacante->empresa->nombre_empresa }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Descripción</h3>
                    <p class="text-gray-900 dark:text-gray-100">{{ $vacante->descripcion }}</p>
                </div>

                @if ($vacante->requisitos)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Requisitos</h3>
                        <p class="text-gray-900 dark:text-gray-100">{{ $vacante->requisitos }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Tipo:</span> {{ ucfirst(str_replace('_', ' ', $vacante->tipo_empleo)) }}</div>
                    <div><span class="text-gray-500">Modalidad:</span> {{ ucfirst($vacante->modalidad) }}</div>
                    <div><span class="text-gray-500">Ubicación:</span> {{ $vacante->ubicacion ?? '—' }}</div>
                    <div><span class="text-gray-500">Cierra:</span> {{ $vacante->fecha_cierre?->format('d/m/Y') ?? '—' }}</div>
                </div>

                <hr class="dark:border-gray-700">

                @if ($yaPostulado)
                    <div class="p-4 bg-blue-100 text-blue-800 rounded-md">
                        Ya te has postulado a esta vacante. Revisa el estado en "Mis Postulaciones".
                    </div>
                @else
                    <form method="POST" action="{{ route('estudiante.vacantes.postular', $vacante) }}">
                        @csrf
                        <div>
                            <x-input-label for="carta_presentacion" :value="__('Carta de presentación (opcional)')" />
                            <textarea id="carta_presentacion" name="carta_presentacion" rows="4"
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('carta_presentacion') }}</textarea>
                        </div>
                        <x-primary-button class="mt-4">{{ __('Postularme') }}</x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>