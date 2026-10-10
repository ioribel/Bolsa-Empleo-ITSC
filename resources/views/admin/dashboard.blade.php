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
                    <h3 class="text-lg font-semibold mb-4">{{ __('Panel de Administración') }}</h3>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-5">
                            <div class="text-3xl font-bold text-[#042C53]">{{ $totalEstudiantes }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Estudiantes</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-5">
                            <div class="text-3xl font-bold text-[#042C53]">{{ $empresasAprobadas }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Empresas aprobadas</div>
                        </div>
                        <div class="bg-[#fdf3e7] dark:bg-gray-900 rounded-lg p-5">
                            <div class="text-3xl font-bold text-[#A32D2D]">{{ $empresasPendientes }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Empresas pendientes</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-5">
                            <div class="text-3xl font-bold text-[#042C53]">{{ $vacantesActivas }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Vacantes activas</div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-4">
                        <a href="{{ route('admin.empresas.index') }}" class="underline text-indigo-600">Gestionar empresas</a>
                        <a href="{{ route('admin.carreras.index') }}" class="underline text-indigo-600">Gestionar carreras</a>
                        <a href="{{ route('admin.usuarios.index') }}" class="underline text-indigo-600">Gestionar usuarios</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>