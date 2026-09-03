@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    <!-- ALERTAS FLASH -->
    @if(session('error'))
        <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
    @endif

    <!-- BOTÓN VOLVER Y ENCABEZADO OFICIAL -->
    <div class="flex items-center justify-between">
        <a href="{{ $codigoInspeccion ? route('inspecciones-cavidades.show', $codigoInspeccion) : route('pnc.index') }}" 
           class="text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 hover:bg-gray-50 px-4 py-2 rounded-xl transition-all shadow-sm flex items-center space-x-2">
            <span>← Volver</span>
        </a>

        <div class="text-right">
            <span class="bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-full border border-red-200">
                Formato Oficial: FE-SIG-FOR-30-V
            </span>
            <span class="text-[11px] text-gray-400 block mt-1">Versión: 01 | Fecha de vigencia: 9/11/2023</span>
        </div>
    </div>

    <!-- TARJETA DEL FORMULARIO PRINCIPAL -->
    <form action="{{ route('pnc.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        @csrf

        <!-- CABECERA INSTITUCIONAL GRUPO FÉNIX -->
        <div class="bg-gradient-to-r from-red-700 via-fenix-dark to-fenix p-6 text-white border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-sm border border-white/20">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-white/80">FÉNIX - SISTEMA INTEGRADO DE GESTIÓN</span>
                        <h2 class="text-2xl font-bold tracking-tight">REPORTE DE PRODUCTO NO CONFORME (PNC)</h2>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md border border-white/20 p-3 rounded-xl text-right">
                    <span class="text-[10px] text-white/80 uppercase font-bold block">Código PNC Correlativo</span>
                    <span class="text-lg font-mono font-extrabold text-white" x-text="'{{ $nextCodigoPnc }}'">{{ $nextCodigoPnc }}</span>
                    <input type="hidden" name="codigo_pnc" value="{{ $nextCodigoPnc }}">
                    <input type="hidden" name="codigo_inspeccion" value="{{ $codigoInspeccion }}">
                </div>
            </div>
        </div>

        <div class="p-6 space-y-8 divide-y divide-gray-100">

            <!-- SECCIÓN 1: DATOS GENERALES -->
            <div class="space-y-4">
                <h3 class="text-sm font-extrabold text-gray-800 uppercase tracking-wider flex items-center space-x-2">
                    <span class="w-2 h-2 bg-fenix rounded-full"></span>
                    <span>1. Datos Generales de la Falla</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Producto Afectado (Solo Lectura Heredado) -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1 flex items-center justify-between">
                            <span>Producto Afectado</span>
                        </label>
                        @if($selectedProducto)
                            <div class="relative">
                                <input type="text" 
                                       value="{{ $selectedProducto->codigo }} - {{ $selectedProducto->nombre }}" 
                                       readonly disabled
                                       class="w-full pl-3.5 pr-8 py-2.5 bg-gray-100/90 border border-gray-300 rounded-xl text-xs font-bold text-gray-800 cursor-not-allowed shadow-inner">
                                <svg class="w-4 h-4 text-gray-400 absolute right-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input type="hidden" name="producto_id" value="{{ $selectedProducto->id }}">
                        @else
                            <select name="producto_id" required class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-900 focus:ring-fenix focus:border-fenix">
                                <option value="">-- Seleccionar Producto --</option>
                                @foreach($productos as $prod)
                                    <option value="{{ $prod->id }}" {{ old('producto_id') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->codigo }} - {{ $prod->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <!-- Lote de Producción (Solo Lectura Heredado) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1 flex items-center justify-between">
                            <span>Lote de Producción</span>
                        </label>
                        @if($selectedLote)
                            <div class="relative">
                                <input type="text" 
                                       value="{{ $selectedLote->codigo_lote }}" 
                                       readonly disabled
                                       class="w-full pl-3.5 pr-8 py-2.5 bg-gray-100/90 border border-gray-300 rounded-xl text-xs font-mono font-bold text-gray-800 cursor-not-allowed shadow-inner">
                                <svg class="w-4 h-4 text-gray-400 absolute right-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input type="hidden" name="lote_id" value="{{ $selectedLote->id }}">
                        @else
                            <select name="lote_id" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-mono font-semibold text-gray-900">
                                <option value="">-- Seleccionar Lote --</option>
                                @foreach($lotes as $lote)
                                    <option value="{{ $lote->id }}" {{ old('lote_id') == $lote->id ? 'selected' : '' }}>
                                        {{ $lote->codigo_lote }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <!-- Fecha de Emisión Automática e Inmodificable -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1 flex items-center justify-between">
                            <span>Fecha de Emisión</span>
                        </label>
                        <div class="relative">
                            <input type="date" value="{{ $today }}" readonly disabled
                                   class="w-full pl-3.5 pr-8 py-2.5 bg-gray-100/90 border border-gray-300 rounded-xl text-xs font-mono font-bold text-gray-800 cursor-not-allowed shadow-inner">
                            <svg class="w-4 h-4 text-gray-400 absolute right-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input type="hidden" name="fecha" value="{{ $today }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    <!-- Cantidad -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Cantidad</label>
                        <input type="number" step="0.01" name="cantidad" value="{{ old('cantidad', $cantidadSugerida) }}" required
                               placeholder="Ej. 10.50"
                               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-mono font-bold text-gray-900">
                    </div>

                    <!-- Unidad de Medida -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Unidad de Medida *</label>
                        <select name="unidad_medida" required class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-900">
                            <option value="Millares" {{ old('unidad_medida') == 'Millares' ? 'selected' : '' }}>Millares</option>
                            <option value="Unidades" {{ old('unidad_medida') == 'Unidades' ? 'selected' : '' }}>Unidades</option>
                            <option value="Kg" {{ old('unidad_medida') == 'Kg' ? 'selected' : '' }}>Kg (Kilogramos)</option>
                            <option value="Cajas / Bultos" {{ old('unidad_medida') == 'Cajas / Bultos' ? 'selected' : '' }}>Cajas / Bultos</option>
                        </select>
                    </div>

                    <!-- Cliente / Proveedor -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Cliente / Proveedor (Opcional)</label>
                        <input type="text" name="cliente_proveedor" value="{{ old('cliente_proveedor') }}" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-medium text-gray-900">
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: DESCRIPCIÓN DE LA NO CONFORMIDAD -->
            <div class="pt-6 space-y-4">
                <h3 class="text-sm font-extrabold text-gray-800 uppercase tracking-wider flex items-center space-x-2">
                    <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                    <span>2. Descripción Detallada de la No Conformidad Detectada</span>
                </h3>

                <textarea name="descripcion_nc" rows="3" required
                          placeholder="Describe detalladamente el problema..."
                          class="w-full px-3.5 py-2.5 border border-red-200 rounded-xl text-xs font-medium text-gray-900 bg-red-50/30 focus:ring-red-500 focus:border-red-500"></textarea>
            </div>

            <!-- SECCIÓN 3: DÓNDE SE DETECTÓ Y DÓNDE SE ORIGINÓ -->
            <div class="pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Dónde se detectó -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                    <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-2">
                        Dónde se Detectó la Falla
                    </h4>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Área</label>
                        <input type="text" name="detectado_area" value="{{ old('detectado_area') }}"
                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-semibold">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Fecha</label>
                            <input type="date" name="detectado_fecha" value="{{ old('detectado_fecha', $today) }}"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Responsable</label>
                            <input type="text" name="detectado_responsable" value="{{ old('detectado_responsable') }}"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs">
                        </div>
                    </div>

                    <!-- Espacio para Firma -->
                    <div class="pt-3">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg py-4 text-center bg-white">
                            <span class="text-[10px] text-gray-400 font-bold uppercase block">Espacio Reservado para Firma Física</span>
                            <span class="text-[9px] text-gray-400 italic">Responsable de Detección</span>
                        </div>
                    </div>
                </div>

                <!-- Dónde se originó -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                    <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-2">
                        Dónde se Originó la No Conformidad
                    </h4>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Área</label>
                        <input type="text" name="originado_area" value=""
                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-semibold">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Fecha</label>
                            <input type="date" name="originado_fecha" value="{{ old('originado_fecha', $today) }}"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Responsable</label>
                            <input type="text" name="originado_responsable" value=""
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs">
                        </div>
                    </div>

                    <!-- Espacio para Firma -->
                    <div class="pt-3">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg py-4 text-center bg-white">
                            <span class="text-[10px] text-gray-400 font-bold uppercase block">Espacio Reservado para Firma Física</span>
                            <span class="text-[9px] text-gray-400 italic">Responsable de Origen</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 4: EVALUACIÓN / PRUEBAS REALIZADAS -->
            <div class="pt-6 space-y-4">
                <h3 class="text-sm font-extrabold text-gray-800 uppercase tracking-wider flex items-center space-x-2">
                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                    <span>4. Evaluación / Pruebas Realizadas</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                    <label class="inline-flex items-center space-x-2 cursor-pointer bg-white p-2.5 rounded-lg border border-gray-200 text-xs font-semibold">
                        <input type="checkbox" name="eval_revision_registros" value="1" {{ old('eval_revision_registros') ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <span>Revisión de Registros y Condiciones de Proceso</span>
                    </label>

                    <label class="inline-flex items-center space-x-2 cursor-pointer bg-white p-2.5 rounded-lg border border-gray-200 text-xs font-semibold">
                        <input type="checkbox" name="eval_inspeccion_visual" value="1" {{ old('eval_inspeccion_visual') ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <span>Inspección Visual</span>
                    </label>

                    <label class="inline-flex items-center space-x-2 cursor-pointer bg-white p-2.5 rounded-lg border border-gray-200 text-xs font-semibold">
                        <input type="checkbox" name="eval_analisis_pruebas" value="1" {{ old('eval_analisis_pruebas') ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                        <span>Análisis Metrológico / Pruebas</span>
                    </label>

                    <div x-data="{ checkOtros: {{ old('eval_otros_check') ? 'true' : 'false' }} }" class="bg-white p-2.5 rounded-lg border border-gray-200 space-y-2">
                        <label class="inline-flex items-center space-x-2 cursor-pointer text-xs font-semibold">
                            <input type="checkbox" name="eval_otros_check" value="1" x-model="checkOtros" class="w-4 h-4 text-blue-600 rounded">
                            <span>Otros (Especificar)</span>
                        </label>
                        <input type="text" name="eval_otros_texto" value="{{ old('eval_otros_texto') }}" x-show="checkOtros" placeholder="Detalle de otra prueba..."
                               class="w-full px-2 py-1 border border-gray-300 rounded text-xs">
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 5: TRATAMIENTO DE SALIDA NO CONFORME Y AUTORIZACIÓN -->
            <div class="pt-6 space-y-4">
                <h3 class="text-sm font-extrabold text-gray-800 uppercase tracking-wider flex items-center space-x-2">
                    <span class="w-2 h-2 bg-amber-600 rounded-full"></span>
                    <span>5. Tratamiento de Salida No Conforme</span>
                </h3>

                <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-200 space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <label class="inline-flex items-center space-x-2 bg-white p-2 rounded-lg border border-amber-200 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" name="tratamiento_devolucion" value="1" {{ old('tratamiento_devolucion') ? 'checked' : '' }} class="w-4 h-4 text-amber-600 rounded">
                            <span>Devolución</span>
                        </label>

                        <label class="inline-flex items-center space-x-2 bg-white p-2 rounded-lg border border-amber-200 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" name="tratamiento_reproceso" value="1" {{ old('tratamiento_reproceso') ? 'checked' : '' }} class="w-4 h-4 text-amber-600 rounded">
                            <span>Reproceso</span>
                        </label>

                        <label class="inline-flex items-center space-x-2 bg-white p-2 rounded-lg border border-amber-200 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" name="tratamiento_reclasificado" value="1" {{ old('tratamiento_reclasificado') ? 'checked' : '' }} class="w-4 h-4 text-amber-600 rounded">
                            <span>Reclasificado</span>
                        </label>

                        <label class="inline-flex items-center space-x-2 bg-white p-2 rounded-lg border border-amber-200 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" name="tratamiento_molido" value="1" {{ old('tratamiento_molido') ? 'checked' : '' }} class="w-4 h-4 text-amber-600 rounded">
                            <span>Molido / Peletizado</span>
                        </label>

                        <label class="inline-flex items-center space-x-2 bg-white p-2 rounded-lg border border-amber-200 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" name="tratamiento_desperdicio" value="1" {{ old('tratamiento_desperdicio') ? 'checked' : '' }} class="w-4 h-4 text-amber-600 rounded">
                            <span>Desperdicio / Scrap</span>
                        </label>

                        <label class="inline-flex items-center space-x-2 bg-white p-2 rounded-lg border border-amber-200 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" name="tratamiento_refilado" value="1" {{ old('tratamiento_refilado') ? 'checked' : '' }} class="w-4 h-4 text-amber-600 rounded">
                            <span>Refilado</span>
                        </label>

                        <label class="inline-flex items-center space-x-2 bg-white p-2 rounded-lg border border-amber-200 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" name="tratamiento_concesion" value="1" {{ old('tratamiento_concesion') ? 'checked' : '' }} class="w-4 h-4 text-amber-600 rounded">
                            <span>Concesión</span>
                        </label>

                        <label class="inline-flex items-center space-x-2 bg-white p-2 rounded-lg border border-amber-200 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" name="tratamiento_desviacion" value="1" {{ old('tratamiento_desviacion') ? 'checked' : '' }} class="w-4 h-4 text-amber-600 rounded">
                            <span>Desviación</span>
                        </label>

                        <label class="inline-flex items-center space-x-2 bg-white p-2 rounded-lg border border-amber-200 text-xs font-semibold cursor-pointer">
                            <input type="checkbox" name="tratamiento_otros" value="1" {{ old('tratamiento_otros') ? 'checked' : '' }} class="w-4 h-4 text-amber-600 rounded">
                            <span>Otros</span>
                        </label>
                    </div>

                    <!-- Datos de Autorización -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-3 border-t border-amber-200">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Autorizado por (Nombre y Cargo)</label>
                            <input type="text" name="tratamiento_autorizado_por" value="" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-semibold">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Fecha de Autorización</label>
                            <input type="date" name="tratamiento_fecha" value=""
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs">
                        </div>

                        <div>
                            <div class="border-2 border-dashed border-amber-300 rounded-lg py-2.5 text-center bg-white">
                                <span class="text-[10px] text-amber-800 font-bold uppercase block">Firma de Autorización</span>
                                <br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 6: ANÁLISIS DE CAUSA RAÍZ (5M) Y ACCIÓN CORRECTIVA -->
            <div class="pt-6 space-y-4">
                <h3 class="text-sm font-extrabold text-gray-800 uppercase tracking-wider flex items-center space-x-2">
                    <span class="w-2 h-2 bg-emerald-600 rounded-full"></span>
                    <span>6. Causa Raíz</span>
                </h3>

                <div class="space-y-4 bg-emerald-50/50 p-4 rounded-xl border border-emerald-200">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Factores Involucrados (5M):</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center space-x-2 bg-white px-3 py-1.5 rounded-lg border border-emerald-200 text-xs font-semibold cursor-pointer">
                                <input type="checkbox" name="causa_mano_obra" value="1" {{ old('causa_mano_obra') ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 rounded">
                                <span>Mano de Obra</span>
                            </label>

                            <label class="inline-flex items-center space-x-2 bg-white px-3 py-1.5 rounded-lg border border-emerald-200 text-xs font-semibold cursor-pointer">
                                <input type="checkbox" name="causa_maquina" value="1" {{ old('causa_maquina', true) ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 rounded">
                                <span>Máquina</span>
                            </label>

                            <label class="inline-flex items-center space-x-2 bg-white px-3 py-1.5 rounded-lg border border-emerald-200 text-xs font-semibold cursor-pointer">
                                <input type="checkbox" name="causa_material" value="1" {{ old('causa_material') ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 rounded">
                                <span>Material</span>
                            </label>

                            <label class="inline-flex items-center space-x-2 bg-white px-3 py-1.5 rounded-lg border border-emerald-200 text-xs font-semibold cursor-pointer">
                                <input type="checkbox" name="causa_metodo" value="1" {{ old('causa_metodo') ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 rounded">
                                <span>Método</span>
                            </label>

                            <label class="inline-flex items-center space-x-2 bg-white px-3 py-1.5 rounded-lg border border-emerald-200 text-xs font-semibold cursor-pointer">
                                <input type="checkbox" name="causa_medio_ambiente" value="1" {{ old('causa_medio_ambiente') ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 rounded">
                                <span>Medio Ambiente</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Causa Principal</label>
                            <textarea name="causa_principal" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs font-medium text-gray-900 bg-white">{{ old('causa_principal') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Acción Correctiva</label>
                            <textarea name="accion_correctiva" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs font-medium text-gray-900 bg-white">{{ old('accion_correctiva') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTONES DE ACCIÓN -->
            <div class="pt-6 flex justify-end space-x-4">
                <a href="{{ route('pnc.index') }}" 
                   class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold text-xs rounded-xl transition-all">
                    Cancelar
                </a>

                <button type="submit" 
                        class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Emitir Reporte PNC</span>
                </button>
            </div>

        </div>
    </form>
</div>
@endsection
