<section class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:p-6" x-data="{ adding: false }">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Experiencia laboral</h3>
        <button type="button" @click="adding = !adding" class="shrink-0 rounded-md bg-[#042C53] px-3 py-2 text-xs font-semibold text-white">
            <span x-text="adding ? 'Cancelar' : 'Agregar'"></span>
        </button>
    </div>

    <form x-cloak x-show="adding" x-transition method="POST" action="{{ route('estudiante.perfil.experiencias.store') }}" class="mt-4 grid grid-cols-1 gap-3 rounded-md border border-gray-200 p-3 dark:border-gray-700 sm:grid-cols-2">
        @csrf
        <input name="empresa" required maxlength="255" placeholder="Empresa u organización" class="rounded-md border-gray-300 text-sm">
        <input name="cargo" required maxlength="255" placeholder="Cargo" class="rounded-md border-gray-300 text-sm">
        <select name="tipo" required class="rounded-md border-gray-300 text-sm">
            <option value="">Tipo de experiencia</option>
            @foreach (['empleo' => 'Empleo', 'pasantia' => 'Pasantía', 'voluntariado' => 'Voluntariado', 'freelance' => 'Freelance'] as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <input name="ubicacion" maxlength="255" placeholder="Ubicación (opcional)" class="rounded-md border-gray-300 text-sm">
        <label class="text-sm">Inicio <input name="fecha_inicio" type="date" required class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
        <label class="text-sm">Fin <input name="fecha_fin" type="date" x-bind:disabled="$el.closest('form').querySelector('[name=actual]').checked" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
        <label class="flex items-center gap-2 text-sm sm:col-span-2"><input name="actual" type="checkbox" value="1" class="rounded border-gray-300"> Trabajo aquí actualmente</label>
        <textarea name="descripcion" rows="3" maxlength="1000" placeholder="Descripción (máximo 1000 caracteres)" class="rounded-md border-gray-300 text-sm sm:col-span-2"></textarea>
        <button class="rounded-md bg-[#042C53] px-4 py-2 text-sm font-semibold text-white sm:col-span-2">Guardar experiencia</button>
    </form>

    <div class="mt-4 space-y-3">
        @forelse ($estudiante->experiencias as $experiencia)
            <article x-data="{ editing: false }" class="rounded-md border border-gray-200 p-3 dark:border-gray-700">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h4 class="break-words font-medium text-gray-900 dark:text-gray-100">{{ $experiencia->cargo }}</h4>
                        <p class="break-words text-sm text-gray-600 dark:text-gray-300">{{ $experiencia->empresa }} · {{ ucfirst($experiencia->tipo) }}</p>
                        <p class="text-xs text-gray-500">{{ $experiencia->fecha_inicio->format('m/Y') }} – {{ $experiencia->actual ? 'Actualidad' : ($experiencia->fecha_fin?->format('m/Y') ?? 'Sin fecha de fin') }}</p>
                        @if ($experiencia->descripcion)<p class="mt-2 break-words text-sm">{{ $experiencia->descripcion }}</p>@endif
                    </div>
                    <div class="flex shrink-0 gap-3 text-sm">
                        <button type="button" @click="editing = !editing" class="text-blue-700 underline">Editar</button>
                        <form method="POST" action="{{ route('estudiante.perfil.experiencias.destroy', $experiencia) }}" onsubmit="return confirm('¿Eliminar esta experiencia?');">
                            @csrf @method('DELETE')
                            <button class="text-red-700 underline">Eliminar</button>
                        </form>
                    </div>
                </div>
                <form x-cloak x-show="editing" x-transition method="POST" action="{{ route('estudiante.perfil.experiencias.update', $experiencia) }}" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @csrf @method('PUT')
                    <input name="empresa" value="{{ $experiencia->empresa }}" required maxlength="255" class="rounded-md border-gray-300 text-sm">
                    <input name="cargo" value="{{ $experiencia->cargo }}" required maxlength="255" class="rounded-md border-gray-300 text-sm">
                    <select name="tipo" required class="rounded-md border-gray-300 text-sm">
                        @foreach (['empleo', 'pasantia', 'voluntariado', 'freelance'] as $tipo)
                            <option value="{{ $tipo }}" @selected($experiencia->tipo === $tipo)>{{ ucfirst($tipo) }}</option>
                        @endforeach
                    </select>
                    <input name="ubicacion" value="{{ $experiencia->ubicacion }}" maxlength="255" placeholder="Ubicación" class="rounded-md border-gray-300 text-sm">
                    <label class="text-sm">Inicio <input name="fecha_inicio" type="date" value="{{ $experiencia->fecha_inicio->format('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
                    <label class="text-sm">Fin <input name="fecha_fin" type="date" value="{{ $experiencia->fecha_fin?->format('Y-m-d') }}" x-bind:disabled="$el.closest('form').querySelector('[name=actual]').checked" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></label>
                    <label class="flex items-center gap-2 text-sm sm:col-span-2"><input name="actual" type="checkbox" value="1" @checked($experiencia->actual) class="rounded border-gray-300"> Trabajo aquí actualmente</label>
                    <textarea name="descripcion" rows="3" maxlength="1000" class="rounded-md border-gray-300 text-sm sm:col-span-2">{{ $experiencia->descripcion }}</textarea>
                    <button class="rounded-md bg-[#042C53] px-4 py-2 text-sm font-semibold text-white sm:col-span-2">Guardar cambios</button>
                </form>
            </article>
        @empty
            <p class="text-sm text-gray-500">Aún no has agregado experiencia laboral o pasantías.</p>
        @endforelse
    </div>
</section>
