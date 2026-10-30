<section class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:p-6" x-data="{ adding: false }">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Idiomas</h3>
        <button type="button" @click="adding = !adding" class="shrink-0 rounded-md bg-[#042C53] px-3 py-2 text-xs font-semibold text-white">
            <span x-text="adding ? 'Cancelar' : 'Agregar'"></span>
        </button>
    </div>

    <form x-cloak x-show="adding" x-transition method="POST" action="{{ route('estudiante.perfil.idiomas.store') }}"
        class="mt-4 grid grid-cols-1 gap-3 rounded-md border border-gray-200 p-3 dark:border-gray-700 sm:grid-cols-3">
        @csrf
        <input name="idioma" required maxlength="100" placeholder="Idioma" class="rounded-md border-gray-300 text-sm">
        <select name="nivel" required class="rounded-md border-gray-300 text-sm sm:col-span-2">
            @foreach (['básico', 'intermedio', 'avanzado', 'nativo'] as $nivel)
                <option value="{{ $nivel }}">{{ ucfirst($nivel) }}</option>
            @endforeach
        </select>
        <button class="rounded-md bg-[#042C53] px-4 py-2 text-sm font-semibold text-white sm:col-span-3">Guardar idioma</button>
    </form>

    <div class="mt-4 space-y-3">
        @forelse ($estudiante->idiomas as $idioma)
            <article x-data="{ editing: false }" class="flex flex-col gap-3 rounded-md border border-gray-200 p-3 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                <p class="font-medium">{{ ucfirst($idioma->idioma) }} <span class="text-sm font-normal text-gray-500">· {{ ucfirst($idioma->nivel) }}</span></p>
                <div class="flex gap-3 text-sm">
                    <button type="button" @click="editing = !editing" class="text-blue-700 underline">Editar</button>
                    <form method="POST" action="{{ route('estudiante.perfil.idiomas.destroy', $idioma) }}" onsubmit="return confirm('¿Eliminar este idioma?');">
                        @csrf @method('DELETE')
                        <button class="text-red-700 underline">Eliminar</button>
                    </form>
                </div>
                <form x-cloak x-show="editing" x-transition method="POST" action="{{ route('estudiante.perfil.idiomas.update', $idioma) }}" class="grid w-full grid-cols-1 gap-2 sm:grid-cols-3">
                    @csrf @method('PUT')
                    <input name="idioma" value="{{ $idioma->idioma }}" required maxlength="100" class="rounded-md border-gray-300 text-sm">
                    <select name="nivel" required class="rounded-md border-gray-300 text-sm sm:col-span-2">
                        @foreach (['básico', 'intermedio', 'avanzado', 'nativo'] as $nivel)
                            <option value="{{ $nivel }}" @selected($idioma->nivel === $nivel)>{{ ucfirst($nivel) }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-md bg-[#042C53] px-3 py-2 text-sm font-semibold text-white sm:col-span-3">Guardar cambios</button>
                </form>
            </article>
        @empty
            <p class="text-sm text-gray-500">Aún no has agregado idiomas.</p>
        @endforelse
    </div>
</section>
