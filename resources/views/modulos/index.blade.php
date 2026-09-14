@extends('layouts.fullscreen')

@section('content')
<div class="max-w-6xl w-full mx-auto space-y-10 py-6">

    <!-- Header Principal -->
    <div class="text-center space-y-4 max-w-2xl mx-auto">
        <div class="inline-flex items-center space-x-2 px-4 py-1.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-xs font-bold shadow-inner">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Módulos Operativos de Calidad</span>
        </div>
        <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
            Seleccione el Proceso de Producción
        </h1>
        <p class="text-sm md:text-base text-gray-400 font-medium">
            Elija el área que desea gestionar para acceder a los controles analíticos y auditorías de calidad.
        </p>
    </div>

    <!-- TARJETAS DE PROCESOS (3 Cuadros Interactivos Fullscreen) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- 1. TARJETA PREFORMAS (PROCESO ACTIVO) -->
        <a href="{{ route('dashboard') }}" 
           class="group relative bg-gray-800/90 backdrop-blur-xl p-8 rounded-3xl shadow-xl hover:shadow-2xl border-2 border-gray-700/60 hover:border-emerald-500 transition-all duration-300 flex flex-col justify-between overflow-hidden transform hover:-translate-y-2 cursor-pointer">
            <div class="absolute -top-12 -right-12 w-36 h-36 bg-emerald-500/20 rounded-full blur-3xl group-hover:bg-emerald-500/30 transition-all"></div>
            
            <div class="space-y-6 relative z-10">
                <div class="flex items-center justify-between">
                    <div class="w-16 h-16 bg-fenix text-white rounded-2xl flex items-center justify-center text-3xl shadow-lg group-hover:scale-110 transition-transform">
                        🧪
                    </div>
                    <span class="px-3.5 py-1.5 bg-emerald-500/20 text-emerald-300 text-[11px] font-black uppercase tracking-wider rounded-full border border-emerald-500/40">
                        Proceso Activo
                    </span>
                </div>

                <div class="space-y-3">
                    <h2 class="text-2xl font-black text-white group-hover:text-emerald-400 transition-colors">
                        Preformas
                    </h2>
                    <p class="text-xs text-gray-400 leading-relaxed font-medium">
                        Gestión completa de auditorías por cavidad, peso, parámetros de inyección y reportes PNC.
                    </p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-700/60 flex items-center justify-between text-xs font-bold text-emerald-400 group-hover:text-emerald-300 relative z-10">
                <span>Ingresar al Módulo</span>
                <div class="w-9 h-9 rounded-full bg-emerald-500/20 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-all">
                    <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </div>
            </div>
        </a>

        <!-- 2. TARJETA TERMOFORMADO (EN DESARROLLO) -->
        <a href="{{ route('termoformado') }}" 
           class="group relative bg-gray-800/90 backdrop-blur-xl p-8 rounded-3xl shadow-xl hover:shadow-2xl border-2 border-gray-700/60 hover:border-amber-400 transition-all duration-300 flex flex-col justify-between overflow-hidden transform hover:-translate-y-2 cursor-pointer">
            <div class="absolute -top-12 -right-12 w-36 h-36 bg-amber-500/20 rounded-full blur-3xl group-hover:bg-amber-500/30 transition-all"></div>
            
            <div class="space-y-6 relative z-10">
                <div class="flex items-center justify-between">
                    <div class="w-16 h-16 bg-amber-500 text-white rounded-2xl flex items-center justify-center text-3xl shadow-lg group-hover:scale-110 transition-transform">
                        📦
                    </div>
                    <span class="px-3.5 py-1.5 bg-amber-500/20 text-amber-300 text-[11px] font-black uppercase tracking-wider rounded-full border border-amber-500/40">
                        Próximamente
                    </span>
                </div>

                <div class="space-y-3">
                    <h2 class="text-2xl font-black text-white group-hover:text-amber-400 transition-colors">
                        Termoformado
                    </h2>
                    <p class="text-xs text-gray-400 leading-relaxed font-medium">
                        Control de calidad para líneas de soplado, termoformado, espesores, galga y empaque final.
                    </p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-700/60 flex items-center justify-between text-xs font-bold text-amber-400 group-hover:text-amber-300 relative z-10">
                <span>Ver Estado del Módulo</span>
                <div class="w-9 h-9 rounded-full bg-amber-500/20 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-all">
                    <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </div>
            </div>
        </a>

        <!-- 3. TARJETA LAMINADO (EN DESARROLLO) -->
        <a href="{{ route('laminado') }}" 
           class="group relative bg-gray-800/90 backdrop-blur-xl p-8 rounded-3xl shadow-xl hover:shadow-2xl border-2 border-gray-700/60 hover:border-sky-400 transition-all duration-300 flex flex-col justify-between overflow-hidden transform hover:-translate-y-2 cursor-pointer">
            <div class="absolute -top-12 -right-12 w-36 h-36 bg-sky-500/20 rounded-full blur-3xl group-hover:bg-sky-500/30 transition-all"></div>
            
            <div class="space-y-6 relative z-10">
                <div class="flex items-center justify-between">
                    <div class="w-16 h-16 bg-sky-600 text-white rounded-2xl flex items-center justify-center text-3xl shadow-lg group-hover:scale-110 transition-transform">
                        📜
                    </div>
                    <span class="px-3.5 py-1.5 bg-sky-500/20 text-sky-300 text-[11px] font-black uppercase tracking-wider rounded-full border border-sky-500/40">
                        Próximamente
                    </span>
                </div>

                <div class="space-y-3">
                    <h2 class="text-2xl font-black text-white group-hover:text-sky-400 transition-colors">
                        Laminado
                    </h2>
                    <p class="text-xs text-gray-400 leading-relaxed font-medium">
                        Monitoreo de procesos de extrusión, tensión de bobinas y laminación continua de lámina PET.
                    </p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-700/60 flex items-center justify-between text-xs font-bold text-sky-400 group-hover:text-sky-300 relative z-10">
                <span>Ver Estado del Módulo</span>
                <div class="w-9 h-9 rounded-full bg-sky-500/20 flex items-center justify-center group-hover:bg-sky-600 group-hover:text-white transition-all">
                    <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </div>
            </div>
        </a>

    </div>

</div>
@endsection
