<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Grupo Fénix - Sistema de Calidad</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR (Menú lateral oscuro con acentos en verde #30732B) -->
        <aside class="w-64 bg-gray-900 text-gray-300 flex flex-col justify-between hidden md:flex shadow-xl">
            <div>
                <!-- Logo / Título -->
                <div class="px-6 py-5 border-b border-gray-800 flex items-center space-x-3">
                    <div class="bg-fenix text-white p-2 rounded-lg shadow-md font-bold">GF</div>
                    <span class="text-white font-bold tracking-wide">GRUPO FÉNIX</span>
                </div>

                <!-- Enlaces del Menú -->
                <nav class="mt-6 px-4 space-y-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('dashboard') ? 'text-white bg-fenix rounded-xl shadow-lg' : 'hover:bg-gray-800 rounded-xl' }} transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('productos.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('productos.*') ? 'text-white bg-fenix rounded-xl shadow-lg' : 'hover:bg-gray-800 rounded-xl' }} transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Productos
                    </a>
                    <a href="{{ route('maquinas.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('maquinas.*') ? 'text-white bg-fenix rounded-xl shadow-lg' : 'hover:bg-gray-800 rounded-xl' }} transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Máquinas
                    </a>
                    <a href="{{ route('moldes.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('moldes.*') ? 'text-white bg-fenix rounded-xl shadow-lg' : 'hover:bg-gray-800 rounded-xl' }} transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Moldes
                    </a>
                    <a href="{{ route('resinas.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('resinas.*') ? 'text-white bg-fenix rounded-xl shadow-lg' : 'hover:bg-gray-800 rounded-xl' }} transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.605 15.12a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        Resinas
                    </a>
                    <a href="{{ route('operarios.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('operarios.*') ? 'text-white bg-fenix rounded-xl shadow-lg' : 'hover:bg-gray-800 rounded-xl' }} transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Operarios
                    </a>
                    <a href="{{ route('inspecciones-cavidades.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('inspecciones-cavidades.*') ? 'text-white bg-fenix rounded-xl shadow-lg' : 'hover:bg-gray-800 rounded-xl' }} transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Auditorías por Cavidad
                    </a>
                    <a href="{{ route('inspecciones-calidad.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('inspecciones-calidad.*') ? 'text-white bg-fenix rounded-xl shadow-lg' : 'hover:bg-gray-800 rounded-xl' }} transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Resumen de Calidad
                    </a>
                    <a href="{{ route('pnc.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('pnc.*') ? 'text-white bg-fenix rounded-xl shadow-lg' : 'hover:bg-gray-800 rounded-xl' }} transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Producto No Conforme (PNC)
                    </a>
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <a href="{{ route('users.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('users.*') ? 'text-white bg-fenix rounded-xl shadow-lg' : 'hover:bg-gray-800 rounded-xl' }} transition-all">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Gestión de Usuarios
                        </a>
                    @endif
                </nav>
            </div>
            
            <!-- Pie del Sidebar y Botón Cerrar Sesión -->
            <div class="p-4 border-t border-gray-800 space-y-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center justify-center px-4 py-2.5 bg-gray-800/80 hover:bg-red-600/90 text-gray-300 hover:text-white rounded-xl text-xs font-bold transition-all border border-gray-700/50 hover:border-red-500 cursor-pointer shadow-sm group">
                        <svg class="w-4 h-4 mr-2 text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>

                <div class="text-[10px] text-center text-gray-500 font-mono">
                    Sistema de Calidad v1.0
                </div>
            </div>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            <!-- Barra Superior -->
            <header class="bg-white shadow-sm h-16 shrink-0 flex items-center justify-between px-8 z-10">
                <!-- Fecha y Hora unificadas -->
                <div class="flex items-center space-x-4 bg-gray-50 px-4 py-1.5 rounded-xl border border-gray-100">
                    <span class="text-xs font-bold text-gray-600">
                        {{ ucfirst(\Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D MMMM YYYY')) }}
                    </span>
                    
                    <div class="h-4 w-px bg-gray-200"></div>

                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-fenix" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xs font-bold text-gray-700">HORA EXACTA: <span id="reloj"></span></span>
                    </div>
                </div>

                <!-- Bienvenida al Usuario y Botón Cerrar Sesión -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-fenix/10 border border-fenix/30 text-fenix font-bold text-xs flex items-center justify-center">
                            {{ strtoupper(substr(Auth::user()->name ?? Auth::user()->username ?? 'U', 0, 2)) }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <span class="block text-xs font-bold text-gray-800">{{ Auth::user()->name ?? Auth::user()->username ?? 'Usuario' }}</span>
                            <span class="block text-[10px] text-gray-400 font-mono uppercase">{{ Auth::user()->role->nombre ?? 'Usuario' }}</span>
                        </div>
                    </div>

                    <div class="h-6 w-px bg-gray-200"></div>

                    <!-- Botón Cerrar Sesión (Header) -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                title="Cerrar Sesión"
                                class="px-3.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden md:inline">Cerrar Sesión</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Contenido Dinámico -->
            <main class="flex-1 p-6 md:p-8">
                @yield('content')
            </main>

        </div>
    </div>

    <script>
        let serverTime = new Date("{{ $serverTime ?? now()->toIso8601String() }}");

        function updateServerClock() {
            serverTime.setSeconds(serverTime.getSeconds() + 1);

            let hours = String(serverTime.getHours()).padStart(2, '0');
            let minutes = String(serverTime.getMinutes()).padStart(2, '0');
            let seconds = String(serverTime.getSeconds()).padStart(2, '0');

            const clockEl = document.getElementById('reloj') || document.getElementById('server-clock');
            if (clockEl) {
                clockEl.innerText = `${hours}:${minutes}:${seconds}`;
            }
        }

        setInterval(updateServerClock, 1000);
        updateServerClock();
    </script>
</body>
</html>