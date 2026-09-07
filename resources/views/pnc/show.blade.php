@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- ALERTAS FLASH -->
    @if(session('success'))
        <div class="p-4 bg-green-100 border-l-4 border-fenix text-fenix-dark rounded-r-xl shadow-sm flex items-center justify-between print:hidden">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-fenix" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
    @endif

    <!-- BOTONES DE ACCIÓN SUPERIORES -->
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ route('pnc.index') }}" 
           class="text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-all shadow-sm">
            ← Volver al Listado de PNCs
        </a>

        <div class="flex items-center space-x-3">
            @if($pnc->estado_pnc === 'PROCESADO')
                <span class="px-3 py-1.5 bg-emerald-100 text-emerald-800 text-xs font-extrabold rounded-xl border border-emerald-300 flex items-center space-x-1 shadow-2xs">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>ESTADO: PROCESADO</span>
                </span>
            @else
                <span class="px-3 py-1.5 bg-amber-100 text-amber-800 text-xs font-extrabold rounded-xl border border-amber-300 flex items-center space-x-1">
                    <span>ESTADO: {{ $pnc->estado_pnc }}</span>
                </span>

                @if(auth()->user()?->hasRole('Supervisor|Administrador'))
                    <form action="{{ route('pnc.procesar', $pnc->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Deseas procesar oficialmente este reporte de Producto No Conforme {{ $pnc->codigo_pnc }}?')">
                        @csrf
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow transition-all flex items-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Procesar PNC</span>
                        </button>
                    </form>
                @endif
            @endif

            <button onclick="window.print()" 
                    class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-xl text-xs font-bold shadow transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir Documento</span>
            </button>

            <a href="{{ route('pnc.pdf', $pnc->id) }}" target="_blank"
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Descargar PDF</span>
            </a>
        </div>
    </div>

    <!-- DOCUMENTO FORMATO METROLÓGICO FE-SIG-FOR-30-V -->
    <div class="bg-white rounded-2xl border-2 border-gray-800 overflow-hidden shadow-lg p-8 space-y-6">

        <!-- CABECERA INSTITUCIONAL METROLÓGICA -->
        <div class="border-2 border-gray-800 rounded-xl overflow-hidden">
            <div class="grid grid-cols-12 divide-x-2 divide-gray-800 text-center">
                <!-- Logotipo -->
                <div class="col-span-3 p-4 flex flex-col items-center justify-center bg-gray-50">
                    <span class="text-xl font-extrabold tracking-widest text-fenix-dark">GRUPO FÉNIX</span>
                    <span class="text-[9px] text-gray-500 font-bold uppercase">SISTEMA INTEGRADO DE GESTIÓN</span>
                </div>

                <!-- Título Oficial del Formato -->
                <div class="col-span-6 p-4 flex flex-col justify-center bg-white">
                    <h1 class="text-base font-extrabold text-gray-900 uppercase tracking-tight">REPORTE DE PRODUCTO NO CONFORME (PNC)</h1>
                    <span class="text-[11px] text-gray-500 font-medium">Control Metrológico y Aseguramiento de Calidad</span>
                </div>

                <!-- Código Documental -->
                <div class="col-span-3 p-3 bg-gray-50 text-left text-[10px] space-y-1 justify-center flex flex-col font-mono">
                    <div><strong>Código:</strong> FE-SIG-FOR-30-V</div>
                    <div><strong>Versión:</strong> 00</div>
                    <div><strong>Fecha:</strong> 9/11/2023</div>
                    <div class="pt-1 border-t border-gray-300 font-bold text-red-700">PNC N°: {{ $pnc->codigo_pnc }}</div>
                </div>
            </div>
        </div>

        <!-- 1. DATOS GENERALES -->
        <div class="border border-gray-800 rounded-xl p-4 space-y-3">
            <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider bg-gray-100 p-1.5 rounded border border-gray-300">
                1. Datos Generales de la Falla
            </h3>

            <div class="grid grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Fecha de Emisión</span>
                    <span class="font-mono font-bold text-gray-900">{{ $pnc->fecha ? $pnc->fecha->format('d/m/Y') : '-' }}</span>
                </div>

                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Código de Auditoría</span>
                    <span class="font-mono font-bold text-fenix-dark">{{ $pnc->codigo_inspeccion ?: 'Manual / Sin Auditoría' }}</span>
                </div>

                <div class="col-span-2">
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Producto Afectado</span>
                    <span class="font-bold text-gray-900">{{ $pnc->producto->codigo ?? '' }} - {{ $pnc->producto->nombre ?? 'N/A' }}</span>
                </div>

                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Lote de Producción</span>
                    <span class="font-mono font-bold text-gray-900">{{ $pnc->lote->codigo_lote ?? 'N/A' }}</span>
                </div>

                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Cantidad Afectada</span>
                    <span class="font-mono font-bold text-red-700 text-sm">
                        {{ number_format($pnc->cantidad, 2) }} {{ $pnc->unidad_medida }}
                        <!--@if(!empty($pnc->unidad_medida_2))
                            <span class="text-xs text-gray-500 font-semibold font-sans">({{ $pnc->unidad_medida_2 }})</span>
                        @endif-->
                    </span>
                </div>

                <div class="col-span-2">
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Cliente / Proveedor</span>
                    <span class="font-semibold text-gray-800">{{ $pnc->cliente_proveedor ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- 2. DESCRIPCIÓN DE LA NO CONFORMIDAD DETECTADA -->
        <div class="border border-gray-800 rounded-xl p-4 space-y-3">
            <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider bg-gray-100 p-1.5 rounded border border-gray-300">
                2. Descripción Detallada de la No Conformidad Detectada
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Columna Izquierda: Descripción -->
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-gray-700 uppercase tracking-wider block">Detalle del Problema / Falla Registrada:</span>
                    <div class="p-3 bg-red-50/50 rounded-lg border border-red-200 text-xs font-medium text-gray-900 min-h-[80px]">
                        {{ $pnc->descripcion_nc }}
                    </div>
                </div>

                <!-- Columna Derecha: Cavidades con Fallas Detectadas -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-gray-700 uppercase tracking-wider block">Cavidades con Fallas Detectadas:</span>
                        @if(isset($cavidadesDefectuosas) && $cavidadesDefectuosas->count() > 0)
                            <span class="px-2 py-0.5 bg-red-100 text-red-800 text-[10px] font-bold rounded-full border border-red-200 font-mono">
                                {{ $cavidadesDefectuosas->count() }} Afectadas
                            </span>
                        @endif
                    </div>

                    @if(isset($cavidadesDefectuosas) && $cavidadesDefectuosas->count() > 0)
                        <div class="border border-gray-200 rounded-lg overflow-hidden max-h-44 overflow-y-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-gray-100 text-[10px] font-bold text-gray-700 uppercase border-b border-gray-200">
                                    <tr>
                                        <th class="p-1.5 border-r border-gray-200">Cavidad</th>
                                        <th class="p-1.5 border-r border-gray-200">Motivo / Observación</th>
                                        <th class="p-1.5 text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-[11px]">
                                    @foreach($cavidadesDefectuosas as $cav)
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-1.5 font-mono font-bold text-gray-900 border-r border-gray-200">
                                                Cav. {{ sprintf('%02d', $cav->cavidad_numero) }}
                                            </td>
                                            <td class="p-1.5 text-gray-700 border-r border-gray-200">
                                                {{ $cav->motivo_scrap ?: ($cav->observaciones ?: '-') }}
                                            </td>
                                            <td class="p-1.5 text-center font-bold">
                                                @if($cav->estado === 'FUERA_DE_RANGO')
                                                    <span class="text-red-700">🔴 FUERA RANGO</span>
                                                @elseif($cav->estado === 'OBSERVADO')
                                                    <span class="text-orange-700">🟠 OBSERVADO</span>
                                                @elseif($cav->estado === 'PASABLE')
                                                    <span class="text-amber-700">⚠️ PASABLE</span>
                                                @elseif($cav->estado === 'ANULADO')
                                                    <span class="text-gray-500">⚪ ANULADO</span>
                                                @else
                                                    <span class="text-red-700">🔴 {{ $cav->estado }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-xs text-gray-500 text-center min-h-[80px] flex items-center justify-center">
                            No hay cavidades registradas con observaciones o auditoría manual.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. DÓNDE SE DETECTÓ Y DÓNDE SE ORIGINÓ -->
        <div class="grid grid-cols-2 gap-4">
            <!-- Dónde se detectó -->
            <div class="border border-gray-800 rounded-xl p-4 space-y-3">
                <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider bg-gray-100 p-1 rounded border border-gray-300">
                    3. Dónde se Detectó la Falla
                </h3>
                <div class="text-xs space-y-1">
                    <div><strong>Área:</strong> {{ $pnc->detectado_area }}</div>
                    <div><strong>Fecha:</strong> {{ $pnc->detectado_fecha ? $pnc->detectado_fecha->format('d/m/Y') : '-' }}</div>
                    <div><strong>Responsable:</strong> {{ $pnc->detectado_responsable }}</div>
                </div>

                <!-- Recuadro para Firma -->
                <div class="pt-4 text-center">
                    <div class="border-t border-gray-800 pt-1 text-[10px] font-bold text-gray-700">
                        Firma Responsable de Detección
                    </div>
                </div>
            </div>

            <!-- Dónde se originó -->
            <div class="border border-gray-800 rounded-xl p-4 space-y-3">
                <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider bg-gray-100 p-1 rounded border border-gray-300">
                    Dónde se Originó la No Conformidad
                </h3>
                <div class="text-xs space-y-1">
                    <div><strong>Área:</strong> {{ $pnc->originado_area }}</div>
                    <div><strong>Fecha:</strong> {{ $pnc->originado_fecha ? $pnc->originado_fecha->format('d/m/Y') : '-' }}</div>
                    <div><strong>Responsable:</strong> {{ $pnc->originado_responsable ?: '-' }}</div>
                </div>

                <!-- Recuadro para Firma -->
                <div class="pt-4 text-center">
                    <div class="border-t border-gray-800 pt-1 text-[10px] font-bold text-gray-700">
                        Firma Responsable de Origen
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. EVALUACIÓN / PRUEBAS REALIZADAS -->
        <div class="border border-gray-800 rounded-xl p-4 space-y-2">
            <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider bg-gray-100 p-1.5 rounded border border-gray-300">
                4. Evaluación / Pruebas Realizadas
            </h3>
            <div class="grid grid-cols-4 gap-2 text-xs">
                <div class="flex items-center space-x-1.5">
                    <span>{!! $pnc->eval_revision_registros ? '☑' : '☐' !!}</span>
                    <span>Revisión Registros / Proceso</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span>{!! $pnc->eval_inspeccion_visual ? '☑' : '☐' !!}</span>
                    <span>Inspección Visual</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span>{!! $pnc->eval_analisis_pruebas ? '☑' : '☐' !!}</span>
                    <span>Análisis / Pruebas</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span>{!! $pnc->eval_otros_check ? '☑' : '☐' !!}</span>
                    <span>Otros: {{ $pnc->eval_otros_texto ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- 5. TRATAMIENTO DE SALIDA NO CONFORME Y AUTORIZACIÓN -->
        <div class="border border-gray-800 rounded-xl p-4 space-y-3">
            <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider bg-gray-100 p-1.5 rounded border border-gray-300">
                5. Tratamiento de Salida No Conforme & Autorización
            </h3>

            <div class="grid grid-cols-3 gap-2 text-xs">
                <div>{!! $pnc->tratamiento_devolucion ? '☑' : '☐' !!} Devolución</div>
                <div>{!! $pnc->tratamiento_reproceso ? '☑' : '☐' !!} Reproceso</div>
                <div>{!! $pnc->tratamiento_reclasificado ? '☑' : '☐' !!} Reclasificado</div>
                <div>{!! $pnc->tratamiento_molido ? '☑' : '☐' !!} Molido / Peletizado</div>
                <div>{!! $pnc->tratamiento_desperdicio ? '☑' : '☐' !!} Desperdicio / Scrap</div>
                <div>{!! $pnc->tratamiento_refilado ? '☑' : '☐' !!} Refilado</div>
                <div>{!! $pnc->tratamiento_concesion ? '☑' : '☐' !!} Concesión</div>
                <div>{!! $pnc->tratamiento_desviacion ? '☑' : '☐' !!} Desviación</div>
                <div>{!! $pnc->tratamiento_otros ? '☑' : '☐' !!} Otros</div>
            </div>

            <div class="pt-4 border-t border-gray-300 grid grid-cols-3 gap-4 text-xs">
                <div>
                    <strong>Autorizado Por:</strong><br>
                    <span>{{ $pnc->tratamiento_autorizado_por ?: '' }}</span>
                    <br>
                </div>

                <div>
                    <strong>Fecha Autorización:</strong><br>
                    <span>{{ $pnc->tratamiento_fecha ? $pnc->tratamiento_fecha->format('d/m/Y') : '' }}</span>
                    <br>
                </div>

                <div class="text-center">
                    <br><br><br>
                    <div class="border-t border-gray-800 pt-1 text-[10px] font-bold text-gray-700">
                        Firma de Autorización Calidad
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- 6. CAUSA RAÍZ (5M) Y ACCIÓN CORRECTIVA -->
        <div class="border border-gray-800 rounded-xl p-4 space-y-3">
            <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider bg-gray-100 p-1.5 rounded border border-gray-300">
                6. Análisis de Causa Raíz
            </h3>

            <div class="flex flex-wrap gap-4 text-xs border-b border-gray-200 pb-2">
                <div>{!! $pnc->causa_mano_obra ? '☑' : '☐' !!} Mano de Obra</div>
                <div>{!! $pnc->causa_maquina ? '☑' : '☐' !!} Máquina</div>
                <div>{!! $pnc->causa_material ? '☑' : '☐' !!} Material</div>
                <div>{!! $pnc->causa_metodo ? '☑' : '☐' !!} Método</div>
                <div>{!! $pnc->causa_medio_ambiente ? '☑' : '☐' !!} Medio Ambiente</div>
            </div>

            <div class="space-y-4 text-xs">
                <div class="w-full">
                    <strong class="block text-gray-700 uppercase tracking-wider text-[10px]">Causa Principal Determinada:</strong>
                    <p class="p-3 bg-gray-50 rounded-lg border border-gray-200 mt-1 min-h-[40px] text-gray-900 font-medium w-full">{{ $pnc->causa_principal ?: '' }}</p>
                </div>

                <div class="w-full">
                    <strong class="block text-gray-700 uppercase tracking-wider text-[10px]">Acción Correctiva Imputada:</strong>
                    <p class="p-3 bg-gray-50 rounded-lg border border-gray-200 mt-1 min-h-[40px] text-gray-900 font-medium w-full">{{ $pnc->accion_correctiva ?: '' }}</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
