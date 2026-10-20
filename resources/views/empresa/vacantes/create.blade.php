<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#696969] leading-tight">
            {{ __('Nueva Vacante') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <form method="POST" action="{{ route('empresa.vacantes.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="titulo" :value="__('Título')" class="form-label-light" />
                        <x-text-input id="titulo" class="block mt-1 w-full" type="text" name="titulo" :value="old('titulo')" required />
                        <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="descripcion" :value="__('Descripción')" class="form-label-light" />
                        <textarea id="descripcion" name="descripcion" rows="4" required
                            class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">{{ old('descripcion') }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="requisitos" :value="__('Requisitos')" class="form-label-light" />
                        <textarea id="requisitos" name="requisitos" rows="3"
                            class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">{{ old('requisitos') }}</textarea>
                        <x-input-error :messages="$errors->get('requisitos')" class="mt-2" />
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="tipo_empleo" :value="__('Tipo de empleo')" class="form-label-light" />
                            <select id="tipo_empleo" name="tipo_empleo" required
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="tiempo_completo" @selected(old('tipo_empleo') == 'tiempo_completo')>Tiempo completo</option>
                                <option value="medio_tiempo" @selected(old('tipo_empleo') == 'medio_tiempo')>Medio tiempo</option>
                                <option value="pasantia" @selected(old('tipo_empleo') == 'pasantia')>Pasantía</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="modalidad" :value="__('Modalidad')" class="form-label-light" />
                            <select id="modalidad" name="modalidad" required
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="presencial" @selected(old('modalidad') == 'presencial')>Presencial</option>
                                <option value="remoto" @selected(old('modalidad') == 'remoto')>Remoto</option>
                                <option value="hibrido" @selected(old('modalidad') == 'hibrido')>Híbrido</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="ubicacion" :value="__('Ubicación')" class="form-label-light" />
                        <x-text-input id="ubicacion" class="block mt-1 w-full" type="text" name="ubicacion" :value="old('ubicacion')" />
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="salario_min" :value="__('Salario mínimo (RD$)')" class="form-label-light" />
                            <x-text-input id="salario_min" class="block mt-1 w-full" type="number" step="0.01" name="salario_min" :value="old('salario_min')" />
                            <x-input-error :messages="$errors->get('salario_min')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="salario_max" :value="__('Salario máximo (RD$)')" class="form-label-light" />
                            <x-text-input id="salario_max" class="block mt-1 w-full" type="number" step="0.01" name="salario_max" :value="old('salario_max')" />
                            <x-input-error :messages="$errors->get('salario_max')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="fecha_cierre" :value="__('Fecha de cierre')" class="form-label-light" />
                        <x-text-input id="fecha_cierre" class="block mt-1 w-full" type="date" name="fecha_cierre" :value="old('fecha_cierre')" />
                        <x-input-error :messages="$errors->get('fecha_cierre')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label :value="__('Carreras dirigidas')" class="form-label-light" />
                        <div class="mt-2 grid grid-cols-1 gap-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-700 rounded-md p-3 sm:grid-cols-2">
                            @foreach ($carreras as $carrera)
                                <label class="flex items-center gap-2 text-sm form-label-light">
                                    <input type="checkbox" name="carreras[]" value="{{ $carrera->id }}"
                                        @checked(in_array($carrera->id, old('carreras', [])))
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                    {{ $carrera->nombre }}
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('carreras')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <a href="{{ route('empresa.vacantes.index') }}" class="text-center text-sm text-gray-600 dark:text-gray-400 underline sm:mr-4">Cancelar</a>
                        <x-primary-button class="w-full justify-center sm:w-auto">{{ __('Guardar borrador') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>