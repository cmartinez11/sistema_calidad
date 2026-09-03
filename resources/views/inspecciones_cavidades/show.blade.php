@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- BARRA SUPERIOR DE ACCIONES (OCULTA EN IMPRESIÓN) -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 print:hidden">
        <a href="{{ route('inspecciones-cavidades.index') }}" 
           class="inline-flex items-center space-x-2 text-sm font-semibold text-gray-600 hover:text-fenix transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Volver al Historial</span>
        </a>

        <div class="flex items-center space-x-3">
            <a href="{{ route('inspecciones-cavidades.create', ['producto_id' => $producto->id]) }}" 
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Inspección</span>
            </a>

            <a href="{{ route('inspecciones-cavidades.pdf', ['codigo' => $codigo]) }}" target="_blank"
               class="px-5 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-sm font-semibold shadow-md transition-all flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar PDF Profesional</span>
            </a>

            <button onclick="window.print()" 
                    class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-semibold transition-all flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir Pantalla</span>
            </button>
        </div>
    </div>

    <!-- DOCUMENTO DE REPORTE METROLÓGICO (ESTILO HOJA TÉCNICA A4) -->
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200 print:shadow-none print:border-none print:p-0 print:m-0 space-y-6">
        
        <!-- CABECERA DEL INFORME -->
        <div class="flex items-center justify-between border-b-2 border-gray-800 pb-4">
            <div class="flex items-center space-x-4">
                <div class="bg-fenix text-white p-3 rounded-xl font-black text-xl shadow-sm">GF</div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight uppercase">Grupo Fénix - Control de Calidad</h1>
                    <p class="text-xs text-gray-500 font-semibold uppercase">Reporte Metrológico de Auditoría de Pesos por Cavidad</p>
                </div>
            </div>
            <div class="text-right">
                <span class="bg-emerald-100 text-emerald-900 border border-emerald-300 font-mono font-bold text-sm px-3 py-1.5 rounded-lg inline-block">
                    {{ $codigo }}
                </span>
                <p class="text-[11px] text-gray-400 font-mono mt-1">
                    {{ \Carbon\Carbon::parse($header->created_at)->format('d/m/Y h:i:s A') }}
                </p>
            </div>
        </div>

        <!-- DATOS DE CONTEXTO TÉCNICO -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200 text-xs">
            <div>
                <span class="text-gray-400 font-semibold uppercase block text-[10px]">Código Producto</span>
                <span class="font-bold font-mono text-gray-900 text-sm">{{ $producto->codigo }}</span>
            </div>
            <div>
                <span class="text-gray-400 font-semibold uppercase block text-[10px]">Nombre Producto</span>
                <span class="font-bold text-gray-900">{{ $producto->nombre }}</span>
            </div>
            <div>
                <span class="text-gray-400 font-semibold uppercase block text-[10px]">Inyectora / Máquina</span>
                <span class="font-bold text-gray-900">{{ $header->maquina->codigo ?? 'N/A' }} {{ $header->maquina ? '('.$header->maquina->nombre.')' : '' }}</span>
            </div>

            <div>
                <span class="text-gray-400 font-semibold uppercase block text-[10px]">Resina</span>
                <span class="font-bold text-gray-900">
                    @if(isset($resinaObj) && $resinaObj)
                        {{ $resinaObj->nombre ?? $resinaObj->codigo }}
                    @elseif(isset($calidadResumen) && $calidadResumen->resina)
                        {{ $calidadResumen->resina->nombre ?? $calidadResumen->resina->codigo }}
                    @elseif(isset($header->resina) && $header->resina)
                        {{ $header->resina->nombre ?? $header->resina->codigo }}
                    @else
                        N/A
                    @endif
                </span>
            </div>
            
            <div>
                <span class="text-gray-400 font-semibold uppercase block text-[10px]">Operario</span>
                <span class="font-bold text-gray-900">{{ $header->operario->nombre ?? 'N/A' }}</span>
            </div>

            <div>
                <span class="text-gray-400 font-semibold uppercase block text-[10px]">Encargado</span>
                <span class="font-bold text-gray-900">{{ $header->user->name ?? 'N/A' }}</span>
            </div>

            <div>
                <span class="text-gray-400 font-semibold uppercase block text-[10px]">Molde</span>
                <span class="font-bold text-gray-900">{{ $header->molde->nombre ?? 'N/A' }}</span>
            </div>

            <div>
                <span class="text-gray-400 font-semibold uppercase block text-[10px]">Turno</span>
                <span class="font-bold text-gray-900">{{ $header->turno->nombre ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- RESUMEN ESTADÍSTICO DE PESOS -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                <span class="text-[10px] text-gray-500 font-semibold uppercase block">Tolerancia</span>
                <span class="text-xs font-mono font-bold text-gray-800">
                    {{ $param && $param->peso_min > 0 ? $param->peso_min.'g - '.$param->peso_max.'g' : 'Sin límites' }}
                </span>
                <span class="text-[10px] text-gray-400 block mt-0.5">Nominal: {{ $param->peso_nominal ?? $producto->peso_unitario ?? '-' }}g</span>
            </div>

            <div class="bg-green-50 p-3 rounded-xl border border-green-200">
                <span class="text-[10px] text-green-700 font-semibold uppercase block">Conformes</span>
                <span class="text-base font-mono font-bold text-green-800">{{ $conformesCount }} / {{ $totalCavidades }}</span>
                <span class="text-[10px] text-green-600 block mt-0.5">{{ number_format(($conformesCount / max($totalCavidades,1))*100, 1) }}% cumplimiento</span>
            </div>

            <div class="bg-red-50 p-3 rounded-xl border border-red-200">
                <span class="text-[10px] text-red-700 font-semibold uppercase block">Fuera de Rango</span>
                <span class="text-base font-mono font-bold text-red-800">{{ $fueraDeRangoCount }}</span>
                <span class="text-[10px] text-red-600 block mt-0.5">Defectos detectados</span>
            </div> 

            <div class="bg-orange-50 p-3 rounded-xl border border-orange-200">
                <span class="text-[10px] text-orange-700 font-semibold uppercase block">Observados</span>
                <span class="text-base font-mono font-bold text-orange-800">{{ $observadoCount }}</span>
                <span class="text-[10px] text-orange-600 block mt-0.5">Observaciones</span>
            </div>

            <div class="bg-amber-50 p-3 rounded-xl border border-amber-200">
                <span class="text-[10px] text-amber-700 font-semibold uppercase block">Pasables</span>
                <span class="text-base font-mono font-bold text-amber-800">{{ $pasableCount ?? 0 }}</span>
                <span class="text-[10px] text-amber-600 block mt-0.5">Aceptadas con falla</span>
            </div>

            <div class="bg-blue-50 p-3 rounded-xl border border-blue-200">
                <span class="text-[10px] text-blue-700 font-semibold uppercase block">Peso Promedio</span>
                <span class="text-base font-mono font-bold text-blue-900">{{ $promedioPeso }} g</span>
                <span class="text-[10px] text-blue-600 block mt-0.5">Evaluación metrológica</span>
            </div>
        </div>

        <!-- TABLA COMPLETA DE DETALLE CAVIDAD POR CAVIDAD -->
        <div class="space-y-2">
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Detalle Cavidad por Cavidad</h3>
            
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-100 border-b border-gray-200 text-gray-700 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2.5 text-center w-24">N° Cavidad</th>
                            <th class="px-3 py-2.5 font-mono text-center w-28">Peso (g)</th>
                            <th class="px-3 py-2.5 text-center w-32">Espesor de Pared</th>
                            <th class="px-3 py-2.5 text-center w-32">Espesor de Fondo</th>
                            <th class="px-3 py-2.5 text-center w-32">Altura</th>
                            <th class="px-3 py-2.5 text-center w-32">Estado</th>
                            <th class="px-3 py-2.5 w-44">Motivo de Scrap / Defecto</th>
                            <th class="px-3 py-2.5">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($cavidades as $cav)
                            <tr class="{{ $cav->estado === 'ANULADO' ? 'bg-gray-100/90 text-gray-500 italic' : ($cav->estado === 'FUERA_DE_RANGO' ? 'bg-red-50 font-semibold text-red-900' : ($cav->estado === 'PASABLE' ? 'bg-amber-50/50' : 'hover:bg-gray-50/50')) }}">
                                <td class="px-3 py-2 text-center font-mono font-bold text-gray-800">
                                    C-{{ sprintf('%02d', $cav->cavidad_numero) }}
                                </td>
                                <td class="px-3 py-2 text-center font-mono font-bold {{ $cav->estado === 'FUERA_DE_RANGO' ? 'text-red-700 text-sm' : ($cav->estado === 'ANULADO' ? 'text-gray-400' : 'text-gray-900') }}">
                                    {{ $cav->estado === 'ANULADO' ? 0.00 : ($cav->peso_medido !== null ? number_format($cav->peso_medido, 2).' g' : '-') }}
                                </td>
                                <td class="px-3 py-2 text-center font-mono text-gray-500">
                                    {{ $cav->espesor_pared !== null ? number_format($cav->espesor_pared, 2).' g' : '-' }}
                                </td>
                                <td class="px-3 py-2 text-center font-mono text-gray-500">
                                    {{ $cav->espesor_fondo !== null ? number_format($cav->espesor_fondo, 2).' g' : '-' }}
                                </td>
                                <td class="px-3 py-2 text-center font-mono text-gray-500">
                                    {{ $param && $param->altura_min > 0 ? $param->altura_min.' - '.$param->altura_max.' g' : '-' }}
                                </td>
                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    @if($cav->estado === 'CONFORME')
                                        <span class="px-2.5 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold rounded-full border border-green-200">
                                            🟢 Conforme
                                        </span>
                                    @elseif($cav->estado === 'OBSERVADO')
                                        <span class="px-2.5 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-full border border-orange-200">
                                            🟠 Observado
                                        </span>
                                    @elseif($cav->estado === 'PASABLE')
                                        <span class="px-2.5 py-0.5 bg-amber-500 text-white text-[10px] font-bold rounded-full">
                                            ⚠️ Pasable
                                        </span>
                                    @elseif($cav->estado === 'ANULADO')
                                        <span class="px-2.5 py-0.5 bg-gray-200 text-gray-700 text-[10px] font-bold rounded-full border border-gray-300">
                                            ⚪ Anulado
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-full border border-red-200">
                                            🔴 Fuera de Rango
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if(in_array($cav->estado, ['FUERA_DE_RANGO', 'OBSERVADO', 'PASABLE']))
                                        <span class="font-bold text-red-700 bg-red-100/70 px-2 py-0.5 rounded-md border border-red-200 inline-block text-[11px]">
                                            {{ $cav->motivo_scrap ?? '-' }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-gray-700 text-xs">
                                    {{ $cav->observaciones ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECCIÓN DE ACCIONES CUANDO EXISTEN DEFECTOS O CAV. OBSERVADAS -->
        @php
            $tieneDefectos = $cavidades->whereIn('estado', ['FUERA_DE_RANGO', 'OBSERVADO'])->count() > 0;
            $estadoActualCalidad = $calidadResumen->estado_evaluacion ?? null;
        @endphp

        @if($tieneDefectos)
            <div x-data="{ showModalObservado: false }" class="mt-6 bg-gradient-to-r from-amber-50 via-orange-50 to-red-50 p-6 rounded-2xl border-2 border-amber-200 shadow-sm print:hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start space-x-3">
                        <div class="p-3 bg-amber-500 text-white rounded-xl shadow-md">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-gray-900">Se detectaron preformas fuera de rango u observadas</h4>
                            <p class="text-xs text-gray-600 mt-0.5">
                                @if(!$estadoActualCalidad)
                                    <span class="text-red-700 font-bold">🔒 Auditoría retenida preventivamente:</span> Selecciona el flujo de salida para registrar en el Resumen de Calidad.
                                @else
                                    Selecciona el flujo deseado para consolidar el estado en el Resumen de Calidad:
                                @endif
                            </p>
                            @if($estadoActualCalidad)
                                <div class="mt-2 inline-flex items-center space-x-2 text-xs">
                                    <span class="text-gray-500 font-semibold">Estado actual consolidado:</span>
                                    @if($estadoActualCalidad === 'CONFORME')
                                        <span class="px-2.5 py-0.5 font-bold rounded-full text-[11px] bg-green-100 text-green-700 border border-green-200">🟢 CONFORME</span>
                                    @elseif($estadoActualCalidad === 'PASABLE')
                                        <span class="px-2.5 py-0.5 font-bold rounded-full text-[11px] bg-amber-100 text-amber-800 border border-amber-300">⚠️ PASABLE</span>
                                    @elseif($estadoActualCalidad === 'OBSERVADO' || $estadoActualCalidad === 'OBSERVADO_PNC')
                                        <span class="px-2.5 py-0.5 font-bold rounded-full text-[11px] bg-orange-100 text-orange-800 border border-orange-300">🟠 OBSERVADO</span>
                                    @elseif($estadoActualCalidad === 'PNC')
                                        <span class="px-2.5 py-0.5 font-bold rounded-full text-[11px] bg-red-600 text-white shadow-sm">🔴 PNC</span>
                                    @else
                                        <span class="px-2.5 py-0.5 font-bold rounded-full text-[11px] bg-gray-200 text-gray-700">{{ $estadoActualCalidad }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="mt-2 inline-flex items-center space-x-2 text-xs">
                                    <span class="px-2.5 py-0.5 font-bold rounded-full text-[11px] bg-amber-200 text-amber-900 border border-amber-300 animate-pulse">
                                        ⏳ PENDIENTE DE CONSOLIDAR (BLOQUEO PREVENTIVO)
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- BOTÓN 1: Generar Inspección (Abre Modal de Justificación) -->
                        <button type="button" 
                                @click="showModalObservado = true"
                                class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>📋 Generar Inspección (OBSERVADO)</span>
                        </button>

                        <!-- BOTÓN 2: Generar PNC -->
                        <a href="{{ route('pnc.create', ['codigo_inspeccion' => $codigo]) }}" 
                           class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>⚠️ Generar PNC</span>
                        </a>
                    </div>
                </div>

                <!-- MODAL DE JUSTIFICACIÓN PARA CONSOLIDAR COMO OBSERVADO -->
                <div x-show="showModalObservado" 
                     x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    
                    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100"
                         @click.away="showModalObservado = false">
                        
                        <!-- Encabezado Modal -->
                        <div class="bg-gradient-to-r from-amber-600 to-orange-600 p-5 text-white flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-white/20 rounded-xl">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold">Justificación de Inspección Observada</h3>
                                    <p class="text-[11px] text-amber-100">Selecciona obligatoriamente el motivo de pase con observación</p>
                                </div>
                            </div>
                            <button type="button" @click="showModalObservado = false" class="text-white/80 hover:text-white text-xl font-bold">&times;</button>
                        </div>

                        <!-- Formulario Modal -->
                        <form action="{{ route('inspecciones-cavidades.consolidar-observado', $codigo) }}" method="POST" class="p-6 space-y-4">
                            @csrf

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Motivo de Observación (Obligatorio) *
                                </label>
                                <select name="motivo_observacion_id" required
                                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-900 focus:ring-amber-500 focus:border-amber-500">
                                    <option value="">-- Seleccionar Motivo de Observación --</option>
                                    @foreach($motivosObservacion as $mot)
                                        <option value="{{ $mot->id }}">
                                            {{ $mot->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Justificación o Detalle Adicional (Opcional)
                                </label>
                                <textarea name="motivo_observacion_texto" rows="3"
                                          placeholder="Ingresa notas adicionales o instrucciones técnicas registradas por jefatura..."
                                          class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-xs font-medium text-gray-900 focus:ring-amber-500 focus:border-amber-500"></textarea>
                            </div>

                            <div class="pt-3 flex justify-end space-x-3 border-t border-gray-100">
                                <button type="button" @click="showModalObservado = false"
                                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                                    Cancelar
                                </button>
                                <button type="submit"
                                        class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                                    Confirmar y Consolidar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- FIRMAS DE CONFORMIDAD (SOLO IMPRESIÓN) -->
        <div class="pt-12 grid grid-cols-2 gap-8 text-center text-xs text-gray-500 hidden print:grid">
            <div class="border-t border-gray-400 pt-2">
                <p class="font-bold text-gray-800">{{ $header->operario->nombre ?? 'Operador de Planta' }}</p>
                <p class="text-[10px] text-gray-400">Operario / Auditor Metrológico</p>
            </div>
            <div class="border-t border-gray-400 pt-2">
                <p class="font-bold text-gray-800">Jefatura de Control de Calidad</p>
                <p class="text-[10px] text-gray-400">Firma y Sello de Validación</p>
            </div>
        </div>

    </div>

</div>

<!-- ESTILOS ESPECÍFICOS DE IMPRESIÓN -->
<style>
@media print {
    body {
        background-color: white !important;
        font-size: 11px !important;
    }
    aside, header, nav, .print\:hidden {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>
@endsection