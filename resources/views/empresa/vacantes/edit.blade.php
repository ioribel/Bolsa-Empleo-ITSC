<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Vacante') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('empresa.vacantes.update', $vacante) }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="titulo" :value="__('Título')" />
                        <x-text-input id="titulo" class="block mt-1 w-full" type="text" name="titulo" :value="old('titulo', $vacante->titulo)" required />
                        <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="descripcion" :value="__('Descripción')" />
                        <textarea id="descripcion" name="descripcion" rows="4" required
                            class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">{{ old('descripcion', $vacante->descripcion) }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="requisitos" :value="__('Requisitos')" />
                        <textarea id="requisitos" name="requisitos" rows="3"
                            class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">{{ old('requisitos', $vacante->requisitos) }}</textarea>
                        <x-input-error :messages="$errors->get('requisitos')" class="mt-2" />
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tipo_empleo" :value="__('Tipo de empleo')" />
                            <select id="tipo_empleo" name="tipo_empleo" required
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="tiempo_completo" @selected(old('tipo_empleo', $vacante->tipo_empleo) == 'tiempo_completo')>Tiempo completo</option>
                                <option value="medio_tiempo" @selected(old('tipo_empleo', $vacante->tipo_empleo) == 'medio_tiempo')>Medio tiempo</option>
                                <option value="pasantia" @selected(old('tipo_empleo', $vacante->tipo_empleo) == 'pasantia')>Pasantía</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="modalidad" :value="__('Modalidad')" />
                            <select id="modalidad" name="modalidad" required
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="presencial" @selected(old('modalidad', $vacante->modalidad) == 'presencial')>Presencial</option>
                                <option value="remoto" @selected(old('modalidad', $vacante->modalidad) == 'remoto')>Remoto</option>
                                <option value="hibrido" @selected(old('modalidad', $vacante->modalidad) == 'hibrido')>Híbrido</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="ubicacion" :value="__('Ubicación')" />
                        <x-text-input id="ubicacion" class="block mt-1 w-full" type="text" name="ubicacion" :value="old('ubicacion', $vacante->ubicacion)" />
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="salario_min" :value="__('Salario mínimo (RD$)')" />
                            <x-text-input id="salario_min" class="block mt-1 w-full" type="number" step="0.01" name="salario_min" :value="old('salario_min', $vacante->salario_min)" />
                            <x-input-error :messages="$errors->get('salario_min')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="salario_max" :value="__('Salario máximo (RD$)')" />
                            <x-text-input id="salario_max" class="block mt-1 w-full" type="number" step="0.01" name="salario_max" :value="old('salario_max', $vacante->salario_max)" />
                            <x-input-error :messages="$errors->get('salario_max')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="fecha_cierre" :value="__('Fecha de cierre')" />
                        <x-text-input id="fecha_cierre" class="block mt-1 w-full" type="date" name="fecha_cierre" :value="old('fecha_cierre', $vacante->fecha_cierre?->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('fecha_cierre')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label :value="__('Carreras dirigidas')" />
                        <div class="mt-2 grid grid-cols-2 gap-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-700 rounded-md p-3">
                            @foreach ($carreras as $carrera)
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="carreras[]" value="{{ $carrera->id }}"
                                        @checked(in_array($carrera->id, old('carreras', $vacante->carreras->pluck('id')->toArray())))
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                    {{ $carrera->nombre }}
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('carreras')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('empresa.vacantes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 underline mr-4">Cancelar</a>
                        <x-primary-button>{{ __('Guardar cambios') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>