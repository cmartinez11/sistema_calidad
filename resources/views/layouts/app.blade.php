<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <a href="#" class="flex items-center px-4 py-3 text-white bg-fenix rounded-xl shadow-lg transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Dashboard
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 hover:bg-gray-800 rounded-xl transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Lotes y Calidad
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 hover:bg-gray-800 rounded-xl transition-all">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Parámetros Preforma
                    </a>
                </nav>
            </div>
            
            <!-- Pie del Sidebar -->
            <div class="p-4 border-t border-gray-800 text-xs text-center text-gray-500">
                Sistema de Calidad v1.0
            </div>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            
            <!-- Barra Superior -->
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-8 z-10">
                <div class="text-sm text-gray-500">
                    Pages / <span class="text-gray-800 font-semibold">Dashboard</span>
                </div>
                <div class="flex items-center space-x-4">
                    <input type="text" placeholder="Buscar..." class="px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-fenix">
                    <span class="font-medium text-gray-700 text-sm">Hola, Cristhian</span>
                </div>
            </header>

            <!-- Contenido Dinámico (Dashboard Cards & Widgets) -->
            <main class="flex-1 p-8">
                @yield('content')
            </main>

        </div>
    </div>

</body>
</html>