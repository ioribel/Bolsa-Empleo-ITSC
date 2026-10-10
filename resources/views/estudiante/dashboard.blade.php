<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Panel de Estudiante') }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-5">
                            <div class="text-3xl font-bold text-[#042C53]">{{ $vacantesDisponibles }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Vacantes para tu carrera</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-5">
                            <div class="text-3xl font-bold text-[#042C53]">{{ $misPostulaciones }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Mis postulaciones</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-5">
                            <div class="text-3xl font-bold text-[#A32D2D]">{{ $postulacionesAceptadas }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Postulaciones aceptadas</div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-4">
                        <a href="{{ route('estudiante.vacantes.index') }}" class="underline text-indigo-600">Ver vacantes</a>
                        <a href="{{ route('estudiante.postulaciones.index') }}" class="underline text-indigo-600">Mis postulaciones</a>
                        <a href="{{ route('estudiante.perfil.edit') }}" class="underline text-indigo-600">Mi perfil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>