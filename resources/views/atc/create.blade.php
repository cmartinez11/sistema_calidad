@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <strong class="font-bold">¡Atención! Hay errores de validación:</strong>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header y Regreso -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('atc.index') }}" class="inline-flex items-center text-xs font-bold text-gray-500 hover:text-emerald-700 transition-colors mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver a la lista de ATC
            </a>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Nuevo Registro de Atención al Cliente (ATC)</h1>
            <p class="text-xs text-gray-500 font-medium">Formulario oficial para el registro de reclamos o devoluciones de clientes</p>
        </div>

        <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 px-4 py-2 rounded-xl text-right">
            <span class="block text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Correlativo Asignado</span>
            <span class="text-lg font-mono font-black">{{ $nextCorrelativo }}</span>
        </div>
    </div>

    <!-- Errores de Validación -->
    @if ($errors->any())
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm space-y-1">
            <div class="flex items-center space-x-2 text-rose-800 font-bold text-sm">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Por favor corrija los errores señalados:</span>
            </div>
            <ul class="list-disc list-inside text-xs text-rose-700 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('atc.store') }}" class="space-y-6">
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
                    <select id="sif_cliente_select" 
                            class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                        <option value="">-- Seleccionar cliente del sistema SIF --</option>
                        @foreach($clientesSif as $cli)
                            <option value="{{ $cli['nombre'] ?? '' }}" 
                                    data-ruc="{{ $cli['ruc'] ?? '' }}">
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
            <button type="submit" class="px-8 py-3 bg-fenix hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-lg hover:shadow-xl transition-all flex items-center space-x-2">
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
        const sifSelect = document.getElementById('sif_cliente_select');
        const nombreInput = document.getElementById('cliente_nombre');
        const rucInput = document.getElementById('cliente_ruc');

        if (sifSelect) {
            sifSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const nombre = selectedOption.value;
                const ruc = selectedOption.getAttribute('data-ruc') || '';

                if (nombre) {
                    nombreInput.value = nombre;
                    rucInput.value = ruc;
                }
            });
        }

        // Lógica para agregar y remover filas dinámicamente en el Plan de Acción
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
