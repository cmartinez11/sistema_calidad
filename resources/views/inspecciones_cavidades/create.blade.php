@extends('layouts.app')

@section('content')
<div x-data="{
    moldeId:'',
    selectedProductoId: '{{ old('producto_id', $selectedProductoId ?? '') }}',
    maquinaId: '{{ old('maquina_id', '') }}',
    operarioId: '{{ old('operario_id', '') }}',
    turnoId: '{{ old('turno_id', '') }}',
    resinaId: '{{ old('resina_id', '') }}',
    
    loading: false,
    productoNombre: '',
    productoCodigo: '',
    numeroCavidades: 0,
    
    pesoNominal: 0,
    pesoMin: 0,
    pesoMax: 0,
    espesorParedMin: 0, 
    espesorParedMax: 0,
    espesorFondoMin: 0, 
    espesorFondoMax: 0,
    alturaMin: 0, 
    alturaMax: 0,
    
    cavidades: [],

    // Estado del Modal Excel
    showExcelModal: false,
    excelFile: null,
    uploadingExcel: false,
    excelError: '',
    excelSuccess: '',

    init() {
        if (this.selectedProductoId) {
            this.cargarParametros();
        }
    },

    async cargarParametros() {
        if (!this.selectedProductoId) {
            this.cavidades = [];
            this.numeroCavidades = 0;
            return;
        }

        this.loading = true;
        try {
            let response = await fetch(`/productos/${this.selectedProductoId}/parametros-json`);
            if (!response.ok) throw new Error('Error al cargar parámetros del producto');

            let data = await response.json();
            this.productoCodigo = data.codigo || '';
            this.productoNombre = data.nombre || '';
            this.numeroCavidades = data.numero_cavidades || 1;
            this.pesoNominal = parseFloat(data.peso_nominal || 0);
            this.pesoMin = parseFloat(data.peso_min || 0);
            this.pesoMax = parseFloat(data.peso_max || 0);

            this.espesorParedMin = parseFloat(data.espesor_pared_min || 0);
            this.espesorParedMax = parseFloat(data.espesor_pared_max || 0);

            this.espesorFondoMin = parseFloat(data.espesor_fondo_min || 0);
            this.espesorFondoMax = parseFloat(data.espesor_fondo_max || 0);

            this.alturaMin = parseFloat(data.altura_min || 0);
            this.alturaMax = parseFloat(data.altura_max || 0);

            let newCavidades = [];
            for (let i = 1; i <= this.numeroCavidades; i++) {
                newCavidades.push({
                    cavidad_numero: i,
                    peso_medido: '',
                    espesor_pared: '',
                    espesor_fondo: '',
                    altura: '',
                    tiene_defecto: false,
                    es_pasable: false,
                    anulada: false,
                    estado: 'CONFORME',
                    motivo_scrap: '',
                    observaciones: ''
                });
            }
            this.cavidades = newCavidades;
        } catch (e) {
            console.error(e);
            alert('No se pudieron obtener los parámetros del producto seleccionado.');
        } finally {
            this.loading = false;
        }
    },

    actualizarCavidadesMolde() {
        let select = document.getElementById('molde_id');
        let option = select.options[select.selectedIndex];
        let cavidadesCount = parseInt(option.getAttribute('data-cavidades')) || 0;
        
        if (cavidadesCount > 0) {
            this.numeroCavidades = cavidadesCount;

            let newCavidades = [];
            for (let i = 1; i <= this.numeroCavidades; i++) {
                newCavidades.push({
                    cavidad_numero: i,
                    peso_medido: '',
                    espesor_pared: '',
                    espesor_fondo: '',
                    altura: '',
                    tiene_defecto: false,
                    es_pasable: false,
                    anulada: false,
                    estado: 'CONFORME',
                    motivo_scrap: '',
                    observaciones: ''
                });
            }
            this.cavidades = newCavidades;
        }
    },

    evaluarCavidad(cav) {
        if (cav.anulada) {
            cav.estado = 'ANULADO';
            cav.motivo_scrap = '';
            cav.tiene_defecto = false;
            cav.es_pasable = false;
            return;
        }

        if (cav.tiene_defecto) {
            if (cav.es_pasable) {
                cav.estado = 'PASABLE';
            } else {
                cav.estado = 'OBSERVADO';
            }
            return;
        } else {
            cav.es_pasable = false;
        } 
        
        let peso = parseFloat(cav.peso_medido);
        let pared = parseFloat(cav.espesor_pared);
        let fondo = parseFloat(cav.espesor_fondo);
        let alt = parseFloat(cav.altura);
        
        let vacio = (isNaN(peso) || cav.peso_medido === '') && 
                    (isNaN(pared) || cav.espesor_pared === '') && 
                    (isNaN(fondo) || cav.espesor_fondo === '') && 
                    (isNaN(alt) || cav.altura === '');

        if (vacio) {
            cav.estado = 'CONFORME';
            cav.motivo_scrap = '';
            return;
        }

        let fueraPeso = (this.pesoMin > 0 && this.pesoMax > 0 && !isNaN(peso) && cav.peso_medido !== '' && (peso < this.pesoMin || peso > this.pesoMax));
        let fueraPared = (this.espesorParedMin > 0 && this.espesorParedMax > 0 && !isNaN(pared) && cav.espesor_pared !== '' && (pared < this.espesorParedMin || pared > this.espesorParedMax));
        let fueraFondo = (this.espesorFondoMin > 0 && this.espesorFondoMax > 0 && !isNaN(fondo) && cav.espesor_fondo !== '' && (fondo < this.espesorFondoMin || fondo > this.espesorFondoMax));
        let fueraAltura = (this.alturaMin > 0 && this.alturaMax > 0 && !isNaN(alt) && cav.altura !== '' && (alt < this.alturaMin || alt > this.alturaMax));

        if (fueraPeso || fueraPared || fueraFondo || fueraAltura) {
            cav.estado = 'FUERA_DE_RANGO';
        } else {
            cav.estado = 'CONFORME';
            cav.motivo_scrap = '';
        }
    },

    async subirExcel() {
        if (!this.excelFile) {
            this.excelError = 'Por favor selecciona un archivo Excel (.xlsx, .xls) o CSV.';
            return;
        }

        this.uploadingExcel = true;
        this.excelError = '';
        this.excelSuccess = '';

        let formData = new FormData();
        formData.append('excel_file', this.excelFile);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            let response = await fetch('{{ route('inspecciones-cavidades.procesar-excel') }}', {
                method: 'POST',
                body: formData
            });

            let result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Error al procesar el archivo');
            }

            let importedList = result.cavidades;

            // Si no hay cavidades seleccionadas aún, ajustar el número de cavidades al total importado
            if (this.cavidades.length < importedList.length) {
                this.numeroCavidades = importedList.length;
                let newCavidades = [];
                for (let i = 1; i <= this.numeroCavidades; i++) {
                    newCavidades.push({
                        cavidad_numero: i,
                        peso_medido: '',
                        espesor_pared: '',
                        espesor_fondo: '',
                        altura: '',
                        tiene_defecto: false,
                        es_pasable: false,
                        anulada: false,
                        estado: 'CONFORME',
                        motivo_scrap: '',
                        observaciones: ''
                    });
                }
                this.cavidades = newCavidades;
            }

            // Mapear cada fila leída e invocar la evaluación de tolerancias
            importedList.forEach(item => {
                let targetIndex = this.cavidades.findIndex(c => c.cavidad_numero === item.cavidad_numero);
                if (targetIndex !== -1) {
                    let cav = this.cavidades[targetIndex];
                    if (item.peso_medido !== '') cav.peso_medido = item.peso_medido;
                    if (item.espesor_pared !== '') cav.espesor_pared = item.espesor_pared;
                    if (item.espesor_fondo !== '') cav.espesor_fondo = item.espesor_fondo;
                    if (item.altura !== '') cav.altura = item.altura;
                    if (item.tiene_defecto) cav.tiene_defecto = true;
                    if (item.observaciones) cav.observaciones = item.observaciones;

                    // Evaluación automática contra las tolerancias de producto
                    this.evaluarCavidad(cav);
                }
            });

            this.excelSuccess = `¡Excelente! Se importaron datos de ${result.count} cavidades. Evaluación de tolerancias aplicada.`;
            setTimeout(() => {
                this.showExcelModal = false;
                this.excelFile = null;
                this.excelSuccess = '';
            }, 1200);

        } catch (e) {
            this.excelError = e.message;
        } finally {
            this.uploadingExcel = false;
        }
    },

    get conformesCount() {
        return this.cavidades.filter(c => c.estado === 'CONFORME' && c.peso_medido !== '').length;
    },

    get fueraDeRangoCount() {
        return this.cavidades.filter(c => c.estado === 'FUERA_DE_RANGO').length;
    },

    get observadoCount(){
        return this.cavidades.filter(c => c.estado === 'OBSERVADO').length;
    },

    get pasablesCount(){
        return this.cavidades.filter(c => c.estado === 'PASABLE').length;
    },

    get anuladasCount(){
        return this.cavidades.filter(c => c.estado === 'ANULADO').length;
    },

    get promedioPeso() {
        let medidos = this.cavidades.filter(c => c.estado !== 'ANULADO' && !isNaN(parseFloat(c.peso_medido)) && c.peso_medido !== '');
        if (medidos.length === 0) return '0.00';
        let suma = medidos.reduce((acc, c) => acc + parseFloat(c.peso_medido), 0);
        return (suma / medidos.length).toFixed(2);
    }
}" class="space-y-6">

    <!-- ALERTAS FLASH -->
    @if(session('success'))
        <div class="p-4 bg-green-100 border-l-4 border-fenix text-fenix-dark rounded-r-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-fenix" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
    @endif

    <!-- CABECERA PRINCIPAL DE REGISTRO -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Inspección Cavidad por Cavidad</h2>
                <p class="text-xs text-gray-400 mt-1">Captura de pesos unitarios cavidad por cavidad con validación automática de tolerancias y scrap</p>
            </div>
            
            <div class="flex items-center space-x-3">
                <!-- Botón Subir Excel de Medición -->
                <button type="button" @click="showExcelModal = true"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-md transition-all flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>📊 Subir Excel de Medición</span>
                </button>

                <a href="{{ route('inspecciones-cavidades.index') }}" 
                   class="text-xs font-bold text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-xl transition-all">
                    ← Volver al Listado
                </a>
            </div>
        </div>

        <!-- FORMULARIO CABECERA -->
        <form action="{{ route('inspecciones-cavidades.store') }}" method="POST" class="mt-6 space-y-6">
            @csrf

            <!-- FILA 1 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Selector de Producto Buscable con Alpine.js -->
                <div x-data="{
                    open: false,
                    search: '',
                    productosList: [
                        @foreach($productos as $prod)
                            {
                                id: '{{ $prod->id }}',
                                text: '{{ $prod->codigo }} - {{ $prod->nombre }} ({{ $prod->parametroPreforma->numero_cavidades ?? 1 }} Cavidades)',
                                searchKey: '{{ strtolower($prod->codigo . ' ' . $prod->nombre . ' ' . ($prod->parametroPreforma->numero_cavidades ?? 1)) }}'
                            },
                        @endforeach
                    ],
                    get filteredProductos() {
                        if (this.search === '') return this.productosList;
                        return this.productosList.filter(p => p.searchKey.includes(this.search.toLowerCase()));
                    },
                    selectedText: '{{ $selectedProductoId ? $productos->firstWhere('id', $selectedProductoId)?->codigo . ' - ' . $productos->firstWhere('id', $selectedProductoId)?->nombre : '-- Seleccionar Producto --' }}',
                    selectProduct(prod) {
                        this.selectedProductoId = prod.id;
                        this.selectedText = prod.text;
                        this.open = false;
                        this.search = '';
                        this.cargarParametros();
                    }
                }" @click.away="open = false">
                    
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Producto a Inspeccionar *</label>
                    <input type="hidden" name="producto_id" x-model="selectedProductoId" required>

                    <div class="relative">
                        <div @click="open = !open" 
                             class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm font-semibold bg-gray-50/50 cursor-pointer flex items-center justify-between focus:ring-1 focus:ring-fenix">
                            <span x-text="selectedText" class="truncate text-gray-800"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        <div x-show="open" x-cloak class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-hidden flex flex-col">
                            <div class="p-2 border-b border-gray-100 bg-gray-50">
                                <input type="text" x-model="search" placeholder="Escribe para buscar código, nombre o cavidades..." 
                                       class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-fenix font-medium">
                            </div>

                            <div class="overflow-y-auto max-h-48 divide-y divide-gray-50">
                                <template x-for="prod in filteredProductos" :key="prod.id">
                                    <div @click="selectProduct(prod)" 
                                         class="px-3.5 py-2 text-xs text-gray-700 hover:bg-fenix/10 hover:text-fenix cursor-pointer transition-colors font-medium"
                                         x-text="prod.text">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selector de Inyectora / Máquina -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Máquina / Inyectora</label>
                    <select name="maquina_id" x-model="maquinaId"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                        <option value="">-- Opcional --</option>
                        @foreach($maquinas as $maq)
                            <option value="{{ $maq->id }}">{{ $maq->codigo }} - {{ $maq->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Selector de Molde -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Molde *</label>
                    <select name="molde_id" id="molde_id" x-model="moldeId" @change="actualizarCavidadesMolde()" required
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix font-semibold">
                        <option value="">-- Seleccionar Molde --</option>
                        @foreach($moldes as $molde)
                            <option value="{{ $molde->id }}" data-cavidades="{{ $molde->numero_cavidades }}">
                                {{ $molde->codigo }} - {{ $molde->nombre }} ({{ $molde->numero_cavidades }} Cavidades)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- FILA 2 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Selector de Resina -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Resina</label>
                    <select name="resina_id" x-model="resinaId"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                        <option value="">-- Opcional --</option>
                        @foreach($resinas as $res)
                            <option value="{{ $res->id }}">{{ $res->codigo }} - {{ $res->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Selector de Operario / Encargado -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Operario / Encargado</label>
                    <select name="operario_id" x-model="operarioId"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                        <option value="">-- Opcional --</option>
                        @foreach($operarios as $ope)
                            <option value="{{ $ope->id }}">{{ $ope->nombre }} {{ $ope->codigo_operario ? '('.$ope->codigo_operario.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Turno de Trabajo -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Turno de Trabajo</label>
                    <select name="turno_id" x-model="turnoId"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                        <option value="">-- Opcional --</option>
                        @foreach($turnos as $tur)
                            <option value="{{ $tur->id }}">{{ $tur->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- FILA 3: Resumen de Parámetros del Producto -->
            <div class="bg-emerald-50/70 border border-emerald-200 p-4 rounded-xl flex items-center justify-between">
                <div>
                    <span class="text-[11px] text-emerald-800 font-bold uppercase tracking-wider block">PARÁMETROS DE TOLERANCIA DEL PRODUCTO - <strong class="text-emerald-950 font-bold" x-text="productoNombre"></strong></span>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-1 text-xs font-mono">
                        <div><span class="text-gray-600 block">Peso:</span> <span class="font-bold text-emerald-900" x-text="pesoMin > 0 ? (pesoMin + 'g - ' + pesoMax + 'g') : 'Sin límite'"></span></div>
                        <div><span class="text-gray-600 block">Pared:</span> <span class="font-bold text-emerald-900" x-text="espesorParedMin > 0 ? (espesorParedMin + 'mm - ' + espesorParedMax + 'mm') : 'Sin límite'"></span></div>
                        <div><span class="text-gray-600 block">Fondo:</span> <span class="font-bold text-emerald-900" x-text="espesorFondoMin > 0 ? (espesorFondoMin + 'mm - ' + espesorFondoMax + 'mm') : 'Sin límite'"></span></div>
                        <div><span class="text-gray-600 block">Altura:</span> <span class="font-bold text-emerald-900" x-text="alturaMin > 0 ? (alturaMin + 'mm - ' + alturaMax + 'mm') : 'Sin límite'"></span></div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-[11px] text-emerald-800 font-bold uppercase tracking-wider block">Molde</span>
                    <span class="text-xs font-bold text-emerald-900 font-mono" x-text="numeroCavidades + ' Cavidades'"></span>
                </div>
            </div>


            <!-- SECCIÓN: TABLA INTERACTIVA DE CAVIDADES DEL MOLDE -->
            <div class="pt-4 border-t border-gray-100 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-800">Grilla de Medición Cavidad por Cavidad</h3>
                    <div class="flex flex-wrap items-center gap-3 text-xs font-medium text-gray-600">
                        <span>Total: <strong class="text-gray-800 font-mono" x-text="numeroCavidades"></strong></span>
                        <span class="text-green-600 font-bold">🟢 Conformes: <span x-text="conformesCount"></span></span>
                        <span class="text-red-600 font-bold">🔴 Fuera Rango: <span x-text="fueraDeRangoCount"></span></span>
                        <span class="text-amber-600 font-bold">🟠 Observados: <span x-text="observadoCount"></span></span>
                        <span class="text-purple-600 font-bold">🟠 Pasables: <span x-text="pasablesCount"></span></span>
                        <span class="text-gray-500 font-bold">⚪ Anuladas: <span x-text="anuladasCount"></span></span>
                        <span>Promedio: <strong class="text-fenix font-mono font-bold" x-text="promedioPeso + ' g'"></strong></span>
                    </div>
                </div>

                <!-- SPINNER CARGANDO -->
                <div x-show="loading" x-cloak class="py-12 text-center space-y-2">
                    <svg class="animate-spin h-8 w-8 text-fenix mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm font-medium text-gray-500">Cargando parámetros del producto y cavidades...</p>
                </div>

                <!-- MENSAJE SIN PRODUCTO SELECCIONADO -->
                <div x-show="!selectedProductoId && !loading" class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl py-12 text-center text-gray-400">
                    <span class="text-4xl">🧪</span>
                    <p class="text-sm font-semibold text-gray-600 mt-2">Selecciona un producto arriba o sube un archivo Excel para cargar la grilla de cavidades</p>
                </div>

                <!-- TABLA DE MEDIDAS -->
                <div x-show="selectedProductoId && !loading" class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100 border-b border-gray-200 text-gray-700 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-3 py-3 text-center w-20">N° Cavidad</th>
                                    <th class="px-3 py-3 w-28">Peso (g) *</th>
                                    <th class="px-3 py-3 w-28">Esp. Pared (mm)</th>
                                    <th class="px-3 py-3 w-28">Esp. Fondo (mm)</th>
                                    <th class="px-3 py-3 w-28">Altura (mm)</th>
                                    <th class="px-3 py-3 text-center w-28">¿Tiene Defecto?</th>
                                    <th class="px-3 py-3 text-center w-36">Estado</th>
                                    <th class="px-3 py-3 w-44">Motivo de Scrap / Defecto</th>
                                    <th class="px-3 py-3 w-56">Observaciones</th>
                                    <th class="px-3 py-3 text-center w-32 bg-gray-200/70 text-gray-800 font-extrabold">ANULAR CAVIDAD</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(cav, index) in cavidades" :key="cav.cavidad_numero">
                                    <tr :class="cav.anulada ? 'bg-gray-100/90 border-l-4 border-l-gray-400 opacity-60' : ((cav.estado === 'FUERA_DE_RANGO' || cav.estado === 'OBSERVADO') ? 'bg-red-50/90 border-l-4 border-l-red-500' : (cav.estado === 'PASABLE' ? 'bg-amber-50/90 border-l-4 border-l-amber-500' : 'hover:bg-gray-50/80'))" class="transition-colors">
                                        
                                        <!-- N° Cavidad -->
                                        <td class="px-3 py-3 text-center font-bold font-mono text-gray-800">
                                            <input type="hidden" :name="'cavidades[' + index + '][cavidad_numero]'" :value="cav.cavidad_numero">
                                            <span class="bg-gray-200/80 px-2 py-1 rounded-lg border border-gray-300" x-text="'C-' + String(cav.cavidad_numero).padStart(2, '0')"></span>
                                        </td>

                                        <!-- Peso Real -->
                                        <td class="px-3 py-3">
                                            <input type="number" step="0.01" 
                                                   :name="'cavidades[' + index + '][peso_medido]'" 
                                                   x-model="cav.peso_medido" 
                                                   @input="evaluarCavidad(cav)"
                                                   placeholder="Ej. 23.10"
                                                   :required="!cav.anulada"
                                                   :disabled="cav.anulada"
                                                   class="w-full px-2 py-1.5 border border-gray-300 rounded-xl text-xs font-mono font-bold text-gray-900 disabled:bg-gray-200 disabled:cursor-not-allowed">
                                        </td>

                                        <!-- Espesor de Pared -->
                                        <td class="px-3 py-3">
                                            <input type="number" step="0.001" 
                                                   :name="'cavidades[' + index + '][espesor_pared]'" 
                                                   x-model="cav.espesor_pared" 
                                                   @input="evaluarCavidad(cav)"
                                                   placeholder="Ej. 2.50"
                                                   :disabled="cav.anulada"
                                                   class="w-full px-2 py-1.5 border border-gray-300 rounded-xl text-xs font-mono disabled:bg-gray-200 disabled:cursor-not-allowed">
                                        </td>

                                        <!-- Espesor de Fondo -->
                                        <td class="px-3 py-3">
                                            <input type="number" step="0.001" 
                                                   :name="'cavidades[' + index + '][espesor_fondo]'" 
                                                   x-model="cav.espesor_fondo" 
                                                   @input="evaluarCavidad(cav)"
                                                   placeholder="Ej. 3.10"
                                                   :disabled="cav.anulada"
                                                   class="w-full px-2 py-1.5 border border-gray-300 rounded-xl text-xs font-mono disabled:bg-gray-200 disabled:cursor-not-allowed">
                                        </td>

                                        <!-- Altura -->
                                        <td class="px-3 py-3">
                                            <input type="number" step="0.01" 
                                                   :name="'cavidades[' + index + '][altura]'" 
                                                   x-model="cav.altura" 
                                                   @input="evaluarCavidad(cav)"
                                                   placeholder="Ej. 150.0"
                                                   :disabled="cav.anulada"
                                                   class="w-full px-2 py-1.5 border border-gray-300 rounded-xl text-xs font-mono disabled:bg-gray-200 disabled:cursor-not-allowed">
                                        </td>

                                        <!-- Checkbox TIENE DEFECTO? -->
                                        <td class="px-3 py-3 text-center">
                                            <input type="checkbox" 
                                                   x-model="cav.tiene_defecto" 
                                                   @change="evaluarCavidad(cav)"
                                                   :disabled="cav.anulada"
                                                   class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500 cursor-pointer disabled:cursor-not-allowed">
                                        </td>

                                        <!-- Estado y Check ¿Es Pasable? -->
                                        <td class="px-3 py-3 text-center whitespace-nowrap">
                                            <input type="hidden" :name="'cavidades[' + index + '][estado]'" :value="cav.estado">
                                            
                                            <div class="flex items-center justify-center space-x-2">
                                                <template x-if="cav.estado === 'CONFORME'">
                                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold rounded-full border border-green-200">Conforme</span>
                                                </template>
                                                
                                                <template x-if="cav.estado === 'FUERA_DE_RANGO'">
                                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-full border border-red-200">Fuera Rango</span>
                                                </template>

                                                <template x-if="cav.estado === 'OBSERVADO'">
                                                    <span class="px-2 py-0.5 bg-red-600 text-white text-[10px] font-bold rounded-full">Observado</span>
                                                </template>

                                                <template x-if="cav.estado === 'PASABLE'">
                                                    <span class="px-2 py-0.5 bg-amber-500 text-white text-[10px] font-bold rounded-full">Pasable</span>
                                                </template>

                                                <template x-if="cav.estado === 'ANULADO'">
                                                    <span class="px-2 py-0.5 bg-gray-200 text-gray-700 text-[10px] font-bold rounded-full border border-gray-300">⚪ Anulado</span>
                                                </template>

                                                <template x-if="!cav.anulada && cav.tiene_defecto">
                                                    <label class="inline-flex items-center space-x-1 cursor-pointer bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                                        <input type="checkbox" x-model="cav.es_pasable" @change="evaluarCavidad(cav)" class="w-4 h-4 text-amber-600 rounded">
                                                        <span class="text-[10px] font-bold text-amber-800">¿Pasable?</span>
                                                    </label>
                                                </template>
                                            </div>
                                        </td>
                                        
                                        <!-- Motivo de Scrap -->
                                        <td class="px-3 py-3">
                                            <div x-show="!cav.anulada && (cav.estado === 'FUERA_DE_RANGO' || cav.estado === 'OBSERVADO' || cav.estado === 'PASABLE')" x-cloak>
                                                <select :name="'cavidades[' + index + '][motivo_scrap]'" 
                                                        x-model="cav.motivo_scrap"
                                                        :disabled="cav.anulada"
                                                        :required="!cav.anulada && (cav.estado === 'FUERA_DE_RANGO' || cav.estado === 'OBSERVADO' || cav.estado === 'PASABLE')"
                                                        class="w-full px-2 py-1.5 border border-red-300 rounded-xl text-xs font-semibold text-red-800 bg-red-100/60 disabled:cursor-not-allowed">
                                                    <option value="">-- Defecto * --</option>
                                                    <option value="Puntos negros">Puntos negros</option>
                                                    <option value="Quemados">Quemados</option>
                                                    <option value="Crudos">Crudos</option>
                                                    <option value="Flash">Flash</option>
                                                    <option value="Rebaba">Rebaba</option>
                                                    <option value="Variación de espesor">Variación de espesor</option>
                                                    <option value="Defecto visual / superficial">Defecto visual / superficial</option>
                                                    <option value="Otro">Otro defecto</option>
                                                </select>
                                            </div>
                                        </td>

                                        <!-- Observaciones / Justificación -->
                                        <td class="px-3 py-3">
                                            <input type="text" 
                                                   :name="'cavidades[' + index + '][observaciones]'" 
                                                   x-model="cav.observaciones" 
                                                   :placeholder="cav.anulada ? 'Justificación de anulación *' : 'Comentario opcional...'"
                                                   :required="cav.anulada"
                                                   class="w-full px-2 py-1.5 border rounded-xl text-xs transition-colors"
                                                   :class="cav.anulada ? 'border-gray-400 bg-yellow-50/80 font-medium text-gray-900 focus:ring-1 focus:ring-amber-500' : 'border-gray-300 bg-gray-50/50'">
                                        </td>

                                        <!-- ANULAR CAVIDAD -->
                                        <td class="px-3 py-3 text-center bg-gray-50/50">
                                            <label class="inline-flex items-center justify-center cursor-pointer">
                                                <input type="checkbox" 
                                                       x-model="cav.anulada" 
                                                       @change="evaluarCavidad(cav)"
                                                       class="w-4.5 h-4.5 text-gray-600 border-gray-300 rounded focus:ring-gray-500 cursor-pointer">
                                            </label>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- BOTÓN GUARDAR -->
            <div class="flex justify-end pt-4 border-t border-gray-100" x-show="selectedProductoId && !loading">
                <button type="submit" 
                        class="bg-fenix hover:bg-fenix-dark text-white px-8 py-3 rounded-xl font-bold text-sm shadow-lg hover:shadow-xl transition-all flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Guardar Auditoría de Calidad</span>
                </button>
            </div>

        </form>
    </div>

    <!-- MODAL DE SUBIDA MASIVA POR EXCEL -->
    <div x-show="showExcelModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg overflow-hidden transform transition-all">
            <!-- Cabecera Modal -->
            <div class="bg-gradient-to-r from-emerald-600 to-fenix p-5 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-white/20 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base">Carga Masiva de Mediciones (Excel / CSV)</h3>
                        <p class="text-xs text-white/80">Importa pesajes y dimensiones desde una plantilla formateada</p>
                    </div>
                </div>
                <button @click="showExcelModal = false" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <!-- Cuerpo Modal -->
            <div class="p-6 space-y-5">
                
                <!-- Botón Descargar Plantilla -->
                <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-900 block">¿No tienes la plantilla oficial?</span>
                        <span class="text-[11px] text-emerald-700">Descárgala para llenar las mediciones de tus cavidades.</span>
                    </div>
                    <a href="{{ route('inspecciones-cavidades.plantilla-excel') }}" 
                       class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow transition-all flex items-center space-x-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Descargar Plantilla</span>
                    </a>
                </div>

                <!-- Input File Dropzone -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Seleccionar Archivo (.xlsx, .xls, .csv) *</label>
                    <input type="file" @change="excelFile = $event.target.files[0]" accept=".xlsx, .xls, .csv"
                           class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs bg-gray-50 text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-fenix file:text-white hover:file:bg-fenix-dark cursor-pointer">
                </div>

                <!-- Mensaje de Error -->
                <div x-show="excelError" x-cloak class="p-3 bg-red-100 border border-red-200 text-red-700 text-xs font-semibold rounded-xl">
                    <span x-text="excelError"></span>
                </div>

                <!-- Mensaje de Éxito -->
                <div x-show="excelSuccess" x-cloak class="p-3 bg-green-100 border border-green-200 text-green-800 text-xs font-semibold rounded-xl flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="excelSuccess"></span>
                </div>

                <div class="text-[11px] text-gray-400 bg-gray-50 p-3 rounded-xl border border-gray-100">
                    💡 <strong>Nota de Tolerancias:</strong> Los estados de las cavidades no se leen del Excel. El sistema evaluará automáticamente las mediciones leídas contra las tolerancias del producto seleccionado.
                </div>
            </div>

            <!-- Pie Modal -->
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3">
                <button type="button" @click="showExcelModal = false"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold text-xs rounded-xl transition-all">
                    Cancelar
                </button>

                <button type="button" @click="subirExcel()" :disabled="uploadingExcel"
                        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center space-x-2 disabled:opacity-50">
                    <svg x-show="uploadingExcel" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="uploadingExcel ? 'Procesando...' : 'Cargar en la Grilla'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection