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

            <div id="rango_custom_inputs" class="flex items-center space-x-2 {{ $periodo == 'personalizado' ? '' : 'hidden' }}">
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" 
                       class="px-2.5 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-800">
                <span class="text-xs text-gray-400 font-bold">-</span>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" 
                       class="px-2.5 py-1.5 bg-white border border-gray-200 rounded-xl text-xs font-medium text-gray-800">
            </div>

            <button type="submit" 
                    class="px-4 py-2 bg-fenix hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow transition-all flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filtrar</span>
            </button>
        </form>
    </div>

    <!-- 1. SECCIÓN DE TARJETAS DE KPI PRINCIPALES -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Tarjeta 1: Conformes vs Observados -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Inspecciones: Conformes vs Obs.</p>
                    <h4 class="text-3xl font-black text-gray-900 mt-1">{{ $conformesCount }} <span class="text-lg font-normal text-gray-400">/ {{ $observadosCount }}</span></h4>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center font-bold text-lg">⚖️</div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-medium">
                <span class="text-emerald-700 font-bold">🟢 Conformes: {{ $conformesCount }}</span>
                <span class="text-amber-600 font-bold">🟠 Observados: {{ $observadosCount }}</span>
            </div>
            <div class="w-full bg-gray-100 h-2 rounded-full mt-2 overflow-hidden flex">
                @php 
                    $total1 = max(1, $conformesCount + $observadosCount);
                    $pctConf1 = ($conformesCount / $total1) * 100;
                @endphp
                <div class="bg-emerald-500 h-2" style="width: {{ $pctConf1 }}%"></div>
                <div class="bg-amber-500 h-2" style="width: {{ 100 - $pctConf1 }}%"></div>
            </div>
        </div>

        <!-- Tarjeta 2: Conformes vs PNC -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Inspecciones: Conformes vs PNC</p>
                    <h4 class="text-3xl font-black text-gray-900 mt-1">{{ $conformesCount }} <span class="text-lg font-normal text-gray-400">/ {{ $pncEvaluadosCount }}</span></h4>
                </div>
                <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center font-bold text-lg">🛡️</div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-medium">
                <span class="text-emerald-700 font-bold">🟢 Conformes: {{ $conformesCount }}</span>
                <span class="text-rose-600 font-bold">🔴 PNC: {{ $pncEvaluadosCount }}</span>
            </div>
            <div class="w-full bg-gray-100 h-2 rounded-full mt-2 overflow-hidden flex">
                @php 
                    $total2 = max(1, $conformesCount + $pncEvaluadosCount);
                    $pctConf2 = ($conformesCount / $total2) * 100;
                @endphp
                <div class="bg-emerald-500 h-2" style="width: {{ $pctConf2 }}%"></div>
                <div class="bg-rose-500 h-2" style="width: {{ 100 - $pctConf2 }}%"></div>
            </div>
        </div>

        <!-- Tarjeta 3: Alertas ATC Recientes -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-blue-500 uppercase tracking-wider">Alertas ATC Recientes</p>
                    <h4 class="text-3xl font-black text-blue-600 mt-1">{{ count($alertasAtc) }}</h4>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-bold text-lg">📢</div>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs font-medium">
                <span class="text-gray-500">Reclamos y Devoluciones</span>
                <a href="{{ route('atc.index') }}" class="text-blue-600 font-bold hover:underline">Ver todos →</a>
            </div>
        </div>
    </div>

    <!-- 2. PRODUCTOS QUE MÁS FALLAN -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-gray-900">Top 5 Productos con Mayor Índice de Fallas y Rechazos</h3>
                <p class="text-xs text-gray-400">Ranking acumulado de productos con más observaciones, scrap y no conformidades en el periodo</p>
            </div>
            <span class="text-xs font-bold text-rose-600 bg-rose-50 px-3 py-1 rounded-full border border-rose-200">Críticos del Periodo</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
            <div class="lg:col-span-2 h-64 relative">
                <canvas id="chartTopProductosDefectuosos"></canvas>
            </div>
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

    <!-- 3. GRÁFICOS: CUMPLIMIENTO Y ALERTAS ATC -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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

        <!-- SECCIÓN: ALERTAS DE RECLAMOS Y ATC -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Alertas de Reclamos y ATC</h3>
                    <p class="text-xs text-gray-400">Últimos registros de atención al cliente</p>
                </div>
                <a href="{{ route('atc.index') }}" class="text-xs font-bold text-fenix hover:underline flex items-center space-x-1">
                    <span>Ver Más</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="space-y-3 overflow-y-auto max-h-64 pr-1">
                @forelse($alertasAtc ?? [] as $atc)
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/80 flex items-center justify-between text-xs transition-all hover:bg-gray-100/80">
                        <div class="space-y-0.5">
                            <div class="flex items-center space-x-2">
                                <span class="font-mono font-bold text-fenix">{{ $atc->correlativo ?? 'ATC-000' }}</span>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-800 border border-amber-200">
                                    {{ ucfirst($atc->tipo ?? 'Reclamo') }}
                                </span>
                            </div>
                            <p class="font-semibold text-gray-800 truncate max-w-[190px]" title="{{ $atc->cliente_nombre ?? 'Cliente general' }}">
                                👤 {{ $atc->cliente_nombre ?? 'Cliente no especificado' }}
                            </p>
                            <p class="text-[11px] text-gray-500 font-mono">
                                Prod: {{ $atc->producto ?? 'N/A' }} (Cant: {{ $atc->cantidad ?? 0 }})
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="block text-[10px] text-gray-400 font-mono">{{ \Carbon\Carbon::parse($atc->created_at)->format('d/m/Y') }}</span>
                            <a href="{{ route('atc.index', ['search' => $atc->correlativo]) }}" class="mt-1 inline-block px-2 py-1 bg-white border border-gray-300 rounded-lg text-[10px] font-bold text-gray-700 hover:bg-gray-50 shadow-2xs">
                                Revisar
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400 text-xs">
                        <span class="text-2xl block mb-1">📭</span>
                        No hay registros ni alertas de reclamos ATC recientes.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 4. RECHAZO POR MÁQUINA Y DISPONIBILIDAD -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
            <div>
                <h3 class="text-base font-bold text-gray-900">Incidencias de Rechazo por Máquina y Molde</h3>
                <p class="text-xs text-gray-400">Top equipos y matrices con mayor nivel de no conformidad</p>
            </div>
            <div class="h-64 relative">
                <canvas id="chartRechazoMaquinaria"></canvas>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Estado Actual y Disponibilidad de Máquinas</h3>
                    <p class="text-xs text-gray-400">Consolidado en tiempo real del parque industrial ({{ $maquinasTotal }} equipos)</p>
                </div>
                <a href="{{ route('maquinas.index') }}" class="text-xs font-bold text-fenix hover:underline flex items-center space-x-1">
                    <span>Gestionar Máquinas</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="bg-emerald-50/80 p-3 rounded-xl border border-emerald-100 text-center">
                    <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Operativas</span>
                    <span class="text-2xl font-black text-emerald-800">{{ $maquinasActivasCount }}</span>
                </div>
                <div class="bg-amber-50/80 p-3 rounded-xl border border-amber-100 text-center">
                    <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">Mantenimiento</span>
                    <span class="text-2xl font-black text-amber-800">{{ $maquinasMantenimientoCount }}</span>
                </div>
                <div class="bg-rose-50/80 p-3 rounded-xl border border-rose-100 text-center">
                    <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Malogradas</span>
                    <span class="text-2xl font-black text-rose-800">{{ $maquinasInactivasCount }}</span>
                </div>
            </div>

            <div class="space-y-2 pt-1">
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Máquinas Fuera de Servicio Actualmente</h4>
                <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                    @forelse($maquinasDetenidas as $maqDet)
                        @php
                            $lastHist = $maqDet->historiales->first();
                            $estadoMap = [
                                'mantenimiento' => ['label' => 'Mantenimiento', 'bg' => 'bg-amber-100 text-amber-800 border-amber-200'],
                                'sin_programacion' => ['label' => 'Sin Programación', 'bg' => 'bg-purple-100 text-purple-800 border-purple-200'],
                                'sin_resina' => ['label' => 'Sin Resina', 'bg' => 'bg-orange-100 text-orange-800 border-orange-200'],
                                'sin_personal' => ['label' => 'Sin Personal', 'bg' => 'bg-blue-100 text-blue-800 border-blue-200'],
                                'inactivo' => ['label' => 'Inactiva / Malograda', 'bg' => 'bg-rose-100 text-rose-800 border-rose-200'],
                            ];
                            $info = $estadoMap[$maqDet->estado] ?? ['label' => strtoupper($maqDet->estado), 'bg' => 'bg-gray-100 text-gray-800 border-gray-200'];
                        @endphp
                        <div class="p-2.5 bg-gray-50 rounded-xl border border-gray-200/80 flex items-start justify-between text-xs">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-gray-900 font-mono">{{ $maqDet->codigo }}</span>
                                    <span class="text-gray-600 truncate max-w-[130px]">{{ $maqDet->nombre }}</span>
                                </div>
                                @if($lastHist && $lastHist->motivo_observacion)
                                    <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-1">
                                        <span class="font-semibold text-gray-700">Obs:</span> {{ $lastHist->motivo_observacion }}
                                    </p>
                                @endif
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md border shrink-0 {{ $info['bg'] }}">
                                {{ $info['label'] }}
                            </span>
                        </div>
                    @empty
                        <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100 text-center text-xs text-emerald-800 font-medium">
                            ✅ Todas las máquinas del parque industrial están operativas y en producción.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- 5. PARETO Y DESVIACIONES -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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

        <!-- 7. SECCIÓN: VARIACIONES DE DATOS -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
            <div>
                <h3 class="text-base font-bold text-gray-900">Variaciones de Datos</h3>
                <p class="text-xs text-gray-400">Últimas inspecciones con observaciones en peso, espesor o altura</p>
            </div>
            <div class="space-y-3 overflow-y-auto max-h-64">
                @forelse($desviacionesGramaje as $desv)
                    @php
                        $cavidadDefecto = \App\Models\InspeccionCavidad::where('codigo_inspeccion', $desv->codigo_inspeccion)
                            ->whereIn('estado', ['FUERA_DE_RANGO', 'OBSERVADO'])
                            ->first();
                    @endphp
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-gray-800 block">{{ $desv->producto->nombre ?? 'Preforma' }}</span>
                            <span class="text-[10px] text-gray-400 font-mono">Código: {{ $desv->codigo_inspeccion }}</span>
                        </div>
                        <div class="text-right">
                            <span class="block font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                {{ $cavidadDefecto->motivo_scrap ?? 'Fuera de Rango' }}
                            </span>
                            <span class="text-[10px] text-gray-500 font-mono mt-0.5 block">Máquina: {{ $desv->maquina->codigo ?? 'N/A' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-400 text-xs">
                        No se registraron variaciones o defectos en el periodo seleccionado.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 6. SECCIÓN: DISPONIBILIDAD DE MOLDES POR CAVIDADES ANULADAS -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4" 
         x-data="{ activeModalMolde: null, cavidadesLista: [] }">
        
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900">Disponibilidad de Moldes por Cavidades Activas</h3>
                <p class="text-xs text-gray-400">Consolidado del estado de moldes y cavidades operativas basado en el día consultado</p>
            </div>

            <!-- Filtro independiente de un solo día para los moldes -->
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2 bg-gray-50 p-2 rounded-xl border border-gray-200">
                <input type="hidden" name="periodo" value="{{ $periodo }}">
                @if($periodo == 'personalizado')
                    <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
                    <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
                @endif

                <span class="text-[11px] font-bold text-gray-500 uppercase px-1">Seleccionar Día:</span>
                <input type="date" name="fecha_molde" value="{{ $fechaMoldeSeleccionada ?? now('America/Lima')->toDateString() }}" 
                       class="px-2.5 py-1 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-800">
                <button type="submit" 
                        class="px-3 py-1.5 bg-fenix hover:bg-emerald-800 text-white rounded-lg text-xs font-bold shadow transition-all cursor-pointer">
                    Consultar Día
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-700 font-bold uppercase tracking-wider text-center">
                    <tr>
                        <th class="px-4 py-3 text-left">Molde</th>
                        <th class="px-4 py-3">Cav. Totales</th>
                        <th class="px-4 py-3">Cav. Anuladas</th>
                        <th class="px-4 py-3">Activas</th>
                        <th class="px-4 py-3" colspan="2">Disponibilidad del Molde</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-center font-medium">
                    @forelse($moldesDisponibilidad as $molde)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-4 py-3 text-left font-bold text-gray-800">{{ $molde['nombre_molde'] }}</td>
                            <td class="px-4 py-3 font-mono text-gray-600">{{ $molde['cav_totales'] }}</td>
                            
                            <td class="px-4 py-3 font-mono text-rose-600 font-bold">
                                @if($molde['cav_anuladas'] > 0)
                                    <div class="inline-flex items-center space-x-1.5 justify-center">
                                        <span>{{ $molde['cav_anuladas'] }}</span>
                                        <button type="button" 
                                                @click="activeModalMolde = '{{ $molde['nombre_molde'] }}'; cavidadesLista = @js($molde['cavidades_anuladas_lista']);"
                                                class="px-2 py-0.5 bg-rose-100 hover:bg-rose-200 text-rose-800 rounded text-[10px] font-bold transition-all shadow-2xs inline-flex items-center space-x-1 cursor-pointer">
                                            <span>🔍 Ver</span>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 font-mono text-emerald-700 font-bold">{{ $molde['activas'] }}</td>
                            
                            <td class="w-10 py-3 text-right">
                                @if($molde['disponibilidad'] >= 90)
                                    <span class="inline-block w-3 h-3 rounded-full bg-emerald-500 shadow-sm" title="Óptimo (>=90%)"></span>
                                @elseif($molde['disponibilidad'] >= 80)
                                    <span class="inline-block w-3 h-3 rounded-full bg-amber-400 shadow-sm" title="Regular (80-89%)"></span>
                                @else
                                    <span class="inline-block w-3 h-3 rounded-full bg-rose-500 shadow-sm" title="Crítico (<80%)"></span>
                                @endif
                            </td>
                            
                            <td class="px-4 py-3 text-left font-mono font-bold text-gray-800">
                                {{ $molde['disponibilidad'] }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">No hay moldes registrados o con inspecciones en el periodo seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- VENTANA MODAL PARA LISTAR LAS CAVIDADES ANULADAS -->
        <div x-show="activeModalMolde !== null" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden p-6 space-y-4 transform transition-all">
                
                <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto text-xl shadow-inner">
                    🚫
                </div>

                <div class="text-center">
                    <h3 class="font-bold text-base text-gray-800" x-text="activeModalMolde"></h3>
                    <p class="text-xs text-gray-400 mt-0.5">Listado de cavidades anuladas y sus motivos</p>
                </div>

                <div class="max-h-60 overflow-y-auto space-y-2 py-2 px-1">
                    <template x-for="cav in cavidadesLista" :key="cav.etiqueta">
                        <div class="p-2.5 bg-rose-50/60 border border-rose-200/80 rounded-xl flex items-start space-x-2 text-left shadow-2xs">
                            <span class="px-2 py-0.5 bg-rose-600 text-white font-mono text-xs font-bold rounded-md shrink-0" x-text="cav.etiqueta"></span>
                            <span class="text-xs text-rose-900 font-medium self-center break-all" x-text="cav.motivo"></span>
                        </div>
                    </template>
                </div>

                <div class="pt-2">
                    <button type="button" @click="activeModalMolde = null; cavidadesLista = []"
                            class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all cursor-pointer">
                        Cerrar Ventana
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- CHART.JS INTEGRACIÓN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
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

    const ctxPlan = document.getElementById('chartCumplimientoPlan').getContext('2d');
    new Chart(ctxPlan, {
        type: 'bar',
        data: {
            labels: @json($diasSemana),
            datasets: [
                {
                    label: 'Ejecutado (Real)',
                    data: @json($realDiario),
                    backgroundColor: '#30732B',
                    borderRadius: 6,
                    borderSkipped: false
                },
                {
                    label: 'Pendiente (Brecha)',
                    data: @json($pendienteDiario),
                    backgroundColor: 'rgba(209, 213, 219, 0.65)',
                    borderRadius: 6,
                    borderSkipped: false
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
                    intersect: false,
                    callbacks: {
                        footer: function(tooltipItems) {
                            let total = 0;
                            tooltipItems.forEach(function(tooltipItem) {
                                total += tooltipItem.parsed.y;
                            });
                            return 'Tope Meta Diario: ' + total + ' insp.';
                        }
                    }
                }
            },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11, weight: 'bold' } } },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'N° Inspecciones' } }
            }
        }
    });

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
                y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Cantidad de Defectos' } },
                y1: { beginAtZero: true, max: 100, position: 'right', title: { display: true, text: '% Acumulado' }, grid: { drawOnChartArea: false } }
            }
        }
    });
});
</script>
@endsection