@extends('layouts.app')

@section('content')
<!-- Select2 CSS & JS CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* Estilos de integración de Select2 con Tailwind */
    .select2-container--default .select2-selection--single {
        background-color: #f9fafb;
        border-color: #e5e7eb;
        border-radius: 0.75rem;
        height: 42px;
        display: flex;
        align-items: center;
        padding-left: 0.25rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1f2937;
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 42px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .select2-container--default .select2-dropdown {
        border-color: #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        font-size: 0.75rem;
        z-index: 50;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #30732B;
        color: white;
    }
</style>

<div class="max-w-5xl mx-auto space-y-6">
    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative">
            <strong class="font-bold">¡Atención! Hay errores de validación:</strong>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header y Regreso -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('atc.index') }}" class="inline-flex items-center text-xs font-bold text-gray-500 hover:text-emerald-700 transition-colors mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver a la lista de ATC
            </a>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Nuevo Registro de Atención al Cliente (ATC)</h1>
                
                <!-- Indicador Visual de Estado de Conexión (Online / Offline) -->
                <div id="network-status-badge" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold transition-all border shadow-xs bg-emerald-50 text-emerald-800 border-emerald-200">
                    <span id="network-status-dot" class="w-2 h-2 rounded-full mr-1.5 bg-emerald-500 animate-pulse"></span>
                    <span id="network-status-text">Online</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 font-medium">Formulario oficial para el registro de reclamos o devoluciones de clientes</p>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Badge para Registros Offline Pendientes de Sincronización -->
            <div id="pending-sync-badge" class="hidden inline-flex items-center bg-amber-50 text-amber-800 border border-amber-200 px-3 py-1.5 rounded-xl text-xs font-bold space-x-2 shadow-xs">
                <svg class="w-4 h-4 text-amber-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <div>
                    <span class="block text-[9px] uppercase tracking-wider text-amber-600 font-extrabold">Pendientes Sync</span>
                    <span class="text-sm font-mono font-black" id="pending-sync-count">0</span>
                </div>
                <button type="button" id="btn-force-sync" title="Sincronizar ahora con el servidor" class="ml-1 text-amber-700 hover:text-amber-900 p-1 hover:bg-amber-100 rounded-md transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>

            <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 px-4 py-2 rounded-xl text-right">
                <span class="block text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Correlativo Asignado</span>
                <span class="text-lg font-mono font-black">{{ $nextCorrelativo }}</span>
            </div>
        </div>
    </div>

    <!-- Banner Dinámico de Alertas de Sincronización y Estado Offline -->
    <div id="offline-alert-banner" class="hidden p-4 rounded-xl shadow-sm border transition-all duration-300">
        <div class="flex items-start space-x-3">
            <div id="offline-alert-icon" class="flex-shrink-0 mt-0.5"></div>
            <div class="flex-1 text-xs">
                <h3 id="offline-alert-title" class="font-bold text-sm"></h3>
                <p id="offline-alert-message" class="mt-0.5"></p>
                <div id="offline-alert-details" class="mt-2 text-[11px] font-mono bg-white/50 p-2 rounded border border-gray-200 hidden"></div>
            </div>
            <button type="button" onclick="document.getElementById('offline-alert-banner').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <form id="atc-form" method="POST" action="{{ route('atc.store') }}" class="space-y-6">
        @csrf

        <!-- SECCIÓN 1: Datos de Registro y Tipo -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider border-b border-gray-100 pb-3 flex items-center">
                <span class="w-2.5 h-2.5 rounded-full bg-fenix mr-2"></span>
                1. Información de Control
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Correlativo (Readonly) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Correlativo ATC</label>
                    <input type="text" name="correlativo" value="{{ $nextCorrelativo }}" readonly
                           class="w-full px-3 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-700 cursor-not-allowed">
                    <input type="hidden" name="correlativo_display" value="{{ $nextCorrelativo }}">
                </div>

                <!-- Fecha (Readonly / Auto) -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Fecha de Registro</label>
                    <input type="date" name="fecha" value="{{ old('fecha', $today) }}" required
                           class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                </div>

                <!-- Tipo -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tipo de Evento <span class="text-rose-500">*</span></label>
                    <select name="tipo" required
                            class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                        <option value="Reclamo" {{ old('tipo') == 'Reclamo' ? 'selected' : '' }}>Reclamo</option>
                        <option value="Devolucion" {{ old('tipo') == 'Devolucion' ? 'selected' : '' }}>Devolución</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: Información del Cliente SIF -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider border-b border-gray-100 pb-3 flex items-center justify-between">
                <span class="flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-fenix mr-2"></span>
                    2. Cliente
                </span>
                <span class="text-[10px] font-normal text-emerald-700 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-200">SIF Conectado</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Selector de Cliente SIF -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Seleccionar Cliente SIF <span class="text-rose-500">*</span></label>
                    <select id="select-cliente-sif" 
                            class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                        <option value=""></option>
                        @foreach($clientesSif as $cli)
                            <option value="{{ $cli['nombre'] ?? '' }}" 
                                    data-ruc="{{ $cli['ruc'] ?? '' }}"
                                    {{ old('cliente_nombre') == ($cli['nombre'] ?? '') ? 'selected' : '' }}>
                                {{ $cli['nombre'] ?? '' }} {{ !empty($cli['ruc']) ? '('.$cli['ruc'].')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">Seleccione para autocompletar el nombre y RUC, o ingréselo manualmente abajo.</p>
                </div>

                <!-- Cliente Nombre Manual / Autocompletado -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nombre / Razón Social del Cliente <span class="text-rose-500">*</span></label>
                    <input type="text" id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre') }}" required
                           class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-900 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                </div>

                <!-- Cliente RUC -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">RUC del Cliente</label>
                    <input type="text" id="cliente_ruc" name="cliente_ruc" value="{{ old('cliente_ruc') }}"
                           class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-medium text-gray-800 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                </div>
            </div>
        </div>

        <!-- SECCIÓN 3: Detalles del Producto, Cantidad y Descripción -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider border-b border-gray-100 pb-3 flex items-center">
                <span class="w-2.5 h-2.5 rounded-full bg-fenix mr-2"></span>
                3. Detalle del Producto y Defecto
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Producto con Buscador Filtrable en Tiempo Real (Alpine.js + Texto Libre) -->
                <div x-data="{
                    open: false,
                    query: '{{ old('producto', '') }}',
                    productos: [
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
                        if (!this.query.trim()) return this.productos;
                        const q = this.query.toLowerCase().trim();
                        return this.productos.filter(p => p.searchKey.includes(q));
                    },
                    selectProducto(prod) {
                        this.query = prod.fullText;
                        this.open = false;
                    }
                }" class="relative">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Producto <span class="text-rose-500">*</span>
                    </label>

                    <div class="relative">
                        <input type="text" 
                               name="producto" 
                               x-model="query" 
                               @focus="open = true" 
                               @input="open = true"
                               @keydown.escape="open = false"
                               @click.away="open = false"
                               required 
                               placeholder="Escriba el nombre, código o busque en el listado..."
                               autocomplete="off"
                               class="w-full pl-9 pr-8 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:ring-2 focus:ring-fenix focus:bg-white transition-all shadow-2xs">

                        <!-- Icono Lupa -->
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- Botón Limpiar / Desplegar -->
                        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center space-x-1">
                            <button type="button" x-show="query" @click="query = ''; open = true" class="text-gray-400 hover:text-gray-600 p-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            <button type="button" @click="open = !open" class="text-gray-400 hover:text-gray-600 p-1">
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Panel Flotante Desplegable con Resultados -->
                    <div x-show="open" 
                         x-transition
                         class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl max-h-56 overflow-y-auto divide-y divide-gray-100 text-xs">
                        <template x-for="prod in filteredProductos" :key="prod.id">
                            <div @click="selectProducto(prod)" 
                                 class="px-3.5 py-2.5 hover:bg-emerald-50 hover:text-emerald-900 cursor-pointer transition-colors flex items-center justify-between group">
                                <div>
                                    <span class="font-bold text-gray-900 group-hover:text-emerald-900" x-text="prod.codigo"></span>
                                    <span class="text-gray-600 group-hover:text-emerald-800 ml-1.5" x-text="'- ' + prod.nombre"></span>
                                </div>
                                <span class="text-[10px] bg-gray-100 group-hover:bg-emerald-200 group-hover:text-emerald-900 text-gray-600 font-bold px-2 py-0.5 rounded-full transition-colors">Seleccionar</span>
                            </div>
                        </template>

                        <div x-show="query && filteredProductos.length === 0" class="p-3 text-amber-800 bg-amber-50 text-xs">
                            <div class="font-bold flex items-center space-x-1">
                                <span>✏️ Texto libre personalizado:</span>
                            </div>
                            <p class="text-[11px] text-amber-700 mt-0.5">
                                El producto <strong x-text="'&quot;' + query + '&quot;'"></strong> no está en el catálogo. Se registrará como texto libre.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Cantidad -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Cantidad Afectada <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="cantidad" value="{{ old('cantidad') }}" required placeholder="0.00"
                           class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-900 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                </div>

                <!-- Lote -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Lote del Producto</label>
                    <input type="text" name="lote" value="{{ old('lote') }}" placeholder="Ej: PET260901M01"
                           class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono font-medium text-gray-900 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                </div>

                <!-- Descripción del Reclamo o Devolución -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Descripción del Reclamo / Devolución <span class="text-rose-500">*</span></label>
                    <textarea name="descripcion" rows="4" required placeholder="Describa a detalle las observaciones reportadas por el cliente..."
                              class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">{{ old('descripcion') }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 4: Acciones Correctivas y Plan de Acción -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider border-b border-gray-100 pb-3 flex items-center">
                <span class="w-2.5 h-2.5 rounded-full bg-fenix mr-2"></span>
                4.Plan de Acción
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vinculación con PNC -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Vincular con Producto No Conforme (PNC) si aplica</label>
                    <select name="pnc_id" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                        <option value="">-- Ninguno (No vincular) --</option>
                        @foreach($pncs as $pnc)
                            <option value="{{ $pnc->id }}" {{ old('pnc_id') == $pnc->id ? 'selected' : '' }}>
                                {{ $pnc->codigo_pnc }} - {{ $pnc->producto->nombre ?? 'Sin Producto' }} (Lote: {{ $pnc->lote->codigo_lote ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Acción Correctiva -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Acción Correctiva</label>
                    <textarea name="accion_correctiva" rows="3" placeholder="Detalle las acciones inmediatas o de contención acordadas..."
                              class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">{{ old('accion_correctiva') }}</textarea>
                </div>

                <!-- Plan de Acción (Tabla interactiva estilo PNC) -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-gray-700">Plan de Acción / Medidas Correctivas y Preventivas</label>
                        <button type="button" id="btn-add-plan-row"
                                class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-lg text-xs font-bold transition-all space-x-1 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>+ Agregar Actividad</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-xl bg-white">
                        <table class="w-full text-left border-collapse" id="tabla-plan-accion">
                            <thead>
                                <tr class="bg-gray-50 text-[11px] font-bold text-gray-600 uppercase border-b border-gray-200">
                                    <th class="py-2.5 px-3 text-center w-10">#</th>
                                    <th class="py-2.5 px-3">Actividad / Tarea</th>
                                    <th class="py-2.5 px-3">Responsable</th>
                                    <th class="py-2.5 px-3 w-40">Fecha Límite</th>
                                    <th class="py-2.5 px-3 text-center w-12">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="plan-accion-tbody" class="divide-y divide-gray-100 text-xs">
                                <tr>
                                    <td class="py-2 px-3 text-center font-bold text-gray-400 row-num">1</td>
                                    <td class="py-2 px-3">
                                        <input type="text" name="plan_accion[0][actividad]" placeholder="Describa la actividad a realizar..." 
                                               class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="text" name="plan_accion[0][responsable]" placeholder="Nombre del responsable..." 
                                               class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                                    </td>
                                    <td class="py-2 px-3">
                                        <input type="date" name="plan_accion[0][fecha]" 
                                               class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <button type="button" class="btn-remove-row text-rose-500 hover:text-rose-700 p-1 rounded-lg hover:bg-rose-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end space-x-3 pt-2">
            <a href="{{ route('atc.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-bold transition-all">
                Cancelar
            </a>
            <button type="submit" class="px-8 py-3 bg-fenix hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-lg hover:shadow-xl transition-all flex items-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Guardar Registro ATC</span>
            </button>
        </div>
    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('atc-form');
        const nombreInput = document.getElementById('cliente_nombre');
        const rucInput = document.getElementById('cliente_ruc');
        const networkBadge = document.getElementById('network-status-badge');
        const networkDot = document.getElementById('network-status-dot');
        const networkText = document.getElementById('network-status-text');
        const pendingBadge = document.getElementById('pending-sync-badge');
        const pendingCount = document.getElementById('pending-sync-count');
        const btnForceSync = document.getElementById('btn-force-sync');
        const alertBanner = document.getElementById('offline-alert-banner');
        const alertTitle = document.getElementById('offline-alert-title');
        const alertMessage = document.getElementById('offline-alert-message');
        const alertIcon = document.getElementById('offline-alert-icon');
        const alertDetails = document.getElementById('offline-alert-details');

        const STORAGE_KEY = 'atc_offline_queue_v1';
        let isSyncing = false;

        // ==========================================
        // 1. INICIALIZACIÓN DE SELECT2
        // ==========================================
        if (typeof $ !== 'undefined' && $.fn.select2) {
            const $sifSelect = $('#select-cliente-sif');

            $sifSelect.select2({
                placeholder: 'Escriba el nombre o RUC del cliente...',
                allowClear: true,
                width: '100%'
            });

            $sifSelect.on('select2:select', function (e) {
                const selectedOption = $(this).find(':selected');
                const nombre = selectedOption.val() || '';
                const ruc = selectedOption.attr('data-ruc') || '';

                if (nombreInput) nombreInput.value = nombre;
                if (rucInput) rucInput.value = ruc;
            });

            $sifSelect.on('select2:clear', function (e) {
                if (nombreInput) nombreInput.value = '';
                if (rucInput) rucInput.value = '';
            });
        } else {
            const sifSelect = document.getElementById('select-cliente-sif');
            if (sifSelect) {
                sifSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    const nombre = selectedOption.value;
                    const ruc = selectedOption.getAttribute('data-ruc') || '';

                    if (nombreInput) nombreInput.value = nombre;
                    if (rucInput) rucInput.value = ruc;
                });
            }
        }

        // ==========================================
        // 2. DETECCIÓN DE CONEXIÓN EN TIEMPO REAL
        // ==========================================
        function updateNetworkStatus() {
            const isOnline = navigator.onLine;
            if (isOnline) {
                networkBadge.className = "inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold transition-all border shadow-xs bg-emerald-50 text-emerald-800 border-emerald-200";
                networkDot.className = "w-2 h-2 rounded-full mr-1.5 bg-emerald-500 animate-pulse";
                networkText.textContent = "Online";
            } else {
                networkBadge.className = "inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold transition-all border shadow-xs bg-amber-100 text-amber-900 border-amber-300";
                networkDot.className = "w-2 h-2 rounded-full mr-1.5 bg-amber-500";
                networkText.textContent = "Offline (Sin conexión)";
            }
            updatePendingBadge();
        }

        window.addEventListener('online', function() {
            updateNetworkStatus();
            showAlert('success', '¡Conexión Restablecida!', 'Se ha detectado conexión a internet. Iniciando sincronización automática de registros pendientes...');
            syncOfflineQueue();
        });

        window.addEventListener('offline', function() {
            updateNetworkStatus();
            showAlert('warning', 'Modo Offline Activo', 'No hay conexión a internet. Los nuevos registros se guardarán localmente en el navegador y se sincronizarán al recuperar la red.');
        });

        updateNetworkStatus();

        // ==========================================
        // 3. MANEJO DE COLA EN LOCALSTORAGE
        // ==========================================
        function getOfflineQueue() {
            try {
                const stored = localStorage.getItem(STORAGE_KEY);
                return stored ? JSON.parse(stored) : [];
            } catch (e) {
                console.error('Error al leer la cola offline:', e);
                return [];
            }
        }

        function saveOfflineQueue(queue) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(queue));
            } catch (e) {
                console.error('Error al guardar en la cola offline:', e);
            }
            updatePendingBadge();
        }

        function updatePendingBadge() {
            const queue = getOfflineQueue();
            if (queue.length > 0) {
                pendingBadge.classList.remove('hidden');
                pendingCount.textContent = queue.length;
            } else {
                pendingBadge.classList.add('hidden');
                pendingCount.textContent = '0';
            }
        }

        function showAlert(type, title, message, details = null) {
            if (!alertBanner) return;

            alertBanner.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-900',
                'bg-amber-50', 'border-amber-200', 'text-amber-900', 'bg-rose-50', 'border-rose-200', 'text-rose-900');

            if (type === 'success') {
                alertBanner.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-900');
                alertIcon.innerHTML = `<svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
            } else if (type === 'warning') {
                alertBanner.classList.add('bg-amber-50', 'border-amber-200', 'text-amber-900');
                alertIcon.innerHTML = `<svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>`;
            } else {
                alertBanner.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-900');
                alertIcon.innerHTML = `<svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
            }

            alertTitle.textContent = title;
            alertMessage.textContent = message;

            if (details) {
                alertDetails.classList.remove('hidden');
                alertDetails.textContent = details;
            } else {
                alertDetails.classList.add('hidden');
            }

            alertBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // ==========================================
        // 4. EXTRACCIÓN Y LIMPIEZA DEL FORMULARIO
        // ==========================================
        function extractFormData() {
            const formData = new FormData(form);
            const planAccion = [];
            
            const tbody = document.getElementById('plan-accion-tbody');
            if (tbody) {
                const rows = tbody.querySelectorAll('tr');
                rows.forEach((tr, index) => {
                    const actInput = tr.querySelector(`input[name="plan_accion[${index}][actividad]"]`) || tr.querySelector('input[name*="[actividad]"]');
                    const respInput = tr.querySelector(`input[name="plan_accion[${index}][responsable]"]`) || tr.querySelector('input[name*="[responsable]"]');
                    const fecInput = tr.querySelector(`input[name="plan_accion[${index}][fecha]"]`) || tr.querySelector('input[name*="[fecha]"]');

                    const actividad = actInput ? actInput.value.trim() : '';
                    const responsable = respInput ? respInput.value.trim() : '';
                    const fecha = fecInput ? fecInput.value : '';

                    if (actividad || responsable || fecha) {
                        planAccion.push({ actividad, responsable, fecha });
                    }
                });
            }

            return {
                id: 'atc_offline_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
                saved_at: new Date().toLocaleString(),
                correlativo: formData.get('correlativo') || formData.get('correlativo_display') || '',
                fecha: formData.get('fecha') || '',
                tipo: formData.get('tipo') || 'Reclamo',
                cliente_nombre: formData.get('cliente_nombre') || '',
                cliente_ruc: formData.get('cliente_ruc') || '',
                producto: formData.get('producto') || '',
                cantidad: formData.get('cantidad') || '0',
                lote: formData.get('lote') || '',
                descripcion: formData.get('descripcion') || '',
                pnc_id: formData.get('pnc_id') || '',
                accion_correctiva: formData.get('accion_correctiva') || '',
                plan_accion: planAccion
            };
        }

        function resetFormFields() {
            form.reset();
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('#select-cliente-sif').val(null).trigger('change');
            }
            if (nombreInput) nombreInput.value = '';
            if (rucInput) rucInput.value = '';

            const tbody = document.getElementById('plan-accion-tbody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td class="py-2 px-3 text-center font-bold text-gray-400 row-num">1</td>
                        <td class="py-2 px-3">
                            <input type="text" name="plan_accion[0][actividad]" placeholder="Describa la actividad a realizar..." 
                                   class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                        </td>
                        <td class="py-2 px-3">
                            <input type="text" name="plan_accion[0][responsable]" placeholder="Nombre del responsable..." 
                                   class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                        </td>
                        <td class="py-2 px-3">
                            <input type="date" name="plan_accion[0][fecha]" 
                                   class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                        </td>
                        <td class="py-2 px-3 text-center">
                            <button type="button" class="btn-remove-row text-rose-500 hover:text-rose-700 p-1 rounded-lg hover:bg-rose-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                `;
            }
        }

        // ==========================================
        // 5. INTERCEPTACIÓN DE SUBMIT DEL FORMULARIO
        // ==========================================
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!navigator.onLine) {
                    e.preventDefault();
                    handleOfflineSave();
                    return;
                }

                e.preventDefault();
                submitOnlineAsync();
            });
        }

        function handleOfflineSave() {
            const record = extractFormData();

            if (!record.cliente_nombre || !record.producto || !record.cantidad || !record.descripcion) {
                showAlert('warning', 'Campos Obligatorios Incompletos', 'Por favor complete todos los campos marcados con (*) antes de guardar.');
                return;
            }

            const queue = getOfflineQueue();
            queue.push(record);
            saveOfflineQueue(queue);

            showAlert(
                'warning',
                '¡El registro se guardó localmente y se sincronizará cuando vuelva el internet!',
                `El reporte ATC ${record.correlativo} para "${record.cliente_nombre}" fue almacenado en el navegador sin pérdida de datos.`,
                `Correlativo: ${record.correlativo} | Cliente: ${record.cliente_nombre} | Guardado: ${record.saved_at}`
            );

            resetFormFields();
        }

        async function submitOnlineAsync() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const formData = new FormData(form);
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                if (response.ok) {
                    const data = await response.json().catch(() => ({}));
                    showAlert('success', '¡Registro Exitoso!', data.message || 'El registro ATC se ha guardado correctamente en el servidor.');
                    resetFormFields();
                    setTimeout(() => {
                        window.location.href = "{{ route('atc.index') }}";
                    }, 1200);
                } else if (response.status === 422) {
                    const data = await response.json();
                    let errMsgs = [];
                    if (data.errors) {
                        Object.values(data.errors).forEach(errArray => {
                            errMsgs.push(...errArray);
                        });
                    }
                    showAlert('error', 'Error de Validación', data.message || 'Por favor verifique los datos del formulario.', errMsgs.join(' | '));
                } else {
                    throw new Error('Servidor retornó estado: ' + response.status);
                }
            } catch (err) {
                console.warn('Conexión inestable o fallo de red. Guardando offline...', err);
                handleOfflineSave();
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        }

        // ==========================================
        // 6. SINCRONIZACIÓN AUTOMÁTICA AL VOLVER EN LÍNEA
        // ==========================================
        async function syncOfflineQueue() {
            if (isSyncing) return;
            if (!navigator.onLine) return;

            const queue = getOfflineQueue();
            if (queue.length === 0) return;

            isSyncing = true;
            let successCount = 0;
            let failCount = 0;
            const remainingQueue = [];
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const storeUrl = "{{ route('atc.store') }}";

            showAlert('warning', 'Sincronizando Registros...', `Sincronizando ${queue.length} registro(s) pendiente(s) guardado(s) en modo offline...`);

            for (const item of queue) {
                try {
                    const bodyData = new FormData();
                    bodyData.append('_token', token);
                    bodyData.append('correlativo', item.correlativo);
                    bodyData.append('fecha', item.fecha);
                    bodyData.append('tipo', item.tipo);
                    bodyData.append('cliente_nombre', item.cliente_nombre);
                    bodyData.append('cliente_ruc', item.cliente_ruc || '');
                    bodyData.append('producto', item.producto);
                    bodyData.append('cantidad', item.cantidad);
                    bodyData.append('lote', item.lote || '');
                    bodyData.append('descripcion', item.descripcion);
                    if (item.pnc_id) bodyData.append('pnc_id', item.pnc_id);
                    if (item.accion_correctiva) bodyData.append('accion_correctiva', item.accion_correctiva);

                    if (Array.isArray(item.plan_accion)) {
                        item.plan_accion.forEach((pa, idx) => {
                            bodyData.append(`plan_accion[${idx}][actividad]`, pa.actividad || '');
                            bodyData.append(`plan_accion[${idx}][responsable]`, pa.responsable || '');
                            bodyData.append(`plan_accion[${idx}][fecha]`, pa.fecha || '');
                        });
                    }

                    const res = await fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: bodyData
                    });

                    if (res.ok || res.status === 201) {
                        successCount++;
                    } else {
                        console.error('Fallo al sincronizar item offline:', item, await res.text());
                        failCount++;
                        remainingQueue.push(item);
                    }
                } catch (networkErr) {
                    console.error('Conexión perdida durante la sincronización:', networkErr);
                    failCount++;
                    remainingQueue.push(item);
                    break;
                }
            }

            saveOfflineQueue(remainingQueue);
            isSyncing = false;

            if (successCount > 0) {
                showAlert(
                    'success',
                    '¡Sincronización Completada con Éxito!',
                    `Se sincronizaron correctamente ${successCount} registro(s) ATC guardado(s) offline con el servidor. ${failCount > 0 ? failCount + ' registro(s) continúan en cola para reintentar.' : ''}`
                );
            }
        }

        if (btnForceSync) {
            btnForceSync.addEventListener('click', function () {
                if (!navigator.onLine) {
                    showAlert('warning', 'Sin Conexión', 'No hay conexión a internet activa para realizar la sincronización.');
                    return;
                }
                syncOfflineQueue();
            });
        }

        // Verificación periódica cada 15s si hay conexión y registros pendientes
        setInterval(function() {
            if (navigator.onLine && getOfflineQueue().length > 0) {
                syncOfflineQueue();
            }
        }, 15000);

        // Auto sincronizar al cargar la página si existen pendientes y conexión activa
        if (navigator.onLine && getOfflineQueue().length > 0) {
            setTimeout(syncOfflineQueue, 1000);
        }

        // ==========================================
        // 7. LÓGICA DE FILAS DINÁMICAS (PLAN DE ACCIÓN)
        // ==========================================
        let planRowIdx = 1;
        const addBtn = document.getElementById('btn-add-plan-row');
        const tbody = document.getElementById('plan-accion-tbody');

        if (addBtn && tbody) {
            addBtn.addEventListener('click', function () {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="py-2 px-3 text-center font-bold text-gray-400 row-num">${tbody.children.length + 1}</td>
                    <td class="py-2 px-3">
                        <input type="text" name="plan_accion[${planRowIdx}][actividad]" placeholder="Describa la actividad a realizar..." 
                               class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                    </td>
                    <td class="py-2 px-3">
                        <input type="text" name="plan_accion[${planRowIdx}][responsable]" placeholder="Nombre del responsable..." 
                               class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                    </td>
                    <td class="py-2 px-3">
                        <input type="date" name="plan_accion[${planRowIdx}][fecha]" 
                               class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                    </td>
                    <td class="py-2 px-3 text-center">
                        <button type="button" class="btn-remove-row text-rose-500 hover:text-rose-700 p-1 rounded-lg hover:bg-rose-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
                planRowIdx++;
                updateRowNumbers();
            });

            tbody.addEventListener('click', function (e) {
                const btn = e.target.closest('.btn-remove-row');
                if (btn) {
                    if (tbody.children.length > 1) {
                        btn.closest('tr').remove();
                        updateRowNumbers();
                    }
                }
            });

            function updateRowNumbers() {
                Array.from(tbody.children).forEach((tr, i) => {
                    const numCell = tr.querySelector('.row-num');
                    if (numCell) numCell.textContent = i + 1;
                });
            }
        }
    });
</script>
@endsection
