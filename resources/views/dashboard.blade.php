@extends('layouts.app')

@section('content')
<div class="space-y-8">

    <!-- TARJETAS SUPERIORES FLOTANTES -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Tarjeta 1 -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="absolute -top-3 -right-3 w-16 h-16 bg-fenix/10 rounded-full"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold">Lotes Hoy</p>
                    <h4 class="text-2xl font-bold text-gray-800 mt-1">24</h4>
                </div>
                <div class="w-12 h-12 bg-fenix text-white rounded-xl flex items-center justify-center shadow-md">
                    📦
                </div>
            </div>
            <p class="text-xs text-green-600 font-medium mt-4">+12% que ayer</p>
        </div>

        <!-- Tarjeta 2 -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold">Inspecciones OK</p>
                    <h4 class="text-2xl font-bold text-gray-800 mt-1">1,420</h4>
                </div>
                <div class="w-12 h-12 bg-gray-900 text-white rounded-xl flex items-center justify-center shadow-md">
                    ✅
                </div>
            </div>
            <p class="text-xs text-green-600 font-medium mt-4">98.5% conformidad</p>
        </div>

        <!-- Tarjeta 3 (Destacada en tu verde principal) -->
        <div class="bg-fenix text-white p-6 rounded-2xl shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-green-100 uppercase font-semibold">Eficiencia Planta</p>
                    <h4 class="text-2xl font-bold mt-1">94.2%</h4>
                </div>
                <div class="w-12 h-12 bg-white/20 text-white rounded-xl flex items-center justify-center backdrop-blur-sm">
                    ⚡
                </div>
            </div>
            <p class="text-xs text-green-100 mt-4">Meta cumplida hoy</p>
        </div>

        <!-- Tarjeta 4 -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-semibold">Alertas / PNC</p>
                    <h4 class="text-2xl font-bold text-red-500 mt-1">2</h4>
                </div>
                <div class="w-12 h-12 bg-red-500 text-white rounded-xl flex items-center justify-center shadow-md">
                    ⚠️
                </div>
            </div>
            <p class="text-xs text-red-500 font-medium mt-4">Requiere revisión</p>
        </div>

    </div>

    <!-- SECCIÓN DE GRÁFICOS / PANELES PRINCIPALES -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Panel Gráfico Principal -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Inspecciones de Calidad por Día</h3>
            <p class="text-xs text-gray-400 mb-6">Rendimiento semanal de preformas</p>
            
            <!-- Contenedor simulado del gráfico con toque verde -->
            <div class="h-64 bg-fenix/5 rounded-xl border border-dashed border-fenix/30 flex items-center justify-center text-fenix font-medium">
                [ Gráfico de Inspecciones (Chart.js / ApexCharts) ]
            </div>
        </div>

        <!-- Panel Secundario -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Lotes Recientes Liberados</h3>
            <p class="text-xs text-gray-400 mb-6">Últimos registros en línea</p>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                    <span class="text-sm font-semibold text-gray-700">Lote #LT-2026-088</span>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">LIBERADO</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                    <span class="text-sm font-semibold text-gray-700">Lote #LT-2026-089</span>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">LIBERADO</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                    <span class="text-sm font-semibold text-gray-700">Lote #LT-2026-090</span>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">CUARENTENA</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection