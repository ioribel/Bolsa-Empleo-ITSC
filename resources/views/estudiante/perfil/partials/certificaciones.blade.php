<x-profile-card x-data="{ adding: false }">
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <svg class="h-[18px] w-[18px] text-itsc-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z" />
            </svg>
            <h2 class="text-[15px] font-medium text-itsc-slate-900">Certificaciones</h2>
        </div>
    </x-slot>
    <x-slot name="actions">
        <button type="button" @click="adding = !adding" :aria-expanded="adding.toString()" aria-label="Agregar certificación"
            class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg border border-itsc-slate-300 bg-white text-itsc-slate-700 transition-colors hover:bg-itsc-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/15">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
        </button>
    </x-slot>

    <form x-cloak x-show="adding" x-transition method="POST" action="{{ route('estudiante.perfil.certificaciones.store') }}" class="mb-3 grid grid-cols-1 gap-3 rounded-xl border border-itsc-slate-100 p-3">
        @csrf
        <label class="text-xs text-itsc-slate-500">Nombre<input name="nombre" required maxlength="255" placeholder="Nombre de la certificación" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></label>
        <label class="text-xs text-itsc-slate-500">Entidad emisora<input name="entidad_emisora" required maxlength="255" placeholder="Entidad emisora" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm focus:border-itsc-red-600 focus:outline-none focus:ring-4 focus:ring-itsc-red-600/15"></label>
        <label class="text-xs text-itsc-slate-500">Fecha de emisión<input name="fecha_emision" type="date" required class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label>
        <label class="text-xs text-itsc-slate-500">Vencimiento (opcional)<input name="fecha_vencimiento" type="date" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label>
        <label class="text-xs text-itsc-slate-500">URL de la credencial (opcional)<input name="url_credencial" type="url" maxlength="255" placeholder="https://..." class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label>
        <button class="min-h-10 rounded-lg bg-itsc-slate-700 px-4 text-[13px] font-medium text-white">Guardar certificación</button>
    </form>

    <div class="space-y-2">
        @forelse ($estudiante->certificaciones as $certificacion)
            <article x-data="{ editing: false }" class="rounded-xl border border-itsc-slate-100 p-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h3 class="break-words text-sm font-medium text-itsc-slate-900">{{ $certificacion->nombre }}</h3>
                        <p class="break-words text-sm text-itsc-slate-500">{{ $certificacion->entidad_emisora }}</p>
                        <p class="text-xs text-itsc-slate-500">Emitida {{ $certificacion->fecha_emision->format('m/Y') }}@if ($certificacion->fecha_vencimiento) · vence {{ $certificacion->fecha_vencimiento->format('m/Y') }}@endif</p>
                        @if ($certificacion->url_credencial)
                            <a href="{{ $certificacion->url_credencial }}" target="_blank" rel="noopener noreferrer" class="break-all text-sm text-itsc-red-600 hover:text-itsc-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-600">Ver credencial</a>
                        @endif
                    </div>
                    <div class="flex shrink-0 gap-1">
                        <button type="button" @click="editing = !editing" :aria-expanded="editing.toString()" aria-label="Editar certificación"
                            class="inline-flex min-h-10 items-center rounded-lg px-3 text-[13px] text-itsc-slate-700 hover:bg-itsc-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-600">Editar</button>
                        <form method="POST" action="{{ route('estudiante.perfil.certificaciones.destroy', $certificacion) }}" onsubmit="return confirm('¿Eliminar esta certificación?');">
                            @csrf @method('DELETE')
                            <button class="min-h-10 rounded-lg px-3 text-[13px] text-red-800 hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300">Eliminar</button>
                        </form>
                    </div>
                </div>
                <form x-cloak x-show="editing" x-transition method="POST" action="{{ route('estudiante.perfil.certificaciones.update', $certificacion) }}" class="mt-3 grid grid-cols-1 gap-3 border-t border-itsc-slate-100 pt-3">
                    @csrf @method('PUT')
                    <label class="text-xs text-itsc-slate-500">Nombre<input name="nombre" value="{{ $certificacion->nombre }}" required maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label>
                    <label class="text-xs text-itsc-slate-500">Entidad emisora<input name="entidad_emisora" value="{{ $certificacion->entidad_emisora }}" required maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label>
                    <label class="text-xs text-itsc-slate-500">Fecha de emisión<input name="fecha_emision" type="date" value="{{ $certificacion->fecha_emision->format('Y-m-d') }}" required class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label>
                    <label class="text-xs text-itsc-slate-500">Vencimiento<input name="fecha_vencimiento" type="date" value="{{ $certificacion->fecha_vencimiento?->format('Y-m-d') }}" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label>
                    <label class="text-xs text-itsc-slate-500">URL de la credencial<input name="url_credencial" type="url" value="{{ $certificacion->url_credencial }}" maxlength="255" class="mt-1 h-10 w-full rounded-lg border border-itsc-slate-300 px-3 text-sm"></label>
                    <button class="min-h-10 rounded-lg bg-itsc-slate-700 px-4 text-[13px] font-medium text-white">Guardar cambios</button>
                </form>
            </article>
        @empty
            <x-profile-empty-state text="Suma cursos y credenciales." class="p-3">
                <x-slot name="icon"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z" /></svg></x-slot>
                <x-slot name="action"><button type="button" @click="adding = true" class="min-h-10 px-2 text-sm font-medium text-itsc-red-600">Agregar certificación</button></x-slot>
            </x-profile-empty-state>
        @endforelse
    </div>
</x-profile-card>
