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

            <!-- SECCIÓN 1: DATOS GENERALES Y CANTIDAD CON CONVERSIÓN DINÁMICA -->
            <div class="space-y-4" x-data="pncCalculos">
                <h3 class="text-sm font-extrabold text-gray-800 uppercase tracking-wider flex items-center space-x-2">
                    <span class="w-2 h-2 bg-fenix rounded-full"></span>
                    <span>1. Datos Generales de la Falla y Registro de Cantidad</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Producto Afectado (Solo Lectura Heredado o Selección) -->
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
                            <!-- Buscador Filtrable de Producto en Tiempo Real con Alpine.js -->
                            <div x-data="{
                                open: false,
                                search: '',
                                productosList: [
                                    @foreach($productos as $prod)
                                        {
                                            id: '{{ $prod->id }}',
                                            codigo: '{{ addslashes($prod->codigo) }}',
                                            nombre: '{{ addslashes($prod->nombre) }}',
                                            fullText: '{{ addslashes($prod->codigo . ' - ' . $prod->nombre) }}',
                                            searchKey: '{{ strtolower(addslashes($prod->codigo . ' ' . $prod->nombre)) }}'
                                        },
                                    @endforeach
                                ],
                                get filteredProductos() {
                                    if (!this.search.trim()) return this.productosList;
                                    const q = this.search.toLowerCase().trim();
                                    return this.productosList.filter(p => p.searchKey.includes(q));
                                },
                                selectedText: '{{ old('producto_id') && $productos->firstWhere('id', old('producto_id')) ? addslashes($productos->firstWhere('id', old('producto_id'))->codigo . ' - ' . $productos->firstWhere('id', old('producto_id'))->nombre) : '' }}',
                                selectProduct(prod) {
                                    this.productoId = prod.id;
                                    this.selectedText = prod.fullText;
                                    this.open = false;
                                    this.search = '';
                                }
                            }" class="relative">
                                <input type="hidden" name="producto_id" x-model="productoId" required>

                                <div @click="open = !open" 
                                     class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold bg-gray-50/50 cursor-pointer flex items-center justify-between focus:ring-1 focus:ring-fenix shadow-2xs">
                                    <span x-text="selectedText || '-- Digite o busque producto por código o nombre --'" 
                                          :class="selectedText ? 'text-gray-900 font-bold' : 'text-gray-400 font-normal'"></span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>

                                <div x-show="open" 
                                     @click.away="open = false" 
                                     x-transition
                                     class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-hidden flex flex-col text-xs">
                                    <div class="p-2 border-b border-gray-100 bg-gray-50">
                                        <input type="text" 
                                               x-model="search" 
                                               placeholder="Escriba código o nombre para filtrar de inmediato..." 
                                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-fenix font-medium">
                                    </div>

                                    <div class="overflow-y-auto max-h-48 divide-y divide-gray-50">
                                        <template x-for="prod in filteredProductos" :key="prod.id">
                                            <div @click="selectProduct(prod)" 
                                                 class="px-3.5 py-2.5 hover:bg-fenix/10 hover:text-fenix cursor-pointer transition-colors font-medium flex items-center justify-between">
                                                <div>
                                                    <span class="font-bold text-gray-900" x-text="prod.codigo"></span>
                                                    <span class="text-gray-600 ml-1.5" x-text="'- ' + prod.nombre"></span>
                                                </div>
                                                <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full">Elegir</span>
                                            </div>
                                        </template>

                                        <div x-show="search && filteredProductos.length === 0" class="p-3 text-amber-800 bg-amber-50 text-xs">
                                            <span>No se encontraron productos coincidentes con &quot;<strong x-text="search"></strong>&quot;</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

                <!-- CONTROLES DE CANTIDAD Y PRESENTACIÓN DE EMPAQUE -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    <!-- Cantidad Afectada -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Cantidad Registrada *</label>
                        <input type="number" step="0.01" name="cantidad" x-model="cantidad" required
                               placeholder="Ej. 3"
                               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-mono font-bold text-gray-900 focus:ring-fenix focus:border-fenix">
                    </div>

                    <!-- Tipo de Empaque / Unidad Principal -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Tipo de Empaque / Unidad *</label>
                        <select name="unidad_medida" x-model="unidadMedida" required class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-bold text-gray-900 focus:ring-fenix focus:border-fenix">
                            <option value="Cajas">📦 Cajas</option>
                            <option value="Sacos">🛍️ Sacos</option>
                            <option value="Millares">🔢 Millares</option>
                            <option value="Kg">⚖️ Kg (Kilogramos)</option>
                            <option value="Unidades">🧩 Unidades</option>
                        </select>
                    </div>

                    <!-- Gramaje del Producto (Informativo) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Gramaje Nominal</label>
                        <div class="px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-700 flex items-center justify-between shadow-inner">
                            <span x-text="gramaje ? (gramaje.toFixed(2) + ' g') : 'Sin gramaje registrado'"></span>
                            <span class="text-[10px] text-gray-400 font-sans font-normal">Base Matriz</span>
                        </div>
                    </div>
                </div>

                <!-- TARJETA VISUAL DE CONVERSIÓN DINÁMICA EN TIEMPO REAL -->
                <div class="mt-4 p-5 bg-gradient-to-r from-slate-900 via-gray-900 to-red-950 rounded-2xl text-white shadow-xl border border-red-900/40 relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-red-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                        <div>
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="px-2.5 py-0.5 bg-red-500/20 text-red-300 text-[10px] font-extrabold uppercase rounded-md border border-red-500/30 tracking-wider">
                                    ⚡ CONVERSIÓN MATRIZ DE EMPAQUE
                                </span>
                            </div>
                            <div class="text-xs text-red-200/80 font-mono font-medium" x-text="formulaUsada"></div>
                            <p class="text-[11px] text-gray-400 mt-1">Sustituye automáticamente la segunda unidad manual. Calculado en tiempo real.</p>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- Equivalente Total Millares -->
                            <div class="bg-white/10 backdrop-blur-md px-5 py-3 rounded-xl border border-white/15 text-center min-w-[140px] shadow-sm">
                                <span class="text-[10px] uppercase font-bold text-gray-300 block tracking-wider">Total Millares</span>
                                <div class="flex items-baseline justify-center space-x-1">
                                    <span class="text-2xl font-mono font-extrabold text-emerald-400" x-text="totalMillares ? totalMillares.toFixed(3) : '0.000'">0.000</span>
                                    <span class="text-xs text-gray-300 font-bold">mil</span>
                                </div>
                                <span class="text-[10px] text-gray-300 font-mono block mt-0.5" x-text="'(' + unidadesTotales.toLocaleString() + ' u)'"></span>
                            </div>

                            <!-- Equivalente Total Peso Neto (Kg) -->
                            <div class="bg-white/10 backdrop-blur-md px-5 py-3 rounded-xl border border-white/15 text-center min-w-[140px] shadow-sm">
                                <span class="text-[10px] uppercase font-bold text-gray-300 block tracking-wider">Peso Neto Total</span>
                                <div class="flex items-baseline justify-center space-x-1">
                                    <span class="text-2xl font-mono font-extrabold text-amber-300" x-text="totalPesoKg ? totalPesoKg.toFixed(2) : '0.00'">0.00</span>
                                    <span class="text-xs text-gray-300 font-bold">Kg</span>
                                </div>
                                <span class="text-[10px] text-gray-300 block mt-0.5">Masa acumulada</span>
                            </div>
                        </div>
                    </div>

                    <!-- Campos Ocultos para Persistir Totales en BD -->
                    <input type="hidden" name="total_millares" :value="totalMillares">
                    <input type="hidden" name="total_peso_kg" :value="totalPesoKg">
                </div>

                <!-- Fila 3: Cliente / Proveedor (Ancho Completo - 100%) -->
                <div class="pt-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Cliente / Proveedor (Opcional)</label>
                    <input type="text" name="cliente_proveedor" value="{{ old('cliente_proveedor') }}"
                           placeholder="Indicar el nombre del cliente o proveedor si corresponde (Opcional)..."
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-medium text-gray-900">
                </div>
            </div>

            <!-- SECCIÓN 2: DESCRIPCIÓN DE LA NO CONFORMIDAD -->
            <div class="pt-6 space-y-4">
                <h3 class="text-sm font-extrabold text-gray-800 uppercase tracking-wider flex items-center space-x-2">
                    <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                    <span>2. Descripción Detallada de la No Conformidad Detectada</span>
                </h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Columna Izquierda: Textarea Descripción -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Detalle del Problema / Falla Registrada *
                        </label>
                        <textarea name="descripcion_nc" rows="5" required
                                  placeholder="Describe detalladamente las no conformidades físicas, dimensionales o visuales detectadas en las preformas..."
                                  class="w-full px-3.5 py-2.5 border border-red-200 rounded-xl text-xs font-medium text-gray-900 bg-red-50/30 focus:ring-red-500 focus:border-red-500 shadow-inner h-40 resize-none">{{ old('descripcion_nc') }}</textarea>
                    </div>

                    <!-- Columna Derecha: Subsección Cavidades con Fallas Detectadas -->
                    <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200/80 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between border-b border-gray-200 pb-2 mb-3">
                                <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider flex items-center space-x-1.5">
                                    <span>🔍 Cavidades con Fallas Detectadas</span>
                                </h4>
                                @if(isset($cavidadesDefectuosas) && $cavidadesDefectuosas->count() > 0)
                                    <span class="px-2 py-0.5 bg-red-100 text-red-800 text-[10px] font-bold rounded-full border border-red-200 font-mono">
                                        {{ $cavidadesDefectuosas->count() }} afectas
                                    </span>
                                @endif
                            </div>

                            @if(isset($cavidadesDefectuosas) && $cavidadesDefectuosas->count() > 0)
                                <div class="max-h-32 overflow-y-auto space-y-2 pr-1">
                                    @foreach($cavidadesDefectuosas as $cav)
                                        <div class="bg-white p-2 rounded-lg border border-gray-200 shadow-2xs flex items-center justify-between text-xs">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-mono font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded text-[11px] border border-gray-200">
                                                    Cav. {{ sprintf('%02d', $cav->cavidad_numero) }}
                                                </span>
                                                @if(!empty($cav->motivo_scrap))
                                                    <span class="text-[11px] font-semibold text-gray-700 truncate max-w-[130px]" title="{{ $cav->motivo_scrap }}">
                                                        {{ $cav->motivo_scrap }}
                                                    </span>
                                                @elseif(!empty($cav->observaciones))
                                                    <span class="text-[11px] font-medium text-gray-600 truncate max-w-[130px]" title="{{ $cav->observaciones }}">
                                                        {{ $cav->observaciones }}
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Badge de Estado exacto -->
                                            <div>
                                                @if($cav->estado === 'FUERA_DE_RANGO')
                                                    <span class="px-2 py-0.5 bg-red-100 text-red-800 text-[10px] font-bold rounded-full border border-red-200">
                                                        🔴 FUERA DE RANGO
                                                    </span>
                                                @elseif($cav->estado === 'OBSERVADO')
                                                    <span class="px-2 py-0.5 bg-orange-100 text-orange-800 text-[10px] font-bold rounded-full border border-orange-200">
                                                        🟠 OBSERVADO
                                                    </span>
                                                @elseif($cav->estado === 'PASABLE')
                                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-full border border-amber-200">
                                                        ⚠️ PASABLE
                                                    </span>
                                                @elseif($cav->estado === 'ANULADO')
                                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-bold rounded-full border border-gray-200">
                                                        ⚪ ANULADO
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-red-100 text-red-800 text-[10px] font-bold rounded-full border border-red-200">
                                                        🔴 {{ $cav->estado }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 text-center text-gray-400 bg-white rounded-lg border border-dashed border-gray-200">
                                    <p class="text-xs font-medium text-gray-500">No hay inspección de cavidades asociada</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">O no se identificaron cavidades con desviación.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3: DÓNDE SE DETECTÓ Y DÓNDE SE ORIGINÓ -->
            <div class="pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Dónde se detectó -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                    <h4 class="text-xs font-extrabold text-gray-800 uppercase tracking-wider border-b border-gray-200 pb-2">
                        Dónde se Detectó la Falla
                    </h4>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Área *</label>
                        <select name="detectado_area" required class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-semibold text-gray-900 focus:ring-fenix focus:border-fenix">
                            <option value="">-- Seleccionar Área --</option>
                            <option value="Laminado" {{ old('detectado_area') == 'Laminado' ? 'selected' : '' }}>Laminado</option>
                            <option value="Termoformado" {{ old('detectado_area') == 'Termoformado' ? 'selected' : '' }}>Termoformado</option>
                            <option value="Inyección" {{ old('detectado_area', 'Inyección') == 'Inyección' ? 'selected' : '' }}>Inyección</option>
                            <option value="Almacén" {{ old('detectado_area') == 'Almacén' ? 'selected' : '' }}>Almacén</option>
                            <option value="Cliente" {{ old('detectado_area') == 'Cliente' ? 'selected' : '' }}>Cliente</option>
                        </select>
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
                        <span>Análisis / Pruebas</span>
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
                                <input type="checkbox" name="causa_maquina" value="1" {{ old('causa_maquina') ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 rounded">
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

                    <div class="space-y-4">
                        <div class="w-full">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Causa Principal</label>
                            <textarea name="causa_principal" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs font-medium text-gray-900 bg-white">{{ old('causa_principal') }}</textarea>
                        </div>

                        <div class="w-full">
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

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pncCalculos', () => ({
        products: @json($productos),
        productoId: '{{ old('producto_id', $selectedProducto->id ?? '') }}',
        cantidad: '{{ old('cantidad', $cantidadSugerida) }}',
        unidadMedida: '{{ old('unidad_medida', 'Cajas') }}',
        totalMillares: 0,
        totalPesoKg: 0,
        unidadesTotales: 0,
        formulaUsada: '',

        init() {
            this.calcular();
            this.$watch('productoId', () => this.calcular());
            this.$watch('cantidad', () => this.calcular());
            this.$watch('unidadMedida', () => this.calcular());
        },

        get currentProduct() {
            if (!this.productoId) return null;
            return this.products.find(p => p.id == this.productoId) || null;
        },

        get gramaje() {
            if (!this.currentProduct) return 0;
            if (this.currentProduct.parametro_preforma && this.currentProduct.parametro_preforma.peso_nominal) {
                return parseFloat(this.currentProduct.parametro_preforma.peso_nominal);
            }
            return parseFloat(this.currentProduct.peso_unitario || 0);
        },

        get matrizCaja() {
            if (!this.currentProduct || !this.currentProduct.matriz_empaques) return null;
            return this.currentProduct.matriz_empaques.find(m => (m.presentacion || '').toUpperCase() === 'CAJA') || null;
        },

        get matrizSaco() {
            if (!this.currentProduct || !this.currentProduct.matriz_empaques) return null;
            return this.currentProduct.matriz_empaques.find(m => (m.presentacion || '').toUpperCase() === 'SACO') || null;
        },

        calcular() {
            let qty = parseFloat(this.cantidad) || 0;
            let unit = (this.unidadMedida || '').toUpperCase();
            let g = this.gramaje;

            if (qty <= 0) {
                this.totalMillares = 0;
                this.totalPesoKg = 0;
                this.unidadesTotales = 0;
                this.formulaUsada = 'Ingrese una cantidad para calcular equivalencias automáticamente.';
                return;
            }

            if (unit.includes('CAJA') || unit.includes('BULTO')) {
                let mCaja = this.matrizCaja;
                if (mCaja && parseFloat(mCaja.factor_millares) > 0) {
                    let fMil = parseFloat(mCaja.factor_millares);
                    let fKg = parseFloat(mCaja.factor_peso_kg);
                    this.totalMillares = qty * fMil;
                    this.totalPesoKg = qty * fKg;
                    this.formulaUsada = `${qty} Cajas × ${fMil} mil/caja (${fKg} kg/caja)`;
                } else {
                    let factorMillares = 1.55;
                    this.totalMillares = qty * factorMillares;
                    this.totalPesoKg = g > 0 ? ((this.totalMillares * 1000 * g) / 1000) : (qty * 19.22);
                    this.formulaUsada = `${qty} Cajas × ${factorMillares} mil/caja (Estándar 12.4g)`;
                }
            } else if (unit.includes('SACO')) {
                let mSaco = this.matrizSaco;
                if (mSaco && parseFloat(mSaco.factor_millares) > 0) {
                    let fMil = parseFloat(mSaco.factor_millares);
                    let fKg = parseFloat(mSaco.factor_peso_kg);
                    this.totalMillares = qty * fMil;
                    this.totalPesoKg = qty * fKg;
                    this.formulaUsada = `${qty} Sacos × ${fMil} mil/saco (${fKg} kg/saco)`;
                } else {
                    let factorMillares = 3.0;
                    this.totalMillares = qty * factorMillares;
                    this.totalPesoKg = g > 0 ? ((this.totalMillares * 1000 * g) / 1000) : (qty * 37.20);
                    this.formulaUsada = `${qty} Sacos × ${factorMillares} mil/saco (Estándar de Conversión)`;
                }
            } else if (unit.includes('MILLAR')) {
                this.totalMillares = qty;
                this.totalPesoKg = g > 0 ? (qty * g) : 0;
                this.formulaUsada = `${qty} Millares × ${g}g por unidad`;
            } else if (unit.includes('KG') || unit.includes('KILO')) {
                this.totalPesoKg = qty;
                this.totalMillares = g > 0 ? (qty / g) : 0;
                this.formulaUsada = `${qty} Kg ÷ (${g}g / 1000)`;
            } else { // Unidades / Preformas
                this.totalMillares = qty / 1000;
                this.totalPesoKg = g > 0 ? ((qty * g) / 1000) : 0;
                this.formulaUsada = `${qty} Unidades ÷ 1000 (${g}g/unidad)`;
            }

            this.unidadesTotales = Math.round(this.totalMillares * 1000);
        }
    }));
});
</script>
@endsection
