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
                    <h3 class="text-lg font-semibold mb-4">{{ __('Panel de Empresa') }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-5">
                            <div class="text-3xl font-bold text-[#042C53]">{{ $vacantesActivas }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Vacantes activas</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-5">
                            <div class="text-3xl font-bold text-[#042C53]">{{ $postulacionesRecibidas }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Postulaciones recibidas</div>
                        </div>
                        <div class="bg-[#fdf3e7] dark:bg-gray-900 rounded-lg p-5">
                            <div class="text-3xl font-bold text-[#A32D2D]">{{ $pendientesRevisar }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pendientes de revisar</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('empresa.vacantes.index') }}" class="underline text-indigo-600">Gestionar vacantes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>