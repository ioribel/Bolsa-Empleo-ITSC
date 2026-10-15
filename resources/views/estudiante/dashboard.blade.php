<x-app-layout>
    <div class="min-h-[calc(100vh-4rem)] bg-slate-100 px-4 py-8 sm:px-6 sm:py-12">
        <main class="mx-auto max-w-7xl">
            <section class="overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800 shadow-2xl shadow-slate-900/15">
                <div class="border-b border-white/10 px-6 py-7 sm:px-8 sm:py-8">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-sky-400">Bolsa de Empleo ITSC</p>
                            <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Panel de Estudiante</h1>
                            <p class="mt-2 text-sm text-slate-400">Oportunidades y seguimiento de tus postulaciones</p>
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
                                    <p class="text-sm font-medium text-slate-400">Vacantes para tu carrera</p>
                                    <p class="mt-3 text-3xl font-bold tabular-nums text-sky-300">{{ number_format($vacantesDisponibles) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Oportunidades disponibles</p>
                                </div>
                                <span class="rounded-lg bg-sky-400/10 p-2.5 text-sky-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v13H3zM8 7V4h8v3m-13 5h18m-12 0v2h6v-2" />
                                    </svg>
                                </span>
                            </div>
                        </article>

                        <article class="rounded-xl border border-white/10 bg-slate-800/60 p-5 transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-400">Mis postulaciones</p>
                                    <p class="mt-3 text-3xl font-bold tabular-nums text-cyan-300">{{ number_format($misPostulaciones) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Solicitudes enviadas</p>
                                </div>
                                <span class="rounded-lg bg-cyan-400/10 p-2.5 text-cyan-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                                    </svg>
                                </span>
                            </div>
                        </article>

                        <article class="rounded-xl border border-emerald-400/15 bg-emerald-400/[0.06] p-5 transition duration-200 hover:-translate-y-0.5 hover:bg-emerald-400/10">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-400">Postulaciones aceptadas</p>
                                    <p class="mt-3 text-3xl font-bold tabular-nums text-emerald-300">{{ number_format($postulacionesAceptadas) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Respuestas favorables</p>
                                </div>
                                <span class="rounded-lg bg-emerald-400/10 p-2.5 text-emerald-300">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                    </svg>
                                </span>
                            </div>
                        </article>
                    </div>

                    <div class="mt-8 border-t border-white/10 pt-6">
                        <h2 class="text-sm font-semibold text-slate-200">Accesos rápidos</h2>
                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <a href="{{ route('estudiante.vacantes.index') }}" class="group inline-flex min-h-12 items-center justify-between gap-3 rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-950/20 transition duration-200 hover:-translate-y-0.5 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-slate-900">
                                Ver vacantes
                                <svg class="h-4 w-4 text-blue-200 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                            </a>
                            <a href="{{ route('estudiante.postulaciones.index') }}" class="group inline-flex min-h-12 items-center justify-between gap-3 rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-800 shadow-lg shadow-slate-950/10 transition duration-200 hover:-translate-y-0.5 hover:bg-white focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 focus:ring-offset-slate-900">
                                Mis postulaciones
                                <svg class="h-4 w-4 text-slate-500 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                            </a>
                            <a href="{{ route('estudiante.perfil.edit') }}" class="group inline-flex min-h-12 items-center justify-between gap-3 rounded-xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-950/20 transition duration-200 hover:-translate-y-0.5 hover:bg-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-300 focus:ring-offset-2 focus:ring-offset-slate-900">
                                Mi perfil
                                <svg class="h-4 w-4 text-teal-100 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</x-app-layout>