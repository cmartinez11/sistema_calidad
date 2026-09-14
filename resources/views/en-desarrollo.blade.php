@extends('layouts.fullscreen')

@section('content')
<div class="max-w-4xl w-full mx-auto py-8 text-center space-y-8">

    <!-- Tarjeta Principal de Construcción Animatronica -->
    <div class="relative bg-gray-800/90 backdrop-blur-2xl rounded-3xl p-10 md:p-14 shadow-2xl border border-gray-700/80 overflow-hidden space-y-8">
        <!-- Glows de Fondo -->
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-emerald-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-amber-500/20 rounded-full blur-3xl animate-pulse"></div>

        <!-- Escudo / Iconografía Animada de Construcción -->
        <div class="relative inline-flex items-center justify-center">
            <!-- Anillo Giratorio -->
            <div class="w-32 h-32 rounded-full border-4 border-dashed border-emerald-500/40 animate-[spin_10s_linear_infinite] absolute"></div>
            <!-- Contenedor central -->
            <div class="w-20 h-20 bg-gradient-to-tr from-fenix to-emerald-500 text-white rounded-2xl shadow-2xl flex items-center justify-center text-3xl transform hover:scale-105 transition-transform">
                🛠️
            </div>
        </div>

        <!-- Títulos y Mensaje -->
        <div class="space-y-4 max-w-xl mx-auto relative z-10">
            <span class="inline-flex items-center space-x-2 px-4 py-1.5 bg-amber-500/20 text-amber-300 border border-amber-500/40 rounded-full text-xs font-black uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                <span>Módulo de {{ $modulo ?? 'Proceso' }} en Desarrollo</span>
            </span>

            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                Somos Fénix, estamos trabajando en ello
            </h1>

            <p class="text-sm md:text-base text-gray-300 font-medium leading-relaxed">
                {{ $descripcion ?? 'Estamos diseñando una experiencia avanzada para la gestión y auditoría de calidad de este proceso.' }}
            </p>
        </div>

        <!-- Tarjeta de Avance Simulado -->
        <div class="max-w-md mx-auto bg-gray-900/60 p-5 rounded-2xl border border-gray-700/60 space-y-3 relative z-10">
            <div class="flex items-center justify-between text-xs font-bold text-gray-300">
                <span>Estado de implementación</span>
                <span class="text-emerald-400">Próximamente versión 1.5</span>
            </div>
            <div class="w-full bg-gray-700/60 h-2.5 rounded-full overflow-hidden">
                <div class="bg-gradient-to-r from-fenix to-emerald-400 h-2.5 rounded-full animate-pulse" style="width: 65%"></div>
            </div>
        </div>

        <!-- Acciones: Regresar -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4 relative z-10">
            <a href="{{ route('modulos.index') }}" 
               class="px-8 py-3.5 bg-fenix hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg hover:shadow-xl transition-all flex items-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Volver a la Selección de Módulos</span>
            </a>

            <a href="{{ route('dashboard') }}" 
               class="px-6 py-3.5 bg-gray-700/70 hover:bg-gray-700 text-gray-200 rounded-xl text-xs font-bold transition-all">
                Ir al Dashboard de Preformas
            </a>
        </div>
    </div>

</div>
@endsection
