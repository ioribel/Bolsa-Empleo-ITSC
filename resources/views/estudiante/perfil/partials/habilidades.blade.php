<section class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:p-6" x-data="{ adding: false }">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Habilidades</h3>
        <button type="button" @click="adding = !adding" class="shrink-0 rounded-md bg-[#042C53] px-3 py-2 text-xs font-semibold text-white">
            <span x-text="adding ? 'Cancelar' : 'Agregar'"></span>
        </button>
    </div>

    <form x-cloak x-show="adding" x-transition method="POST" action="{{ route('estudiante.perfil.habilidades.store') }}"
        class="mt-4 grid grid-cols-1 gap-3 rounded-md border border-gray-200 p-3 dark:border-gray-700 sm:grid-cols-3">
        @csrf
        <label class="text-sm sm:col-span-3">Habilidad
            <input name="nombre" list="catalogo-habilidades" required minlength="2" maxlength="100" placeholder="Elige una sugerencia o escribe una habilidad" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
        </label>
        <select name="tipo" required class="rounded-md border-gray-300 text-sm">
            <option value="técnica">Técnica</option>
            <option value="blanda">Blanda</option>
        </select>
        <select name="nivel" required class="rounded-md border-gray-300 text-sm sm:col-span-2">
            <option value="básico">Básico</option>
            <option value="intermedio">Intermedio</option>
            <option value="avanzado">Avanzado</option>
        </select>
        <button class="rounded-md bg-[#042C53] px-4 py-2 text-sm font-semibold text-white sm:col-span-3">Guardar habilidad</button>
    </form>

    <datalist id="catalogo-habilidades">
        @foreach ($catalogoHabilidades as $habilidad)
            <option value="{{ $habilidad->nombre }}">{{ $habilidad->tipo === 'técnica' ? 'Técnica' : 'Blanda' }}</option>
        @endforeach
    </datalist>

    <div class="mt-4 flex flex-wrap gap-2">
        @forelse ($estudiante->habilidades as $habilidad)
            <div x-data="{ editing: false }" class="inline-flex max-w-full flex-wrap items-center gap-2 rounded-full bg-blue-50 px-3 py-2 text-sm text-blue-900 dark:bg-blue-950 dark:text-blue-100">
                <span class="break-words">{{ $habilidad->nombre }} · {{ ucfirst($habilidad->pivot->nivel) }}</span>
                <button type="button" @click="editing = !editing" class="underline">Editar</button>
                <form method="POST" action="{{ route('estudiante.perfil.habilidades.destroy', $habilidad) }}" onsubmit="return confirm('¿Eliminar esta habilidad?');">
                    @csrf @method('DELETE')
                    <button class="text-red-700 underline dark:text-red-300">Quitar</button>
                </form>
                <form x-cloak x-show="editing" x-transition method="POST" action="{{ route('estudiante.perfil.habilidades.update', $habilidad) }}" class="w-full space-y-2 border-t border-blue-200 pt-2 dark:border-blue-800">
                    @csrf @method('PUT')
                    <input name="nombre" list="catalogo-habilidades" value="{{ $habilidad->nombre }}" required minlength="2" maxlength="100" class="block w-full rounded-md border-gray-300 text-sm text-gray-900">
                    <div class="flex gap-2">
                        <select name="tipo" required class="min-w-0 flex-1 rounded-md border-gray-300 text-sm text-gray-900">
                            <option value="técnica" @selected($habilidad->tipo === 'técnica')>Técnica</option>
                            <option value="blanda" @selected($habilidad->tipo === 'blanda')>Blanda</option>
                        </select>
                        <select name="nivel" required class="min-w-0 flex-1 rounded-md border-gray-300 text-sm text-gray-900">
                            @foreach (['básico', 'intermedio', 'avanzado'] as $nivel)
                                <option value="{{ $nivel }}" @selected($habilidad->pivot->nivel === $nivel)>{{ ucfirst($nivel) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="rounded-md bg-[#042C53] px-3 py-2 text-xs font-semibold text-white">Guardar</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500">Aún no has agregado habilidades.</p>
        @endforelse
    </div>
</section>
