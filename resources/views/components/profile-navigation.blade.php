@php
    $role = auth()->user()->rol;
    $links = match ($role) {
        'admin' => [
            ['label' => 'Empresas', 'route' => 'admin.empresas.index', 'active' => request()->routeIs('admin.empresas.*')],
            ['label' => 'Vacantes', 'route' => 'admin.vacantes.index', 'active' => request()->routeIs('admin.vacantes.*')],
            ['label' => 'Carreras', 'route' => 'admin.carreras.index', 'active' => request()->routeIs('admin.carreras.*')],
            ['label' => 'Usuarios', 'route' => 'admin.usuarios.index', 'active' => request()->routeIs('admin.usuarios.*')],
        ],
        'empresa' => [
            ['label' => 'Vacantes', 'route' => 'empresa.vacantes.index', 'active' => request()->routeIs('empresa.vacantes.*', 'empresa.postulaciones.*')],
            ['label' => 'Mi perfil', 'route' => 'empresa.perfil.edit', 'active' => request()->routeIs('empresa.perfil.*')],
        ],
        default => [
            ['label' => 'Vacantes', 'route' => 'estudiante.vacantes.index', 'active' => request()->routeIs('estudiante.vacantes.*', 'estudiante.empresas.*')],
            ['label' => 'Postulaciones', 'route' => 'estudiante.postulaciones.index', 'active' => request()->routeIs('estudiante.postulaciones.*')],
            ['label' => 'Mi perfil', 'route' => 'estudiante.perfil.edit', 'active' => request()->routeIs('estudiante.perfil.*')],
        ],
    };

    $links[] = [
        'label' => 'Notificaciones',
        'route' => 'notifications.index',
        'active' => request()->routeIs('notifications.*'),
    ];
    $links[] = [
        'label' => 'Cuenta',
        'route' => 'profile.edit',
        'active' => request()->routeIs('profile.*'),
    ];
@endphp

<nav x-data="{ open: false }" @click.outside="open = false" aria-label="Navegación principal"
    class="relative z-40 mx-auto mb-4 max-w-7xl rounded-xl bg-itsc-slate-700 px-4 py-2.5 text-white">
    <div class="flex min-h-10 items-center justify-between gap-4">
        <a href="{{ route('dashboard') }}" class="inline-flex min-w-0 items-center gap-2.5 rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-100">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border-[3px] border-itsc-red-600 bg-white">
                <img src="{{ asset('images/logo-itsc.png') }}" alt="" width="32" height="32" class="h-full w-full object-contain">
            </span>
            <span class="truncate text-sm font-medium">ITSC · Bolsa de empleo</span>
        </a>

        <div class="flex items-center gap-2">
            <div class="hidden items-center gap-4 lg:gap-6 xl:flex">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}"
                        @if ($link['active']) aria-current="page" @endif
                        class="flex min-h-10 items-center border-b-2 py-2 text-sm {{ $link['active'] ? 'border-itsc-red-600 text-white' : 'border-transparent text-slate-300 transition-colors hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>

            <x-notification-bell />

            <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                aria-controls="profile-mobile-navigation"
                class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg text-slate-300 transition-colors hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-itsc-red-100 xl:hidden">
                <span class="sr-only" x-text="open ? 'Cerrar menú' : 'Abrir menú'"></span>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>

    <div id="profile-mobile-navigation" x-cloak x-show="open"
        class="absolute left-0 right-0 top-full z-50 mt-2 rounded-xl border border-white/10 bg-itsc-slate-700 p-2 shadow-lg xl:hidden">
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}"
                @if ($link['active']) aria-current="page" @endif
                class="block min-h-10 rounded-md px-2 py-2 text-sm {{ $link['active'] ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                {{ $link['label'] }}
            </a>
        @endforeach
    </div>
</nav>
