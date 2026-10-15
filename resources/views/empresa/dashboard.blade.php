<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] bg-slate-100 px-4 py-8 sm:px-6 sm:py-12">
        <main class="mx-auto max-w-7xl">
            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800 shadow-2xl shadow-slate-900/15">
                <div class="border-b border-white/10 px-6 py-7 sm:px-8 sm:py-8">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-400">Bolsa de Empleo ITSC</p>
                            <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Panel de Empresa</h1>
                            <p class="mt-2 text-sm text-slate-400">Resumen de tus vacantes y postulaciones recibidas</p>
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
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <article class="rounded-xl border border-white/10 bg-slate-800/60 p-5 transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-400">Vacantes activas</p>
                                    <p class="mt-3 text-3xl font-bold tabular-nums text-sky-300">{{ number_format($vacantesActivas) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Oportunidades publicadas</p>
                                </div>
                                <span class="rounded-lg bg-sky-400/10 p-2.5 text-sky-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18m6 0V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01m8-5v.01m0 3v.01" />
                                    </svg>
                                </span>
                            </div>
                        </article>

                        <article class="rounded-xl border border-white/10 bg-slate-800/60 p-5 transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-400">Postulaciones recibidas</p>
                                    <p class="mt-3 text-3xl font-bold tabular-nums text-emerald-300">{{ number_format($postulacionesRecibidas) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Candidatos interesados</p>
                                </div>
                                <span class="rounded-lg bg-emerald-400/10 p-2.5 text-emerald-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                                    </svg>
                                </span>
                            </div>
                        </article>

                        <article class="rounded-xl border border-amber-400/15 bg-amber-400/[0.06] p-5 transition duration-200 hover:-translate-y-0.5 hover:bg-amber-400/10">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-400">Pendientes de revisar</p>
                                    <p class="mt-3 text-3xl font-bold tabular-nums text-amber-300">{{ number_format($pendientesRevisar) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Requieren tu atención</p>
                                </div>
                                <span class="rounded-lg bg-amber-400/10 p-2.5 text-amber-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 3.86a2 2 0 0 0-3.4 0Z" />
                                    </svg>
                                </span>
                            </div>
                        </article>
                    </div>

                    <div class="mt-8 border-t border-white/10 pt-6">
                        <h2 class="text-sm font-semibold text-slate-200">Accesos de empresa</h2>
                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <a href="{{ route('empresa.vacantes.index') }}" class="group inline-flex min-h-12 items-center justify-between gap-3 rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-950/20 transition duration-200 hover:-translate-y-0.5 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-slate-900">
                                Gestionar vacantes
                                <svg class="h-4 w-4 text-blue-200 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</x-app-layout>