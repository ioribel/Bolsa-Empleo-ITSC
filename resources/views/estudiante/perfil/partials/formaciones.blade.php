<x-profile-card id="formaciones" x-data="{ adding: false }">
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <svg class="h-[18px] w-[18px] text-itsc-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2ZM8 7h8M8 11h8" />
            </svg>
            <h2 class="text-[15px] font-medium text-itsc-slate-900">Formación académica</h2>
        </div>
    </x-slot>
    <x-slot name="actions">
        <button type="button" @click="adding = !adding" :aria-expanded="adding.toString()"
            class="inline-flex min-h-10 items-center gap-1 rounded-lg border border-itsc-slate-300 bg-white px-3 text-[13px] font-medium text-itsc-slate-700 transition-colors hover:bg-itsc-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/15">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
            <span x-text="adding ? 'Cancelar' : 'Agregar'"></span>
        </button>
    </x-slot>

    <form x-cloak x-show="adding" x-transition method="POST" action="{{ route('estudiante.perfil.formaciones.store') }}"
        class="mb-4 grid grid-cols-1 gap-3 rounded-xl border border-itsc-slate-100 p-3 md:grid-cols-2">
        @csrf
        <div><label for="formacion-institucion" class="mb-1 block text-xs text-itsc-slate-500">Institución</label><input id="formacion-institucion" name="institucion" value="{{ old('institucion') }}" required maxlength="255" class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></div>
        <div><label for="formacion-titulo" class="mb-1 block text-xs text-itsc-slate-500">Título obtenido</label><input id="formacion-titulo" name="titulo" value="{{ old('titulo') }}" required maxlength="255" class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></div>
        <div><label for="formacion-nivel" class="mb-1 block text-xs text-itsc-slate-500">Nivel académico</label><select id="formacion-nivel" name="nivel" required class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"><option value="">Selecciona un nivel</option>@foreach (['bachiller' => 'Bachiller', 'tecnico' => 'Técnico', 'grado' => 'Grado', 'postgrado' => 'Postgrado', 'diplomado' => 'Diplomado', 'otro' => 'Otro'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
        <div><label for="formacion-campo" class="mb-1 block text-xs text-itsc-slate-500">Campo de estudio</label><input id="formacion-campo" name="campo_estudio" maxlength="255" class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></div>
        <div><label for="formacion-inicio" class="mb-1 block text-xs text-itsc-slate-500">Inicio</label><input id="formacion-inicio" name="fecha_inicio" type="date" required class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></div>
        <div><label for="formacion-fin" class="mb-1 block text-xs text-itsc-slate-500">Fin</label><input id="formacion-fin" name="fecha_fin" type="date" x-bind:disabled="$el.closest('form').querySelector('[name=en_curso]').checked" class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></div>
        <label class="flex min-h-10 items-center gap-2 text-sm text-itsc-slate-700 md:col-span-2"><input name="en_curso" type="checkbox" value="1" class="rounded border-itsc-slate-300 text-itsc-slate-700 focus:ring-itsc-red-600/20"> Actualmente en curso</label>
        <div class="md:col-span-2"><label for="formacion-descripcion" class="mb-1 block text-xs text-itsc-slate-500">Descripción (opcional)</label><textarea id="formacion-descripcion" name="descripcion" rows="2" maxlength="5000" class="w-full rounded-lg border border-itsc-slate-300 px-3 py-2 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></textarea></div>
        <button class="min-h-10 rounded-lg bg-itsc-slate-700 px-4 py-2 text-[13px] font-medium text-white transition-colors hover:bg-itsc-slate-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/20 md:col-span-2">Guardar formación</button>
    </form>

    <div class="space-y-3">
        @forelse ($estudiante->formaciones as $formacion)
            <article x-data="{ editing: false }" class="rounded-xl border border-itsc-slate-100 p-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex min-w-0 gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-itsc-slate-50 text-itsc-slate-500" aria-hidden="true">
                            <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" /></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="break-words text-sm font-medium text-itsc-slate-900">{{ $formacion->titulo }}</h3>
                            <p class="break-words text-sm text-itsc-slate-500">{{ $formacion->institucion }} · {{ ucfirst($formacion->nivel) }}</p>
                            <p class="text-xs text-itsc-slate-500">{{ $formacion->fecha_inicio->format('m/Y') }} – {{ $formacion->en_curso ? 'Actualidad' : ($formacion->fecha_fin?->format('m/Y') ?? 'Sin fecha de fin') }}</p>
                            @if ($formacion->descripcion)<p class="mt-2 break-words text-sm text-itsc-slate-700">{{ $formacion->descripcion }}</p>@endif
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-1">
                        <button type="button" @click="editing = !editing" :aria-expanded="editing.toString()" class="min-h-10 rounded-lg px-3 text-[13px] text-itsc-slate-700 transition-colors hover:bg-itsc-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-600" aria-label="Editar formación">Editar</button>
                        <form method="POST" action="{{ route('estudiante.perfil.formaciones.destroy', $formacion) }}" onsubmit="return confirm('¿Eliminar esta formación?');">
                            @csrf @method('DELETE')
                            <button class="min-h-10 rounded-lg px-3 text-[13px] text-red-800 transition-colors hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300">Eliminar</button>
                        </form>
                    </div>
                </div>
                <form x-cloak x-show="editing" x-transition method="POST" action="{{ route('estudiante.perfil.formaciones.update', $formacion) }}" class="mt-3 grid grid-cols-1 gap-3 border-t border-itsc-slate-100 pt-3 md:grid-cols-2">
                    @csrf @method('PUT')
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Institución<input name="institucion" value="{{ $formacion->institucion }}" required maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label></div>
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Título obtenido<input name="titulo" value="{{ $formacion->titulo }}" required maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label></div>
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Nivel académico<select name="nivel" required class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm">@foreach (['bachiller', 'tecnico', 'grado', 'postgrado', 'diplomado', 'otro'] as $nivel)<option value="{{ $nivel }}" @selected($formacion->nivel === $nivel)>{{ ucfirst($nivel) }}</option>@endforeach</select></label></div>
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Campo de estudio<input name="campo_estudio" value="{{ $formacion->campo_estudio }}" maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label></div>
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Inicio<input name="fecha_inicio" type="date" value="{{ $formacion->fecha_inicio->format('Y-m-d') }}" required class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label></div>
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Fin<input name="fecha_fin" type="date" value="{{ $formacion->fecha_fin?->format('Y-m-d') }}" x-bind:disabled="$el.closest('form').querySelector('[name=en_curso]').checked" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label></div>
                    <label class="flex min-h-10 items-center gap-2 text-sm text-itsc-slate-700 md:col-span-2"><input name="en_curso" type="checkbox" value="1" @checked($formacion->en_curso) class="rounded border-itsc-slate-300"> Actualmente en curso</label>
                    <label class="text-xs text-itsc-slate-500 md:col-span-2">Descripción<textarea name="descripcion" rows="2" maxlength="5000" class="mt-1 w-full rounded-lg border border-itsc-slate-300 px-3 py-2 text-sm">{{ $formacion->descripcion }}</textarea></label>
                    <button class="min-h-10 rounded-lg bg-itsc-slate-700 px-4 text-[13px] font-medium text-white md:col-span-2">Guardar cambios</button>
                </form>
            </article>
        @empty
            <x-profile-empty-state text="Cuéntales a las empresas dónde estudiaste.">
                <x-slot name="icon"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 3 7l9 4 9-4-9-4ZM5 9v7l7 4 7-4V9M12 11v9" /></svg></x-slot>
                <x-slot name="action"><button type="button" @click="adding = true" class="min-h-10 rounded-md px-2 text-sm font-medium text-itsc-red-600 transition-colors hover:text-itsc-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-600">Agregar formación</button></x-slot>
            </x-profile-empty-state>
        @endforelse
    </div>
</x-profile-card>
