<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $vacante->titulo }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-4">
                <div>
                    <span class="px-2 py-1 text-xs rounded-full
                        @if($vacante->estado === 'publicada') bg-green-100 text-green-800
                        @elseif($vacante->estado === 'borrador') bg-yellow-100 text-yellow-800
                        @else bg-gray-200 text-gray-700 @endif">
                        {{ ucfirst($vacante->estado) }}
                    </span>
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

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Tipo de empleo</h3>
                        <p class="text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $vacante->tipo_empleo)) }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Modalidad</h3>
                        <p class="text-gray-900 dark:text-gray-100">{{ ucfirst($vacante->modalidad) }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Ubicación</h3>
                    <p class="text-gray-900 dark:text-gray-100">{{ $vacante->ubicacion ?? '—' }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Salario mínimo</h3>
                        <p class="text-gray-900 dark:text-gray-100">{{ $vacante->salario_min ? 'RD$ '.number_format($vacante->salario_min, 2) : '—' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Salario máximo</h3>
                        <p class="text-gray-900 dark:text-gray-100">{{ $vacante->salario_max ? 'RD$ '.number_format($vacante->salario_max, 2) : '—' }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Fecha de cierre</h3>
                    <p class="text-gray-900 dark:text-gray-100">{{ $vacante->fecha_cierre?->format('d/m/Y') ?? '—' }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Carreras dirigidas</h3>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @forelse ($vacante->carreras as $carrera)
                            <span class="px-2 py-1 text-xs bg-indigo-100 text-indigo-800 rounded-full">{{ $carrera->nombre }}</span>
                        @empty
                            <span class="text-gray-500">Ninguna asignada</span>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Postulaciones recibidas</h3>
                    <p class="text-gray-900 dark:text-gray-100">{{ $vacante->postulaciones_count }}</p>
                </div>

                <div class="pt-4">
                    <a href="{{ route('empresa.vacantes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 underline">← Volver al listado</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>