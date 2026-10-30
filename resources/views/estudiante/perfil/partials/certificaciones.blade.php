<section class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:p-6" x-data="{ adding: false }">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Certificaciones</h3>
        <button type="button" @click="adding = !adding" class="shrink-0 rounded-md bg-[#042C53] px-3 py-2 text-xs font-semibold text-white">
            <span x-text="adding ? 'Cancelar' : 'Agregar'"></span>
        </button>
    </div>

    <form x-cloak x-show="adding" x-transition method="POST" action="{{ route('estudiante.perfil.certificaciones.store') }}"
        class="mt-4 grid grid-cols-1 gap-3 rounded-md border border-gray-200 p-3 dark:border-gray-700 sm:grid-cols-2">
        @csrf
        <input name="nombre" required maxlength="255" placeholder="Nombre de la certificación" class="rounded-md border-gray-300 text-sm">
        <input name="entidad_emisora" required maxlength="255" placeholder="Entidad emisora" class="rounded-md border-gray-300 text-sm">
        <label class="text-sm">Fecha de emisión <input name="fecha_emision" type="date" required class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
        <label class="text-sm">Vencimiento (opcional) <input name="fecha_vencimiento" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
        <input name="url_credencial" type="url" maxlength="255" placeholder="URL HTTPS de la credencial (opcional)" class="rounded-md border-gray-300 text-sm sm:col-span-2">
        <button class="rounded-md bg-[#042C53] px-4 py-2 text-sm font-semibold text-white sm:col-span-2">Guardar certificación</button>
    </form>

    <div class="mt-4 space-y-3">
        @forelse ($estudiante->certificaciones as $certificacion)
            <article x-data="{ editing: false }" class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h4 class="break-words font-medium text-gray-900 dark:text-gray-100">{{ $certificacion->nombre }}</h4>
                        <p class="break-words text-sm text-gray-600 dark:text-gray-300">{{ $certificacion->entidad_emisora }}</p>
                        <p class="text-xs text-gray-500">Emitida {{ $certificacion->fecha_emision->format('m/Y') }}
                            @if ($certificacion->fecha_vencimiento) · vence {{ $certificacion->fecha_vencimiento->format('m/Y') }} @endif
                        </p>
                        @if ($certificacion->url_credencial)
                            <a href="{{ $certificacion->url_credencial }}" target="_blank" rel="noopener noreferrer" class="break-all text-sm text-blue-700 underline">Ver credencial</a>
                        @endif
                    </div>
                    <div class="flex shrink-0 gap-3 text-sm">
                        <button type="button" @click="editing = !editing" class="text-blue-700 underline">Editar</button>
                        <form method="POST" action="{{ route('estudiante.perfil.certificaciones.destroy', $certificacion) }}" onsubmit="return confirm('¿Eliminar esta certificación?');">
                            @csrf @method('DELETE')
                            <button class="text-red-700 underline">Eliminar</button>
                        </form>
                    </div>
                </div>
                <form x-cloak x-show="editing" x-transition method="POST" action="{{ route('estudiante.perfil.certificaciones.update', $certificacion) }}" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @csrf @method('PUT')
                    <input name="nombre" value="{{ $certificacion->nombre }}" required maxlength="255" class="rounded-md border-gray-300 text-sm">
                    <input name="entidad_emisora" value="{{ $certificacion->entidad_emisora }}" required maxlength="255" class="rounded-md border-gray-300 text-sm">
                    <label class="text-sm">Fecha de emisión <input name="fecha_emision" type="date" value="{{ $certificacion->fecha_emision->format('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
                    <label class="text-sm">Vencimiento <input name="fecha_vencimiento" type="date" value="{{ $certificacion->fecha_vencimiento?->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
                    <input name="url_credencial" type="url" value="{{ $certificacion->url_credencial }}" maxlength="255" placeholder="URL HTTPS de la credencial" class="rounded-md border-gray-300 text-sm sm:col-span-2">
                    <button class="rounded-md bg-[#042C53] px-4 py-2 text-sm font-semibold text-white sm:col-span-2">Guardar cambios</button>
                </form>
            </article>
        @empty
            <p class="text-sm text-gray-500">Aún no has agregado certificaciones.</p>
        @endforelse
    </div>
</section>
