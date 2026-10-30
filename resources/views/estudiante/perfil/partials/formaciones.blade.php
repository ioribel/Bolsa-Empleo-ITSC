<section class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:p-6" x-data="{ adding: false }">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Formación académica</h3>
        <button type="button" @click="adding = !adding" class="shrink-0 rounded-md bg-[#042C53] px-3 py-2 text-xs font-semibold text-white">
            <span x-text="adding ? 'Cancelar' : 'Agregar'"></span>
        </button>
    </div>

    <form x-cloak x-show="adding" x-transition method="POST" action="{{ route('estudiante.perfil.formaciones.store') }}"
        class="mt-4 grid grid-cols-1 gap-3 rounded-md border border-gray-200 p-3 dark:border-gray-700 sm:grid-cols-2">
        @csrf
        <input name="institucion" value="{{ old('institucion') }}" required maxlength="255" placeholder="Institución" class="rounded-md border-gray-300 text-sm">
        <input name="titulo" value="{{ old('titulo') }}" required maxlength="255" placeholder="Título obtenido" class="rounded-md border-gray-300 text-sm">
        <select name="nivel" required class="rounded-md border-gray-300 text-sm">
            <option value="">Nivel académico</option>
            @foreach (['bachiller' => 'Bachiller', 'tecnico' => 'Técnico', 'grado' => 'Grado', 'postgrado' => 'Postgrado', 'diplomado' => 'Diplomado', 'otro' => 'Otro'] as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <input name="campo_estudio" maxlength="255" placeholder="Campo de estudio" class="rounded-md border-gray-300 text-sm">
        <label class="text-sm">Inicio <input name="fecha_inicio" type="date" required class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
        <label class="text-sm">Fin <input name="fecha_fin" type="date" x-bind:disabled="$el.closest('form').querySelector('[name=en_curso]').checked" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
        <label class="flex items-center gap-2 text-sm sm:col-span-2"><input name="en_curso" type="checkbox" value="1" class="rounded border-gray-300"> Actualmente en curso</label>
        <textarea name="descripcion" rows="2" maxlength="5000" placeholder="Descripción (opcional)" class="rounded-md border-gray-300 text-sm sm:col-span-2"></textarea>
        <button class="rounded-md bg-[#042C53] px-4 py-2 text-sm font-semibold text-white sm:col-span-2">Guardar formación</button>
    </form>

    <div class="mt-4 space-y-3">
        @forelse ($estudiante->formaciones as $formacion)
            <article x-data="{ editing: false }" class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h4 class="break-words font-medium text-gray-900 dark:text-gray-100">{{ $formacion->titulo }}</h4>
                        <p class="break-words text-sm text-gray-600 dark:text-gray-300">{{ $formacion->institucion }} · {{ ucfirst($formacion->nivel) }}</p>
                        <p class="text-xs text-gray-500">{{ $formacion->fecha_inicio->format('m/Y') }} – {{ $formacion->en_curso ? 'Actualidad' : ($formacion->fecha_fin?->format('m/Y') ?? 'Sin fecha de fin') }}</p>
                        @if ($formacion->descripcion)<p class="mt-2 break-words text-sm">{{ $formacion->descripcion }}</p>@endif
                    </div>
                    <div class="flex shrink-0 gap-3 text-sm">
                        <button type="button" @click="editing = !editing" class="text-blue-700 underline">Editar</button>
                        <form method="POST" action="{{ route('estudiante.perfil.formaciones.destroy', $formacion) }}" onsubmit="return confirm('¿Eliminar esta formación?');">
                            @csrf @method('DELETE')
                            <button class="text-red-700 underline">Eliminar</button>
                        </form>
                    </div>
                </div>
                <form x-cloak x-show="editing" x-transition method="POST" action="{{ route('estudiante.perfil.formaciones.update', $formacion) }}" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @csrf @method('PUT')
                    <input name="institucion" value="{{ $formacion->institucion }}" required maxlength="255" class="rounded-md border-gray-300 text-sm">
                    <input name="titulo" value="{{ $formacion->titulo }}" required maxlength="255" class="rounded-md border-gray-300 text-sm">
                    <select name="nivel" required class="rounded-md border-gray-300 text-sm">
                        @foreach (['bachiller', 'tecnico', 'grado', 'postgrado', 'diplomado', 'otro'] as $nivel)
                            <option value="{{ $nivel }}" @selected($formacion->nivel === $nivel)>{{ ucfirst($nivel) }}</option>
                        @endforeach
                    </select>
                    <input name="campo_estudio" value="{{ $formacion->campo_estudio }}" maxlength="255" placeholder="Campo de estudio" class="rounded-md border-gray-300 text-sm">
                    <label class="text-sm">Inicio <input name="fecha_inicio" type="date" value="{{ $formacion->fecha_inicio->format('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
                    <label class="text-sm">Fin <input name="fecha_fin" type="date" value="{{ $formacion->fecha_fin?->format('Y-m-d') }}" x-bind:disabled="$el.closest('form').querySelector('[name=en_curso]').checked" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
                    <label class="flex items-center gap-2 text-sm sm:col-span-2"><input name="en_curso" type="checkbox" value="1" @checked($formacion->en_curso) class="rounded border-gray-300"> Actualmente en curso</label>
                    <textarea name="descripcion" rows="2" maxlength="5000" class="rounded-md border-gray-300 text-sm sm:col-span-2">{{ $formacion->descripcion }}</textarea>
                    <button class="rounded-md bg-[#042C53] px-4 py-2 text-sm font-semibold text-white sm:col-span-2">Guardar cambios</button>
                </form>
            </article>
        @empty
            <p class="text-sm text-gray-500">Aún no has agregado formación.</p>
        @endforelse
    </div>
</section>
