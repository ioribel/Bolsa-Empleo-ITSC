<x-profile-card id="experiencia-laboral" x-data="{ adding: false }">
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <svg class="h-[18px] w-[18px] text-itsc-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 9h.01M15 9h.01M9 12h.01M15 12h.01" />
            </svg>
            <h2 class="text-[15px] font-medium text-itsc-slate-900">Experiencia laboral</h2>
        </div>
    </x-slot>
    <x-slot name="actions">
        <button type="button" @click="adding = !adding" :aria-expanded="adding.toString()"
            class="inline-flex min-h-10 items-center gap-1 rounded-lg border border-itsc-slate-300 bg-white px-3 text-[13px] font-medium text-itsc-slate-700 transition-colors hover:bg-itsc-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/15">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
            <span x-text="adding ? 'Cancelar' : 'Agregar'"></span>
        </button>
    </x-slot>

    <form x-cloak x-show="adding" x-transition method="POST" action="{{ route('estudiante.perfil.experiencias.store') }}"
        class="mb-4 grid grid-cols-1 gap-3 rounded-xl border border-itsc-slate-100 p-3 md:grid-cols-2">
        @csrf
        <div><label for="experiencia-empresa" class="mb-1 block text-xs text-itsc-slate-500">Empresa u organización</label><input id="experiencia-empresa" name="empresa" required maxlength="255" placeholder="Empresa u organización" class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></div>
        <div><label for="experiencia-cargo" class="mb-1 block text-xs text-itsc-slate-500">Cargo</label><input id="experiencia-cargo" name="cargo" required maxlength="255" placeholder="Cargo" class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></div>
        <div><label for="experiencia-tipo" class="mb-1 block text-xs text-itsc-slate-500">Tipo de experiencia</label><select id="experiencia-tipo" name="tipo" required class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"><option value="">Selecciona un tipo</option>@foreach (['empleo' => 'Empleo', 'pasantia' => 'Pasantía', 'voluntariado' => 'Voluntariado', 'freelance' => 'Freelance'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
        <div><label for="experiencia-ubicacion" class="mb-1 block text-xs text-itsc-slate-500">Ubicación (opcional)</label><input id="experiencia-ubicacion" name="ubicacion" maxlength="255" placeholder="Ubicación" class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></div>
        <div><label for="experiencia-inicio" class="mb-1 block text-xs text-itsc-slate-500">Inicio</label><input id="experiencia-inicio" name="fecha_inicio" type="date" required class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></div>
        <div><label for="experiencia-fin" class="mb-1 block text-xs text-itsc-slate-500">Fin</label><input id="experiencia-fin" name="fecha_fin" type="date" x-bind:disabled="$el.closest('form').querySelector('[name=actual]').checked" class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></div>
        <label class="flex min-h-10 items-center gap-2 text-sm text-itsc-slate-700 md:col-span-2"><input name="actual" type="checkbox" value="1" class="rounded border-itsc-slate-300"> Trabajo aquí actualmente</label>
        <div class="md:col-span-2"><label for="experiencia-descripcion" class="mb-1 block text-xs text-itsc-slate-500">Descripción (máximo 1000 caracteres)</label><textarea id="experiencia-descripcion" name="descripcion" rows="3" maxlength="1000" class="w-full rounded-lg border border-itsc-slate-300 px-3 py-2 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></textarea></div>
        <button class="min-h-10 rounded-lg bg-itsc-slate-700 px-4 py-2 text-[13px] font-medium text-white transition-colors hover:bg-itsc-slate-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/20 md:col-span-2">Guardar experiencia</button>
    </form>

    <div class="space-y-3">
        @forelse ($estudiante->experiencias as $experiencia)
            <article x-data="{ editing: false }" class="rounded-xl border border-itsc-slate-100 p-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex min-w-0 gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-itsc-slate-50 text-itsc-slate-500" aria-hidden="true">
                            <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" /></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="break-words text-sm font-medium text-itsc-slate-900">{{ $experiencia->cargo }}</h3>
                            <p class="break-words text-sm text-itsc-slate-500">{{ $experiencia->empresa }} · {{ ucfirst($experiencia->tipo) }}</p>
                            <p class="text-xs text-itsc-slate-500">{{ $experiencia->fecha_inicio->format('m/Y') }} – {{ $experiencia->actual ? 'Actualidad' : ($experiencia->fecha_fin?->format('m/Y') ?? 'Sin fecha de fin') }}</p>
                            @if ($experiencia->descripcion)<p class="mt-2 break-words text-sm text-itsc-slate-700">{{ $experiencia->descripcion }}</p>@endif
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-1">
                        <button type="button" @click="editing = !editing" :aria-expanded="editing.toString()" class="min-h-10 rounded-lg px-3 text-[13px] text-itsc-slate-700 transition-colors hover:bg-itsc-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-600" aria-label="Editar experiencia">Editar</button>
                        <form method="POST" action="{{ route('estudiante.perfil.experiencias.destroy', $experiencia) }}" onsubmit="return confirm('¿Eliminar esta experiencia?');">
                            @csrf @method('DELETE')
                            <button class="min-h-10 rounded-lg px-3 text-[13px] text-red-800 transition-colors hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300">Eliminar</button>
                        </form>
                    </div>
                </div>
                <form x-cloak x-show="editing" x-transition method="POST" action="{{ route('estudiante.perfil.experiencias.update', $experiencia) }}" class="mt-3 grid grid-cols-1 gap-3 border-t border-itsc-slate-100 pt-3 md:grid-cols-2">
                    @csrf @method('PUT')
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Empresa u organización<input name="empresa" value="{{ $experiencia->empresa }}" required maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label></div>
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Cargo<input name="cargo" value="{{ $experiencia->cargo }}" required maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label></div>
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Tipo de experiencia<select name="tipo" required class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm">@foreach (['empleo', 'pasantia', 'voluntariado', 'freelance'] as $tipo)<option value="{{ $tipo }}" @selected($experiencia->tipo === $tipo)>{{ ucfirst($tipo) }}</option>@endforeach</select></label></div>
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Ubicación<input name="ubicacion" value="{{ $experiencia->ubicacion }}" maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label></div>
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Inicio<input name="fecha_inicio" type="date" value="{{ $experiencia->fecha_inicio->format('Y-m-d') }}" required class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label></div>
                    <div><label class="mb-1 block text-xs text-itsc-slate-500">Fin<input name="fecha_fin" type="date" value="{{ $experiencia->fecha_fin?->format('Y-m-d') }}" x-bind:disabled="$el.closest('form').querySelector('[name=actual]').checked" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label></div>
                    <label class="flex min-h-10 items-center gap-2 text-sm text-itsc-slate-700 md:col-span-2"><input name="actual" type="checkbox" value="1" @checked($experiencia->actual) class="rounded border-itsc-slate-300"> Trabajo aquí actualmente</label>
                    <label class="text-xs text-itsc-slate-500 md:col-span-2">Descripción<textarea name="descripcion" rows="3" maxlength="1000" class="mt-1 w-full rounded-lg border border-itsc-slate-300 px-3 py-2 text-sm">{{ $experiencia->descripcion }}</textarea></label>
                    <button class="min-h-10 rounded-lg bg-itsc-slate-700 px-4 text-[13px] font-medium text-white md:col-span-2">Guardar cambios</button>
                </form>
            </article>
        @empty
            <x-profile-empty-state text="Comparte tu experiencia laboral o pasantías.">
                <x-slot name="icon"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" /></svg></x-slot>
                <x-slot name="action"><button type="button" @click="adding = true" class="min-h-10 rounded-md px-2 text-sm font-medium text-itsc-red-600 hover:text-itsc-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-600">Agregar experiencia</button></x-slot>
            </x-profile-empty-state>
        @endforelse
    </div>
</x-profile-card>
