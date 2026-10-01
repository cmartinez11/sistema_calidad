<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Grupo Fénix - Selección de Procesos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-black text-gray-100 font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- BARRA SUPERIOR LIMPIA (SIN SIDEBAR) -->
    <header class="w-full bg-gray-900/80 backdrop-blur-md border-b border-gray-800 px-6 py-4 flex items-center justify-between z-20">
        <!-- Logo / Marca -->
        <div class="flex items-center space-x-3">
            <img src="{{ asset('logo2.png') }}" alt="Logo Grupo Fénix" class="h-10 w-auto object-contain">
            <div>
                <span class="text-white font-black tracking-wider text-base block">GRUPO FÉNIX</span>
                <span class="text-[10px] text-gray-400 uppercase font-mono block">Sistema de Gestión de Calidad</span>
            </div>
        </div>

        <!-- Usuario Autenticado & Cerrar Sesión -->
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-3 bg-gray-800/60 px-3.5 py-1.5 rounded-xl border border-gray-700/50">
                <div class="w-7 h-7 rounded-full bg-fenix text-white font-bold text-xs flex items-center justify-center">
                    {{ strtoupper(substr(Auth::user()->name ?? Auth::user()->username ?? 'U', 0, 2)) }}
                </div>
                <span class="text-xs font-bold text-gray-200 hidden sm:inline">
                    {{ Auth::user()->name ?? Auth::user()->username ?? 'Usuario' }}
                </span>
            </div>

            <!-- Botón Cerrar Sesión -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        title="Cerrar Sesión"
                        class="px-3.5 py-1.5 bg-red-600/20 hover:bg-red-600 text-red-300 hover:text-white border border-red-500/30 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="hidden md:inline">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </header>

    <!-- CONTENIDO PRINCIPAL FULL-SCREEN -->
    <main class="flex-1 flex items-center justify-center p-6 md:p-10">
        @yield('content')
    </main>

    <!-- PIE DE PÁGINA FULL-SCREEN -->
    <footer class="w-full py-4 px-6 border-t border-gray-800/80 text-center text-xs text-gray-500 font-mono">
        Grupo Fénix &copy; {{ date('Y') }} - Todos los derechos reservados | Control de Calidad v1.0
    </footer>

</body>
</html>
