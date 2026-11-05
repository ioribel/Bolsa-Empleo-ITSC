<x-app-layout>
    <x-slot name="header">
        <h1 class="page-heading text-xl font-medium leading-tight">{{ __('Perfil de la empresa') }}</h1>
    </x-slot>

    <div class="profile-view bg-itsc-slate-50 px-3 py-4 sm:px-6 sm:py-6">
        <div class="mx-auto max-w-4xl space-y-3 rounded-xl bg-itsc-slate-50 p-1 sm:p-2">
            @if (session('status'))
                <div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 5000)" x-show="visible"
                    role="status" aria-live="polite" class="fixed right-4 top-4 z-50 rounded-xl border border-itsc-slate-100 bg-white px-4 py-3 text-sm text-itsc-slate-700">
                    {{ str_contains(mb_strtolower(session('status')), 'actualiz') ? 'Cambios guardados' : session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <p class="font-medium">Revisa los datos del formulario:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-profile-card>
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <svg class="h-[18px] w-[18px] text-itsc-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 9h.01M15 9h.01M9 12h.01M15 12h.01" />
                        </svg>
                        <h2 class="text-[15px] font-medium text-itsc-slate-900">Datos de la empresa</h2>
                    </div>
                </x-slot>

                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-[52px] w-[52px] shrink-0 items-center justify-center overflow-hidden rounded-full bg-itsc-slate-700 text-sm font-medium text-white">
                        @if ($empresa->logo_path)
                            <img src="{{ asset('storage/' . $empresa->logo_path) }}" alt="Logo de {{ $empresa->nombre_empresa }}" width="52" height="52" class="h-full w-full object-cover">
                        @else
                            {{ mb_substr($empresa->nombre_empresa, 0, 1) }}
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-base font-medium text-itsc-slate-900">{{ $empresa->nombre_empresa }}</p>
                        <p class="text-sm text-itsc-slate-500">Perfil institucional</p>
                    </div>
                </div>

                <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                    <x-profile-input label="Correo electrónico" id="empresa-correo" :value="$empresa->user->email" readonly />
                    <x-profile-input label="RNC" id="empresa-rnc" :value="$empresa->rnc" readonly />
                </div>

                <form method="POST" action="{{ route('empresa.perfil.update') }}" enctype="multipart/form-data"
                    class="space-y-4" x-data="{ saving: false }" @submit="saving = true">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <x-profile-input id="nombre_empresa" name="nombre_empresa" label="Nombre comercial" required
                            :value="old('nombre_empresa', $empresa->nombre_empresa)" :error="$errors->first('nombre_empresa')" />
                        <x-profile-input id="sector" name="sector" label="Sector o área empresarial"
                            placeholder="Ej. Tecnología, banca, salud"
                            :value="old('sector', $empresa->sector)" :error="$errors->first('sector')" />
                    </div>

                    <x-profile-input id="descripcion" name="descripcion" label="Descripción institucional" as="textarea" rows="5"
                        :value="old('descripcion', $empresa->descripcion)" :error="$errors->first('descripcion')" />

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <x-profile-input id="telefono" name="telefono" label="Teléfono de contacto público"
                            placeholder="Ej. +1 809 555 1234"
                            :value="old('telefono', $empresa->telefono)" :error="$errors->first('telefono')" />
                        <x-profile-input id="sitio_web" name="sitio_web" type="url" label="Sitio web oficial"
                            placeholder="https://www.miempresa.com"
                            :value="old('sitio_web', $empresa->sitio_web)" :error="$errors->first('sitio_web')" />
                    </div>

                    <div>
                        <label for="logo" class="mb-1 block text-xs text-itsc-slate-500">Logotipo de la empresa</label>
                        <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp"
                            aria-describedby="logo-help{{ $errors->first('logo') ? ' logo-error' : '' }}"
                            class="block min-h-10 w-full rounded-lg border border-itsc-slate-300 bg-white px-3 py-2 text-sm text-itsc-slate-700 file:mr-3 file:border-0 file:bg-itsc-slate-50 file:text-itsc-slate-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/15">
                        <p id="logo-help" class="mt-1 text-xs text-itsc-slate-500">Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 2 MB.</p>
                        @if ($errors->first('logo'))
                            <p id="logo-error" role="alert" class="mt-1 text-xs text-red-800">{{ $errors->first('logo') }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-itsc-slate-100 pt-4 sm:flex-row sm:justify-end">
                        <a href="{{ route('empresa.dashboard') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-itsc-slate-300 bg-white px-3.5 py-2 text-[13px] font-medium text-itsc-slate-700 transition-colors hover:bg-itsc-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/15">Cancelar</a>
                        <button type="submit" :disabled="saving" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-itsc-slate-700 px-3.5 py-2 text-[13px] font-medium text-white transition-colors hover:bg-itsc-slate-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-itsc-red-600/20 disabled:cursor-wait disabled:opacity-70">
                            <svg x-cloak x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
                            </svg>
                            <span x-text="saving ? 'Guardando…' : 'Guardar cambios'"></span>
                        </button>
                    </div>
                </form>
            </x-profile-card>
        </div>
    </div>
</x-app-layout>
