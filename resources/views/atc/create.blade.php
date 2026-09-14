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
                    2. Cliente (Integración API SIF)
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
                    <input type="text" id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre') }}" required placeholder="Ej: EMBOTELLADORA DEL SUR S.A."
                           class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-900 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                </div>

                <!-- Cliente RUC -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">RUC del Cliente</label>
                    <input type="text" id="cliente_ruc" name="cliente_ruc" value="{{ old('cliente_ruc') }}" placeholder="Ej: 20123456789"
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
                <!-- Producto -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Producto <span class="text-rose-500">*</span></label>
                    <input type="text" list="productos_list" name="producto" value="{{ old('producto') }}" required placeholder="Escriba o seleccione el producto..."
                           class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                    <datalist id="productos_list">
                        @foreach($productos as $prod)
                            <option value="{{ $prod->nombre }} ({{ $prod->codigo }})"></option>
                        @endforeach
                    </datalist>
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
                4. Vinculación y Plan de Acción
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
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Acción Correctiva Implemented / Vinculación</label>
                    <textarea name="accion_correctiva" rows="4" placeholder="Detalle las acciones inmediatas o de contención acordadas..."
                              class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">{{ old('accion_correctiva') }}</textarea>
                </div>

                <!-- Plan de Acción -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Plan de Acción / Causa Raíz</label>
                    <textarea name="plan_accion" rows="4" placeholder="Detalle el plan de acción correctivo y preventivo..."
                              class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-900 focus:ring-2 focus:ring-fenix focus:bg-white transition-all">{{ old('plan_accion') }}</textarea>
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
    });
</script>
@endsection
