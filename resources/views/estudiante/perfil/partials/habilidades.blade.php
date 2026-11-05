<x-profile-card id="habilidades" x-data="{ adding: false }">
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <svg class="h-[18px] w-[18px] text-itsc-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m13 2-3 8h7l-6 12 1-9H5l8-11Z" />
            </svg>
            <h2 class="text-[15px] font-medium text-itsc-slate-900">Habilidades</h2>
        </div>
    </x-slot>
    <x-slot name="actions">
        <button type="button" @click="adding = !adding" :aria-expanded="adding.toString()"
            class="inline-flex min-h-10 items-center gap-1 rounded-lg border border-itsc-slate-300 bg-white px-3 text-[13px] font-medium text-itsc-slate-700 transition-colors hover:bg-itsc-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/15">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
            <span x-text="adding ? 'Cancelar' : 'Agregar'"></span>
        </button>
    </x-slot>

    <form x-cloak x-show="adding" x-transition method="POST" action="{{ route('estudiante.perfil.habilidades.store') }}"
        class="mb-4 grid grid-cols-1 gap-3 rounded-xl border border-itsc-slate-100 p-3 md:grid-cols-3">
        @csrf
        <div class="md:col-span-3"><label for="habilidad-nombre" class="mb-1 block text-xs text-itsc-slate-500">Habilidad</label><input id="habilidad-nombre" name="nombre" list="catalogo-habilidades" required minlength="2" maxlength="100" placeholder="Elige una sugerencia o escribe una habilidad" class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></div>
        <div><label for="habilidad-tipo" class="mb-1 block text-xs text-itsc-slate-500">Tipo</label><select id="habilidad-tipo" name="tipo" required class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"><option value="técnica">Técnica</option><option value="blanda">Blanda</option></select></div>
        <div><label for="habilidad-nivel" class="mb-1 block text-xs text-itsc-slate-500">Nivel</label><select id="habilidad-nivel" name="nivel" required class="h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"><option value="básico">Básico</option><option value="intermedio">Intermedio</option><option value="avanzado">Avanzado</option></select></div>
        <button class="min-h-10 rounded-lg bg-itsc-slate-700 px-4 text-[13px] font-medium text-white md:col-span-3">Guardar habilidad</button>
    </form>

    <datalist id="catalogo-habilidades">
        @foreach ($catalogoHabilidades as $habilidad)
            <option value="{{ $habilidad->nombre }}">{{ $habilidad->tipo === 'técnica' ? 'Técnica' : 'Blanda' }}</option>
        @endforeach
    </datalist>

    <div class="flex flex-wrap gap-2">
        @forelse ($estudiante->habilidades as $habilidad)
            <div class="max-w-full">
                <x-profile-chip :level="$habilidad->pivot->nivel">
                    {{ $habilidad->nombre }}
                    <button type="button" @click="$dispatch('open-modal', 'edit-skill-{{ $habilidad->id }}')"
                        aria-label="Editar {{ $habilidad->nombre }}" class="min-h-10 min-w-10 rounded-full hover:bg-white/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-600">
                        <svg class="mx-auto h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 16 1-4L16 2l4 4L10 16l-4 1Zm9-11 4 4" /></svg>
                    </button>
                </x-profile-chip>
                <form method="POST" action="{{ route('estudiante.perfil.habilidades.destroy', $habilidad) }}" onsubmit="return confirm('¿Eliminar esta habilidad?');" class="inline-flex align-middle">
                    @csrf @method('DELETE')
                    <button aria-label="Quitar {{ $habilidad->nombre }}" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-full text-red-800 transition-colors hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" /></svg>
                    </button>
                </form>

                <x-modal name="edit-skill-{{ $habilidad->id }}" maxWidth="md" focusable>
                    <div class="space-y-4 p-5">
                        <div>
                            <h2 id="edit-skill-{{ $habilidad->id }}-title" class="text-[15px] font-medium text-itsc-slate-900">Editar habilidad</h2>
                            <p class="mt-1 text-sm text-itsc-slate-500">{{ $habilidad->nombre }}</p>
                        </div>
                        <form method="POST" action="{{ route('estudiante.perfil.habilidades.update', $habilidad) }}" class="space-y-3">
                            @csrf @method('PUT')
                            <label class="block text-xs text-itsc-slate-500">Habilidad
                                <input name="nombre" list="catalogo-habilidades" value="{{ $habilidad->nombre }}" required minlength="2" maxlength="100" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm">
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="text-xs text-itsc-slate-500">Tipo<select name="tipo" required class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"><option value="técnica" @selected($habilidad->tipo === 'técnica')>Técnica</option><option value="blanda" @selected($habilidad->tipo === 'blanda')>Blanda</option></select></label>
                                <label class="text-xs text-itsc-slate-500">Nivel<select name="nivel" required class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm">@foreach (['básico', 'intermedio', 'avanzado'] as $nivel)<option value="{{ $nivel }}" @selected($habilidad->pivot->nivel === $nivel)>{{ ucfirst($nivel) }}</option>@endforeach</select></label>
                            </div>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="$dispatch('close-modal', 'edit-skill-{{ $habilidad->id }}')" class="min-h-10 rounded-lg border border-itsc-slate-300 bg-white px-3 text-[13px] text-itsc-slate-700">Cancelar</button>
                                <button class="min-h-10 rounded-lg bg-itsc-slate-700 px-3 text-[13px] font-medium text-white">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </x-modal>
            </div>
        @empty
            <x-profile-empty-state text="Añade las habilidades que mejor describan tu perfil." class="w-full">
                <x-slot name="icon"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m13 2-3 8h7l-6 12 1-9H5l8-11Z" /></svg></x-slot>
                <x-slot name="action"><button type="button" @click="adding = true" class="min-h-10 rounded-md px-2 text-sm font-medium text-itsc-red-600">Agregar habilidad</button></x-slot>
            </x-profile-empty-state>
        @endforelse
    </div>
</x-profile-card>
