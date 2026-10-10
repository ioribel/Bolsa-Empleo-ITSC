<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bolsa de Empleo del ITSC</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-white">

    <!-- Barra superior -->
    <nav class="bg-[#042C53]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <span class="text-white font-bold text-lg tracking-tight">Bolsa de Empleo ITSC</span>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-white hover:text-gray-200">Ir al Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-200 hover:text-white">Iniciar sesión</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-[#A32D2D] hover:bg-[#8a2525] rounded-md text-sm font-semibold text-white transition">Registrarme</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="bg-[#042C53]">
        <div class="max-w-5xl mx-auto px-6 py-24 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-white leading-tight">
                Conectamos el talento del ITSC<br>con las empresas del país
            </h1>
            <p class="mt-6 text-lg text-gray-200 max-w-2xl mx-auto">
                La plataforma oficial de empleo para estudiantes, egresados y empresas del
                Instituto Técnico Superior Comunitario.
            </p>

            @guest
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-[#A32D2D] hover:bg-[#8a2525] rounded-md font-semibold text-white transition">
                    Soy estudiante o egresado
                </a>
                <a href="{{ route('register.empresa') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-white hover:bg-gray-100 rounded-md font-semibold text-[#042C53] transition">
                    Soy una empresa
                </a>
            </div>
            @endguest
        </div>
    </section>

    <!-- Cómo funciona -->
    <section class="max-w-6xl mx-auto px-6 py-20">
        <h2 class="text-2xl font-bold text-center text-gray-900">¿Cómo funciona?</h2>

        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="text-center">
                <div class="w-14 h-14 mx-auto rounded-full bg-[#042C53] text-white flex items-center justify-center font-bold text-xl">1</div>
                <h3 class="mt-4 font-semibold text-gray-900">Regístrate</h3>
                <p class="mt-2 text-sm text-gray-600">Crea tu cuenta como estudiante, egresado o empresa en pocos minutos.</p>
            </div>
            <div class="text-center">
                <div class="w-14 h-14 mx-auto rounded-full bg-[#042C53] text-white flex items-center justify-center font-bold text-xl">2</div>
                <h3 class="mt-4 font-semibold text-gray-900">Explora o publica</h3>
                <p class="mt-2 text-sm text-gray-600">Los estudiantes ven vacantes dirigidas a su carrera; las empresas publican ofertas reales.</p>
            </div>
            <div class="text-center">
                <div class="w-14 h-14 mx-auto rounded-full bg-[#042C53] text-white flex items-center justify-center font-bold text-xl">3</div>
                <h3 class="mt-4 font-semibold text-gray-900">Conéctate</h3>
                <p class="mt-2 text-sm text-gray-600">Postúlate con un clic y da seguimiento al estado de cada postulación.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-8 text-center text-sm text-gray-500">
            Instituto Técnico Superior Comunitario (ITSC) — Bolsa de Empleo
        </div>
    </footer>

</body>
</html>