<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Postulantes: ') }}{{ $vacante->titulo }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">{{ session('error') }}</div>
            @endif

            <div class="mb-4">
                <a href="{{ route('empresa.vacantes.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">← Volver a mis vacantes</a>
            </div>

            <div class="space-y-4">
                @forelse ($postulaciones as $p)
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $p->estudiante->user->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $p->estudiante->carrera->nombre }} · {{ $p->estudiante->matricula }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $p->estudiante->user->email }}</p>
                                @if ($p->estudiante->telefono)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $p->estudiante->telefono }}</p>
                                @endif

                                @if ($p->estudiante->cv_path)
                                    <a href="{{ Storage::url($p->estudiante->cv_path) }}" target="_blank"
                                        class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-[#042C53] underline">
                                        📄 Ver currículum (PDF)
                                    </a>
                                @else
                                    <p class="mt-2 text-sm text-gray-400 italic">Este estudiante no ha adjuntado CV.</p>
                                @endif
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($p->estado === 'aceptada') bg-green-100 text-green-800
                                @elseif($p->estado === 'rechazada') bg-red-100 text-red-800
                                @elseif($p->estado === 'en_revision') bg-blue-100 text-blue-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $p->estado)) }}
                            </span>
                        </div>

                        @if ($p->estudiante->resumen)
                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ $p->estudiante->resumen }}</p>
                        @endif

                        @if ($p->carta_presentacion)
                            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300 italic">"{{ $p->carta_presentacion }}"</p>
                        @endif

                        <div class="mt-4 flex items-center gap-3">
                            @if ($p->estado === 'recibida')
                                <form method="POST" action="{{ route('empresa.postulaciones.en_revision', $p) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-sm underline text-blue-600">Poner en revisión</button>
                                </form>
                                <form method="POST" action="{{ route('empresa.postulaciones.rechazar', $p) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-sm underline text-red-600">Rechazar</button>
                                </form>
                            @elseif ($p->estado === 'en_revision')
                                <form method="POST" action="{{ route('empresa.postulaciones.aceptar', $p) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-sm underline text-green-600">Aceptar</button>
                                </form>
                                <form method="POST" action="{{ route('empresa.postulaciones.rechazar', $p) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-sm underline text-red-600">Rechazar</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                        Todavía no hay postulantes para esta vacante.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>