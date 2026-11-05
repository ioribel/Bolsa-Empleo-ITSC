<x-profile-card id="idiomas" x-data="{ adding: false }">
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <svg class="h-[18px] w-[18px] text-itsc-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="12" cy="12" r="10" /><path stroke-linecap="round" stroke-linejoin="round" d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z" />
            </svg>
            <h2 class="text-[15px] font-medium text-itsc-slate-900">Idiomas</h2>
        </div>
    </x-slot>
    <x-slot name="actions">
        <button type="button" @click="adding = !adding" :aria-expanded="adding.toString()" aria-label="Agregar idioma"
            class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg border border-itsc-slate-300 bg-white text-itsc-slate-700 transition-colors hover:bg-itsc-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/15">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
        </button>
    </x-slot>

    <form x-cloak x-show="adding" x-transition method="POST" action="{{ route('estudiante.perfil.idiomas.store') }}" class="mb-3 grid grid-cols-1 gap-3 rounded-xl border border-itsc-slate-100 p-3">
        @csrf
        <label class="text-xs text-itsc-slate-500">Idioma<input name="idioma" required maxlength="100" placeholder="Idioma" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></label>
        <label class="text-xs text-itsc-slate-500">Nivel<select name="nivel" required class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15">@foreach (['básico', 'intermedio', 'avanzado', 'nativo'] as $nivel)<option value="{{ $nivel }}">{{ ucfirst($nivel) }}</option>@endforeach</select></label>
        <button class="min-h-10 rounded-lg bg-itsc-slate-700 px-4 text-[13px] font-medium text-white">Guardar idioma</button>
    </form>

    <div class="space-y-2">
        @forelse ($estudiante->idiomas as $idioma)
            <article x-data="{ editing: false }" class="rounded-xl border border-itsc-slate-100 p-2.5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-medium text-itsc-slate-900">{{ ucfirst($idioma->idioma) }} <span class="font-normal text-itsc-slate-500">· {{ ucfirst($idioma->nivel) }}</span></p>
                    <div class="flex gap-1">
                        <button type="button" @click="editing = !editing" :aria-expanded="editing.toString()" aria-label="Editar idioma"
                            class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg text-itsc-slate-700 hover:bg-itsc-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-600">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14 5 5 5M4 20l4-.8L19 8a2.1 2.1 0 0 0-3-3L5 16l-1 4Z" /></svg>
                        </button>
                        <form method="POST" action="{{ route('estudiante.perfil.idiomas.destroy', $idioma) }}" onsubmit="return confirm('¿Eliminar este idioma?');">
                            @csrf @method('DELETE')
                            <button aria-label="Eliminar idioma" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg text-red-800 hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3" /></svg>
                            </button>
                        </form>
                    </div>
                </div>
                <form x-cloak x-show="editing" x-transition method="POST" action="{{ route('estudiante.perfil.idiomas.update', $idioma) }}" class="mt-3 grid grid-cols-1 gap-3 border-t border-itsc-slate-100 pt-3">
                    @csrf @method('PUT')
                    <label class="text-xs text-itsc-slate-500">Idioma<input name="idioma" value="{{ $idioma->idioma }}" required maxlength="100" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label>
                    <label class="text-xs text-itsc-slate-500">Nivel<select name="nivel" required class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm">@foreach (['básico', 'intermedio', 'avanzado', 'nativo'] as $nivel)<option value="{{ $nivel }}" @selected($idioma->nivel === $nivel)>{{ ucfirst($nivel) }}</option>@endforeach</select></label>
                    <button class="min-h-10 rounded-lg bg-itsc-slate-700 px-3 text-[13px] font-medium text-white">Guardar cambios</button>
                </form>
            </article>
        @empty
            <x-profile-empty-state text="Añade los idiomas que hablas." class="p-3">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="10" /><path stroke-linecap="round" d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z" /></svg></x-slot>
                <x-slot name="action"><button type="button" @click="adding = true" class="min-h-10 px-2 text-sm font-medium text-itsc-red-600">Agregar idioma</button></x-slot>
            </x-profile-empty-state>
        @endforelse
    </div>
</x-profile-card>
