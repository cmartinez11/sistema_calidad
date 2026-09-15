@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 pb-12">

    <!-- Header y Filtro Global de Tiempo -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-fenix/10 text-fenix rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Dashboard Analítico de Calidad</h1>
                    <p class="text-xs text-gray-500 font-medium">Indicadores gerenciales, ranking de productos defectuosos y gráfico de Pareto</p>
                </div>
            </div>
        </div>

        <!-- Formulario del Filtro Global de Tiempo -->
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-3 bg-gray-50 p-2.5 rounded-2xl border border-gray-200">
            <!-- Periodo -->
            <div>
                <select id="periodo_select" name="periodo" 
                        class="px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-fenix transition-all">
                    <option value="este_mes" {{ $periodo == 'este_mes' ? 'selected' : '' }}>Este Mes</option>
                    <option value="mes_anterior" {{ $periodo == 'mes_anterior' ? 'selected' : '' }}>Mes Anterior</option>
                    <option value="ultimos_3_meses" {{ $periodo == 'ultimos_3_meses' ? 'selected' : '' }}>Últimos 3 Meses</option>
                    <option value="anio_actual" {{ $periodo == 'anio_actual' ? 'selected' : '' }}>Año Actual</option>
                    <option value="personalizado" {{ $periodo == 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                </select>
            </div>

            <!-- Rangos personalizados -->
            <div id="rango_custom_inputs" class="flex items-center space-x-2 {{ $periodo == 'personalizado' ? '' : 'hidden' }}">
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" 
                       class="px-2.5 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-800">
                <span class="text-xs text-gray-400 font-bold">-</span>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" 
                       class="px-2.5 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-800">
            </div>

            <!-- Botón Filtrar -->
            <button type="submit" 
                    class="px-4 py-2 bg-fenix hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow transition-all flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filtrar</span>
            </button>
        </form>
    </div>

    <!-- 1. SECCIÓN DE TARJETAS DE KPI PRINCIPALES -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- KPI 1: Cumplimiento del Plan -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Cumplimiento del Plan</p>
                    <h4 class="text-3xl font-black text-gray-900 mt-1">{{ $cumplimientoPorcentaje }}%</h4>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-700 rounded-xl flex items-center justify-center font-bold text-lg">
                    📈
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-medium">
                <span class="text-gray-500">{{ $inspeccionesEjecutadas }} / {{ $metaPlanificada }} ejec.</span>
                <span class="text-emerald-700 font-bold">Meta: 100%</span>
            </div>
            <div class="w-full bg-gray-100 h-2 rounded-full mt-2 overflow-hidden">
                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $cumplimientoPorcentaje }}%"></div>
            </div>
        </div>

        <!-- KPI 2: % Conforme vs No Conforme -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Conformidad Producción</p>
                    <h4 class="text-3xl font-black text-emerald-700 mt-1">{{ $pctConforme }}%</h4>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center font-bold text-lg">
                    ✅
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-medium">
                <span class="text-emerald-700 font-bold">{{ $conformesCount }} Conformes</span>
                <span class="text-rose-600 font-bold">{{ $noConformesCount }} No Conformes</span>
            </div>
            <div class="w-full bg-rose-200 h-2 rounded-full mt-2 overflow-hidden flex">
                <div class="bg-emerald-500 h-2" style="width: {{ $pctConforme }}%"></div>
                <div class="bg-rose-500 h-2" style="width: {{ $pctNoConforme }}%"></div>
            </div>
        </div>

        <!-- KPI 3: Estatus PNC Pendientes -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-amber-500 uppercase tracking-wider">PNC Pendientes</p>
                    <h4 class="text-3xl font-black text-amber-600 mt-1">{{ $pncPendientes }}</h4>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center font-bold text-lg">
                    ⚠️
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs">
                <span class="text-gray-500">Procesados: <b class="text-gray-800">{{ $pncProcesados }}</b></span>
                <span class="text-amber-700 font-bold">Retenidos: {{ $pncRetenidos }}</span>
            </div>
        </div>

        <!-- KPI 4: Acciones Vencidas -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-rose-500 uppercase tracking-wider">Acciones Vencidas</p>
                    <h4 class="text-3xl font-black text-rose-600 mt-1">{{ $pncAccionesVencidas }}</h4>
                </div>
                <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center font-bold text-lg">
                    🚨
                </div>
            </div>
            <p class="text-xs text-rose-600 font-bold mt-4">
                {{ $pncAccionesVencidas > 0 ? '¡Requiere atención inmediata!' : 'Sin acciones vencidas' }}
            </p>
        </div>
    </div>

    <!-- 2. NUEVA SECCIÓN: PRODUCTOS QUE MÁS FALLAN (TOP DEFECTUOSOS) -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center">
                    <span class="p-1.5 bg-rose-100 text-rose-700 rounded-lg mr-2 text-xs">🏆</span>
                    Top 5 Productos con Mayor Índice de Fallas y Rechazos
                </h3>
                <p class="text-xs text-gray-400">Ranking acumulado de productos con más observaciones, scrap y no conformidades en el periodo</p>
            </div>
            <span class="text-xs font-bold text-rose-600 bg-rose-50 px-3 py-1 rounded-full border border-rose-200">
                Críticos del Periodo
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
            <!-- Gráfico de Ranking de Productos -->
            <div class="lg:col-span-2 h-64 relative">
                <canvas id="chartTopProductosDefectuosos"></canvas>
            </div>

            <!-- Resumen numérico -->
            <div class="space-y-3 bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Detalle del Top Defectuosos</h4>
                <div class="space-y-2">
                    @foreach($topProductosDefectuosos as $idx => $prod)
                        <div class="flex items-center justify-between text-xs p-2 bg-white rounded-lg border border-gray-200/80 shadow-2xs">
                            <span class="font-bold text-gray-800 truncate max-w-[200px]" title="{{ $prod['producto'] }}">
                                {{ $idx + 1 }}. {{ $prod['producto'] }}
                            </span>
                            <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-mono font-bold rounded-md">
                                {{ $prod['total'] }} fallas
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- 3. SECCIÓN DE GRÁFICOS: CUMPLIMIENTO Y CONFORMIDAD -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Gráfico 1: Cumplimiento Plan (Planificado vs Real) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Cumplimiento del Plan de Inspecciones</h3>
                    <p class="text-xs text-gray-400">Avance comparativo diario de la semana actual en curso: Planificado vs. Ejecutado Real</p>
                </div>
                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold border border-emerald-200">Semana Actual</span>
            </div>
            <div class="h-64 relative">
                <canvas id="chartCumplimientoPlan"></canvas>
            </div>
        </div>

        <!-- Gráfico 2: Donut Conforme vs No Conforme -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
            <div>
                <h3 class="text-base font-bold text-gray-900">Proporción Conforme vs. No Conforme</h3>
                <p class="text-xs text-gray-400">Distribución porcentual de calidad en el periodo</p>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="chartConformidad"></canvas>
            </div>
        </div>
    </div>

    <!-- 4. SECCIÓN DE GRÁFICOS: RECHAZO POR MÁQUINA Y CAVIDAD -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfico 3: Rechazo por Máquina -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
            <div>
                <h3 class="text-base font-bold text-gray-900">Incidencias de Rechazo por Máquina y Molde</h3>
                <p class="text-xs text-gray-400">Top equipos y matrices con mayor nivel de no conformidad</p>
            </div>
            <div class="h-64 relative">
                <canvas id="chartRechazoMaquinaria"></canvas>
            </div>
        </div>

        <!-- Gráfico 4: Rechazo por Cavidad Específica -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
            <div>
                <h3 class="text-base font-bold text-gray-900">Rechazo por Cavidad Específica</h3>
                <p class="text-xs text-gray-400">Frecuencia de defectos registrada por número de cavidad</p>
            </div>
            <div class="h-64 relative">
                <canvas id="chartRechazoCavidades"></canvas>
            </div>
        </div>
    </div>

    <!-- 5. SECCIÓN DE GRÁFICOS: PARETO DE DEFECTOS Y DESVIACIONES -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Gráfico 5: Diagrama de Pareto (80/20) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Diagrama de Pareto de Defectos Principales</h3>
                    <p class="text-xs text-gray-400">Frecuencia de defectos principales y porcentaje acumulado (80/20)</p>
                </div>
                <span class="px-3 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-bold border border-amber-200">Regla 80/20</span>
            </div>
            <div class="h-72 relative">
                <canvas id="chartParetoDefectos"></canvas>
            </div>
        </div>

        <!-- Tabla: Desviación de Gramaje y Control -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
            <div>
                <h3 class="text-base font-bold text-gray-900">Desviaciones de Gramaje</h3>
                <p class="text-xs text-gray-400">Últimas desviaciones críticas en peso de preformas</p>
            </div>

            <div class="space-y-3 overflow-y-auto max-h-64">
                @forelse($desviacionesGramaje as $desv)
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-gray-800 block">{{ $desv->producto->nombre ?? 'Preforma' }}</span>
                            <span class="text-[10px] text-gray-400 font-mono">Código: {{ $desv->codigo_inspeccion }}</span>
                        </div>
                        <div class="text-right font-mono">
                            <span class="block font-bold text-rose-600">{{ $desv->peso_min }}g - {{ $desv->peso_max }}g</span>
                            <span class="text-[10px] text-gray-400">Nom: {{ $desv->producto->parametroPreforma->peso_nominal ?? '-' }}g</span>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-400 text-xs">
                        No se registraron desviaciones críticas de gramaje en el periodo seleccionado.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

<!-- CHART.JS INTEGRACIÓN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // Selector de Periodo Personalizado
    const periodoSelect = document.getElementById('periodo_select');
    const customInputs = document.getElementById('rango_custom_inputs');

    if (periodoSelect) {
        periodoSelect.addEventListener('change', function () {
            if (this.value === 'personalizado') {
                customInputs.classList.remove('hidden');
            } else {
                customInputs.classList.add('hidden');
            }
        });
    }

    // 0. Gráfico Top Productos Defectuosos
    const topProdData = @json($topProductosDefectuosos);
    const ctxTopProd = document.getElementById('chartTopProductosDefectuosos').getContext('2d');
    new Chart(ctxTopProd, {
        type: 'bar',
        data: {
            labels: topProdData.map(d => d.producto),
            datasets: [{
                label: 'Volumen de Fallas / Scrap',
                data: topProdData.map(d => d.total),
                backgroundColor: ['#e11d48', '#f43f5e', '#fb7185', '#fda4af', '#fecdd3'],
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });

    // 1. Gráfico de Cumplimiento del Plan
    const ctxPlan = document.getElementById('chartCumplimientoPlan').getContext('2d');
    new Chart(ctxPlan, {
        type: 'line',
        data: {
            labels: @json($diasSemana),
            datasets: [
                {
                    label: 'Planificado (Meta)',
                    data: @json($planDiario),
                    borderColor: '#9ca3af',
                    borderDash: [5, 5],
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 3
                },
                {
                    label: 'Ejecutado (Real)',
                    data: @json($realDiario),
                    borderColor: '#30732B',
                    backgroundColor: 'rgba(48, 115, 43, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#30732B'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: 'bold' } }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });

    // 2. Gráfico Donut de Conformidad
    const ctxConf = document.getElementById('chartConformidad').getContext('2d');
    new Chart(ctxConf, {
        type: 'doughnut',
        data: {
            labels: ['Conforme (%)', 'No Conforme (%)'],
            datasets: [{
                data: [{{ $pctConforme }}, {{ $pctNoConforme }}],
                backgroundColor: ['#10b981', '#f43f5e'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 3. Gráfico de Rechazo por Máquina
    const ctxMaq = document.getElementById('chartRechazoMaquinaria').getContext('2d');
    const maquinaData = @json($rechazosPorMaquina);
    new Chart(ctxMaq, {
        type: 'bar',
        data: {
            labels: maquinaData.length ? maquinaData.map(d => d.maquina) : ['M01', 'M02', 'M03'],
            datasets: [{
                label: 'Rechazos / Observados',
                data: maquinaData.length ? maquinaData.map(d => d.total) : [4, 8, 2],
                backgroundColor: '#3b82f6',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // 4. Gráfico de Rechazo por Cavidad Específica
    const ctxCav = document.getElementById('chartRechazoCavidades').getContext('2d');
    const cavidadData = @json($rechazosPorCavidad);
    new Chart(ctxCav, {
        type: 'bar',
        data: {
            labels: cavidadData.length ? cavidadData.map(d => d.cavidad) : ['Cav. 4', 'Cav. 8', 'Cav. 12', 'Cav. 16'],
            datasets: [{
                label: 'Incidencias',
                data: cavidadData.length ? cavidadData.map(d => d.total) : [12, 9, 6, 3],
                backgroundColor: '#f59e0b',
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });

    // 5. Gráfico de Pareto de Defectos Principales (Doble Eje Y)
    const ctxPareto = document.getElementById('chartParetoDefectos').getContext('2d');
    new Chart(ctxPareto, {
        type: 'bar',
        data: {
            labels: @json($paretoLabels),
            datasets: [
                {
                    type: 'bar',
                    label: 'Frecuencia de Defectos',
                    data: @json($paretoCantidades),
                    backgroundColor: '#e11d48',
                    borderRadius: 8,
                    yAxisID: 'y'
                },
                {
                    type: 'line',
                    label: '% Acumulado (Pareto)',
                    data: @json($paretoAcumulado),
                    borderColor: '#f59e0b',
                    backgroundColor: 'transparent',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#f59e0b',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    title: { display: true, text: 'Cantidad de Defectos' }
                },
                y1: {
                    beginAtZero: true,
                    max: 100,
                    position: 'right',
                    title: { display: true, text: '% Acumulado' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

});
</script>
@endsection