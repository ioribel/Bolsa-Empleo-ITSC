<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-600 leading-tight">
            {{ __('Postulantes: ') }}{{ $vacante->titulo }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
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
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <h3 class="break-words font-semibold text-gray-900 dark:text-gray-100">{{ $p->estudiante->user->name }}</h3>
                                <p class="break-words text-sm text-gray-500 dark:text-gray-400">{{ $p->estudiante->carrera->nombre }} · {{ $p->estudiante->matricula }}</p>
                                <p class="break-all text-sm text-gray-500 dark:text-gray-400">{{ $p->estudiante->user->email }}</p>
                                @if ($p->estudiante->telefono)
                                    <p class="break-words text-sm text-gray-500 dark:text-gray-400">{{ $p->estudiante->telefono }}</p>
                                @endif

                                @if ($p->cv_path || $p->estudiante->cv_path)
                                    <a href="{{ route('empresa.postulaciones.cv', $p) }}" target="_blank" rel="noopener"
                                        class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-gray-500 dark:text-gray-200 underline">
                                        Ver currículum (PDF)
                                    </a>
                                @else
                                    <p class="mt-2 text-sm text-gray-400 italic">Este estudiante no ha adjuntado CV.</p>
                                @endif
                            </div>
                            <span class="inline-flex w-fit shrink-0 px-2 py-1 text-xs rounded-full
                                @if($p->estado === 'aceptada') bg-green-100 text-green-800
                                @elseif($p->estado === 'rechazada') bg-red-100 text-red-800
                                @elseif($p->estado === 'en_revision') bg-blue-100 text-blue-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $p->estado)) }}
                            </span>
                        </div>

                        @if ($p->estudiante->resumen)
                            <p class="mt-3 break-words text-sm text-gray-600 dark:text-gray-300">{{ $p->estudiante->resumen }}</p>
                        @endif

                        <section class="mt-4 rounded-md border border-gray-200 p-3 dark:border-gray-700">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">Perfil profesional</h4>
                            @if ($p->estudiante->titular)
                                <p class="mt-2 break-words text-sm font-medium">{{ $p->estudiante->titular }}</p>
                            @endif
                            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                @if ($p->estudiante->formaciones->isNotEmpty())
                                    <div>
                                        <h5 class="text-sm font-semibold">Formación</h5>
                                        <ul class="mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                            @foreach ($p->estudiante->formaciones as $formacion)
                                                <li class="break-words">
                                                    {{ $formacion->titulo }} · {{ $formacion->institucion }} ({{ ucfirst($formacion->nivel) }})
                                                    @if ($formacion->campo_estudio) · {{ $formacion->campo_estudio }} @endif
                                                    <span class="text-xs">· {{ $formacion->fecha_inicio->format('m/Y') }} – {{ $formacion->en_curso ? 'Actualidad' : ($formacion->fecha_fin?->format('m/Y') ?? 'Sin fecha de fin') }}</span>
                                                    @if ($formacion->descripcion)<span class="block">{{ $formacion->descripcion }}</span>@endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if ($p->estudiante->experiencias->isNotEmpty())
                                    <div>
                                        <h5 class="text-sm font-semibold">Experiencia</h5>
                                        <ul class="mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                            @foreach ($p->estudiante->experiencias as $experiencia)
                                                <li class="break-words">
                                                    {{ $experiencia->cargo }} · {{ $experiencia->empresa }} ({{ ucfirst($experiencia->tipo) }})
                                                    <span class="text-xs">· {{ $experiencia->fecha_inicio->format('m/Y') }} – {{ $experiencia->actual ? 'Actualidad' : ($experiencia->fecha_fin?->format('m/Y') ?? 'Sin fecha de fin') }}</span>
                                                    @if ($experiencia->descripcion)<span class="block">{{ $experiencia->descripcion }}</span>@endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if ($p->estudiante->habilidades->isNotEmpty())
                                    <div>
                                        <h5 class="text-sm font-semibold">Habilidades</h5>
                                        <ul class="mt-1 flex flex-wrap gap-2 text-sm">
                                            @foreach ($p->estudiante->habilidades as $habilidad)
                                                <li class="max-w-full break-words rounded-full bg-blue-50 px-2 py-1 text-blue-900 dark:bg-blue-950 dark:text-blue-100">
                                                    {{ $habilidad->nombre }} · {{ ucfirst($habilidad->pivot->nivel) }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if ($p->estudiante->idiomas->isNotEmpty())
                                    <div>
                                        <h5 class="text-sm font-semibold">Idiomas</h5>
                                        <ul class="mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                            @foreach ($p->estudiante->idiomas as $idioma)
                                                <li>{{ ucfirst($idioma->idioma) }} · {{ ucfirst($idioma->nivel) }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if ($p->estudiante->certificaciones->isNotEmpty())
                                    <div>
                                        <h5 class="text-sm font-semibold">Certificaciones</h5>
                                        <ul class="mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                            @foreach ($p->estudiante->certificaciones as $certificacion)
                                                <li class="break-words">
                                                    {{ $certificacion->nombre }} · {{ $certificacion->entidad_emisora }}
                                                    <span class="text-xs">· Emitida {{ $certificacion->fecha_emision->format('m/Y') }}
                                                        @if ($certificacion->fecha_vencimiento) · vence {{ $certificacion->fecha_vencimiento->format('m/Y') }} @endif
                                                    </span>
                                                    @if ($certificacion->url_credencial)
                                                        <a href="{{ $certificacion->url_credencial }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline">Ver credencial</a>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if ($p->estudiante->linkedin_url || $p->estudiante->github_url || $p->estudiante->portafolio_url)
                                    <div>
                                        <h5 class="text-sm font-semibold">Enlaces profesionales</h5>
                                        <ul class="mt-1 space-y-1 text-sm">
                                            @foreach (['LinkedIn' => $p->estudiante->linkedin_url, 'GitHub' => $p->estudiante->github_url, 'Portafolio' => $p->estudiante->portafolio_url] as $label => $url)
                                                @if ($url)
                                                    <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="break-all text-blue-700 underline">{{ $label }}</a></li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            @if (! $p->estudiante->titular && $p->estudiante->formaciones->isEmpty() && $p->estudiante->experiencias->isEmpty() && $p->estudiante->habilidades->isEmpty() && $p->estudiante->idiomas->isEmpty() && $p->estudiante->certificaciones->isEmpty() && ! $p->estudiante->linkedin_url && ! $p->estudiante->github_url && ! $p->estudiante->portafolio_url)
                                <p class="mt-2 text-sm text-gray-500">El candidato aún no ha completado su perfil estructurado.</p>
                            @endif
                        </section>

                        @if ($p->carta_presentacion)
                            <p class="mt-3 break-words text-sm text-gray-700 dark:text-gray-300 italic">"{{ $p->carta_presentacion }}"</p>
                        @endif

                        <div class="mt-4 flex flex-col items-start gap-3 sm:flex-row sm:flex-wrap sm:items-center">
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