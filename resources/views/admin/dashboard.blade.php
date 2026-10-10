<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] bg-slate-100 px-4 py-8 sm:px-6 sm:py-12">
        <main class="mx-auto max-w-7xl">
            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800 shadow-2xl shadow-slate-900/15">
                <div class="border-b border-white/10 px-6 py-7 sm:px-8 sm:py-8">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-400">Bolsa de Empleo ITSC</p>
                            <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Panel de Administración</h1>
                            <p class="mt-2 text-sm text-slate-400">Resumen general de la plataforma</p>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-500/15 text-sky-300">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 0 0-16 0m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Bienvenido,</p>
                                <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <article class="rounded-xl border border-white/10 bg-slate-800/60 p-5 transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-400">Estudiantes</p>
                                    <p class="mt-3 text-3xl font-bold tabular-nums text-sky-300">{{ number_format($totalEstudiantes) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Registrados en la plataforma</p>
                                </div>
                                <span class="rounded-lg bg-sky-400/10 p-2.5 text-sky-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-7.87a4 4 0 0 1 0 7.75" />
                                    </svg>
                                </span>
                            </div>
                        </article>

                        <article class="rounded-xl border border-white/10 bg-slate-800/60 p-5 transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-400">Empresas aprobadas</p>
                                    <p class="mt-3 text-3xl font-bold tabular-nums text-emerald-300">{{ number_format($empresasAprobadas) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Con acceso a la plataforma</p>
                                </div>
                                <span class="rounded-lg bg-emerald-400/10 p-2.5 text-emerald-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18m6 0V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01m8-5v.01m0 3v.01" />
                                    </svg>
                                </span>
                            </div>
                        </article>

                        <article class="rounded-xl border border-amber-400/15 bg-amber-400/[0.06] p-5 transition duration-200 hover:-translate-y-0.5 hover:bg-amber-400/10">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-400">Empresas pendientes</p>
                                    <p class="mt-3 text-3xl font-bold tabular-nums text-amber-300">{{ number_format($empresasPendientes) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Esperando revisión</p>
                                </div>
                                <span class="rounded-lg bg-amber-400/10 p-2.5 text-amber-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.4 0Z" />
                                    </svg>
                                </span>
                            </div>
                        </article>

                        <article class="rounded-xl border border-white/10 bg-slate-800/60 p-5 transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-400">Vacantes activas</p>
                                    <p class="mt-3 text-3xl font-bold tabular-nums text-cyan-300">{{ number_format($vacantesActivas) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Oportunidades publicadas</p>
                                </div>
                                <span class="rounded-lg bg-cyan-400/10 p-2.5 text-cyan-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 11 18-5-5 18-4-8-9-5Zm9 5 5-5" />
                                    </svg>
                                </span>
                            </div>
                        </article>
                    </div>

                    <div class="mt-8 border-t border-white/10 pt-6">
                        <h2 class="text-sm font-semibold text-slate-200">Accesos de administración</h2>
                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <a href="{{ route('admin.empresas.index') }}" class="group inline-flex min-h-12 items-center justify-between gap-3 rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-950/20 transition duration-200 hover:-translate-y-0.5 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-slate-900">
                                <span class="inline-flex items-center gap-3">
                                    <svg class="h-5 w-5 text-blue-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18m6 0V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01m8-5v.01m0 3v.01" />
                                    </svg>
                                    Gestionar empresas
                                </span>
                                <svg class="h-4 w-4 text-blue-200 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                            </a>

                            <a href="{{ route('admin.carreras.index') }}" class="group inline-flex min-h-12 items-center justify-between gap-3 rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-800 shadow-lg shadow-slate-950/10 transition duration-200 hover:-translate-y-0.5 hover:bg-white focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 focus:ring-offset-slate-900">
                                <span class="inline-flex items-center gap-3">
                                    <svg class="h-5 w-5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14 3 9l9-5 9 5-9 5Zm-9-5v7l9 5 9-5V9m-9 5v7" />
                                    </svg>
                                    Gestionar carreras
                                </span>
                                <svg class="h-4 w-4 text-slate-500 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                            </a>

                            <a href="{{ route('admin.usuarios.index') }}" class="group inline-flex min-h-12 items-center justify-between gap-3 rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-950/20 transition duration-200 hover:-translate-y-0.5 hover:bg-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-300 focus:ring-offset-2 focus:ring-offset-slate-900 sm:col-span-2 xl:col-span-1">
                                <span class="inline-flex items-center gap-3">
                                    <svg class="h-5 w-5 text-teal-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-7.87a4 4 0 0 1 0 7.75" />
                                    </svg>
                                    Gestionar usuarios
                                </span>
                                <svg class="h-4 w-4 text-teal-100 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</x-app-layout>