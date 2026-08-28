@extends('layouts.app')

@section('content')
<div x-data="{
    moldeId:'',
    selectedProductoId: '{{ old('producto_id', $selectedProductoId ?? '') }}',
    maquinaId: '{{ old('maquina_id', '') }}',
    operarioId: '{{ old('operario_id', '') }}',
    turnoId: '{{ old('turno_id', '') }}',
    
    loading: false,
    productoNombre: '',
    productoCodigo: '',
    numeroCavidades: 0,
    pesoNominal: 0,
    pesoMin: 0,
    pesoMax: 0,
    
    cavidades: [],

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

            // Generar grilla de cavidades del 1 al N
            let newCavidades = [];
            for (let i = 1; i <= this.numeroCavidades; i++) {
                newCavidades.push({
                    cavidad_numero: i,
                    peso_medido: '',
                    estado: 'CONFORME',
                    motivo_scrap: ''
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
        
        this.numeroCavidades = cavidadesCount;

        let newCavidades = [];
        for (let i = 1; i <= this.numeroCavidades; i++) {
            newCavidades.push({
                cavidad_numero: i,
                peso_medido: '',
                estado: 'CONFORME',
                motivo_scrap: ''
            });
        }
        this.cavidades = newCavidades;
    },

    evaluarCavidad(cavidad) {
        let peso = parseFloat(cavidad.peso_medido);
        
        if (isNaN(peso) || cavidad.peso_medido === '') {
            cavidad.estado = 'CONFORME';
            cavidad.motivo_scrap = '';
            return;
        }

        if (this.pesoMin > 0 && this.pesoMax > 0) {
            if (peso >= this.pesoMin && peso <= this.pesoMax) {
                cavidad.estado = 'CONFORME';
                cavidad.motivo_scrap = '';
            } else {
                cavidad.estado = 'FUERA_DE_RANGO';
            }
        } else {
            cavidad.estado = 'CONFORME';
        }
    },

    get conformesCount() {
        return this.cavidades.filter(c => c.estado === 'CONFORME' && c.peso_medido !== '').length;
    },

    get fueraDeRangoCount() {
        return this.cavidades.filter(c => c.estado === 'FUERA_DE_RANGO').length;
    },

    get promedioPeso() {
        let medidos = this.cavidades.filter(c => !isNaN(parseFloat(c.peso_medido)) && c.peso_medido !== '');
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
            
            <div class="flex items-center space-x-2">
                <span class="bg-fenix/10 text-fenix px-3 py-1.5 rounded-xl font-mono text-xs font-bold border border-fenix/20 flex items-center space-x-1">
                    <span>🧪</span>
                    <span>Control de Calidad</span>
                </span>
            </div>
        </div>

        <!-- FORMULARIO CABECERA -->
        <form action="{{ route('inspecciones-cavidades.store') }}" method="POST" class="mt-6 space-y-6">
            @csrf

            <input type="hidden" name="peso_min" :value="pesoMin">
            <input type="hidden" name="peso_max" :value="pesoMax">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Selector de Producto (Obligatorio) -->
                <!-- Selector de Producto Buscable con Alpine.js -->
                <div class="md:col-span-2" x-data="{
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
                    
                    <!-- Input oculto para enviar el ID correctamente en el formulario POST -->
                    <input type="hidden" name="producto_id" x-model="selectedProductoId" required>

                    <div class="relative">
                        <!-- Barra de selección visual -->
                        <div @click="open = !open" 
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm font-semibold bg-gray-50/50 cursor-pointer flex items-center justify-between focus:ring-1 focus:ring-fenix">
                            <span x-text="selectedText" class="truncate text-gray-800"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>

                        <!-- Desplegable con buscador interno -->
                        <div x-show="open" x-cloak class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-hidden flex flex-col">
                            <!-- Input de búsqueda en vivo -->
                            <div class="p-2 border-b border-gray-100 bg-gray-50">
                                <input type="text" x-model="search" placeholder="Escribe para buscar código, nombre o cavidades..." 
                                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-fenix font-medium">
                            </div>

                            <!-- Listado filtrado -->
                            <div class="overflow-y-auto max-h-48 divide-y divide-gray-50">
                                <template x-for="prod in filteredProductos" :key="prod.id">
                                    <div @click="selectProduct(prod)" 
                                        class="px-3.5 py-2 text-xs text-gray-700 hover:bg-fenix/10 hover:text-fenix cursor-pointer transition-colors font-medium"
                                        x-text="prod.text">
                                    </div>
                                </template>
                                <div x-show="filteredProductos.length === 0" class="p-3 text-center text-xs text-gray-400">
                                    No se encontraron productos coincidentes
                                </div>
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

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Molde *</label>
                    <select name="molde_id" id="molde_id" x-model="moldeId" @change="actualizarCavidadesMolde()" required
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                        <option value="">-- Seleccionar Molde --</option>
                        @foreach($moldes as $molde)
                            <option value="{{ $molde->id }}" data-cavidades="{{ $molde->numero_cavidades }}">
                                {{ $molde->codigo }} - {{ $molde->nombre }} ({{ $molde->numero_cavidades }} Cavidades)
                            </option>
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
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Turno -->
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

                <!-- Resumen de Parámetros de Producto -->
                <div class="md:col-span-2 bg-emerald-50/70 border border-emerald-200 p-3 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-emerald-800 font-bold uppercase tracking-wider block">Tolerancias de Peso</span>
                        <span class="text-xs font-mono font-bold text-emerald-900" x-text="pesoMin > 0 ? (pesoMin + 'g - ' + pesoMax + 'g (Nominal: ' + pesoNominal + 'g)') : 'Sin límites definidos'"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-[11px] text-emerald-800 font-bold uppercase tracking-wider block">Molde</span>
                        <span class="text-xs font-bold text-emerald-900 font-mono" x-text="numeroCavidades + ' Cavidades'"></span>
                    </div>
                </div>
            </div>


            <!-- SECCIÓN: TABLA INTERACTIVA DE CAVIDADES DEL MOLDE -->
            <div class="pt-4 border-t border-gray-100 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">Grilla de Medición Cavidad por Cavidad</h3>
                    <div class="flex items-center space-x-4 text-xs font-medium text-gray-500">
                        <span>Total Cavidades: <strong class="text-gray-800 font-mono" x-text="numeroCavidades"></strong></span>
                        <span class="text-green-600 font-bold">🟢 Conformes: <span x-text="conformesCount"></span></span>
                        <span class="text-red-600 font-bold">🔴 Fuera de Rango: <span x-text="fueraDeRangoCount"></span></span>
                        <span>Promedio: <strong class="text-fenix font-mono" x-text="promedioPeso + ' g'"></strong></span>
                    </div>
                </div>

                <!-- SPINNER CARGANDO -->
                <div x-show="loading" x-cloak class="py-12 text-center space-y-2">
                    <svg class="animate-spin h-8 w-8 text-fenix mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm font-medium text-gray-500">Cargando parámetros del producto y generando cavidades...</p>
                </div>

                <!-- MENSAJE SIN PRODUCTO SELECCIONADO -->
                <div x-show="!selectedProductoId && !loading" class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl py-12 text-center text-gray-400">
                    <span class="text-4xl">🧪</span>
                    <p class="text-sm font-semibold text-gray-600 mt-2">Selecciona un producto arriba para cargar la grilla de cavidades</p>
                </div>

                <!-- TABLA DE MEDIDAS -->
                <div x-show="selectedProductoId && !loading" class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100 border-b border-gray-200 text-gray-700 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-center w-24">N° Cavidad</th>
                                    <th class="px-4 py-3 w-48">Peso Real (Gramos) *</th>
                                    <th class="px-4 py-3 text-center w-36">Rango Permitido</th>
                                    <th class="px-4 py-3 text-center w-36">Estado</th>
                                    <th class="px-4 py-3">Motivo de Scrap / Defecto (Si no cumple)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(cav, index) in cavidades" :key="cav.cavidad_numero">
                                    <tr :class="cav.estado === 'FUERA_DE_RANGO' ? 'bg-red-50/90 border-l-4 border-l-red-500' : 'hover:bg-gray-50/80'" class="transition-colors">
                                        
                                        <!-- N° Cavidad -->
                                        <td class="px-4 py-3 text-center font-bold font-mono text-gray-800">
                                            <input type="hidden" :name="'cavidades[' + index + '][cavidad_numero]'" :value="cav.cavidad_numero">
                                            <span class="bg-gray-200/80 px-2.5 py-1 rounded-lg border border-gray-300" x-text="'Cavidad ' + String(cav.cavidad_numero).padStart(2, '0')"></span>
                                        </td>

                                        <!-- Peso Real -->
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" 
                                                   :name="'cavidades[' + index + '][peso_medido]'" 
                                                   x-model="cav.peso_medido" 
                                                   @input="evaluarCavidad(cav)"
                                                   placeholder="Ej. 28.10"
                                                   required
                                                   :class="cav.estado === 'FUERA_DE_RANGO' ? 'border-red-400 focus:border-red-600 focus:ring-red-500 font-bold text-red-900 bg-red-100/50' : 'border-gray-300 focus:border-fenix focus:ring-fenix font-bold text-gray-900'"
                                                   class="w-full px-3 py-1.5 border rounded-xl text-xs font-mono transition-all">
                                        </td>

                                        <!-- Rango Permitido -->
                                        <td class="px-4 py-3 text-center font-mono font-medium text-gray-500">
                                            <span x-text="pesoMin > 0 ? (pesoMin + ' - ' + pesoMax + ' g') : 'Global'"></span>
                                        </td>

                                        <!-- Estado -->
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <input type="hidden" :name="'cavidades[' + index + '][estado]'" :value="cav.estado">
                                            <template x-if="cav.estado === 'CONFORME'">
                                                <span class="px-2.5 py-1 bg-green-100 text-green-700 text-[11px] font-bold rounded-full border border-green-200 inline-flex items-center space-x-1">
                                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                                    <span>Conforme</span>
                                                </span>
                                            </template>
                                            <template x-if="cav.estado === 'FUERA_DE_RANGO'">
                                                <span class="px-2.5 py-1 bg-red-100 text-red-700 text-[11px] font-bold rounded-full border border-red-200 inline-flex items-center space-x-1 animate-pulse">
                                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                                    <span>Fuera de Rango</span>
                                                </span>
                                            </template>
                                        </td>

                                        <!-- Motivo de Scrap (Combobox oculto si cumple) -->
                                        <td class="px-4 py-3">
                                            <div x-show="cav.estado === 'FUERA_DE_RANGO'" x-cloak class="transition-all duration-200">
                                                <select :name="'cavidades[' + index + '][motivo_scrap]'" 
                                                        x-model="cav.motivo_scrap"
                                                        :required="cav.estado === 'FUERA_DE_RANGO'"
                                                        class="w-full px-3 py-1.5 border border-red-300 rounded-xl text-xs font-semibold text-red-800 bg-red-100/60 focus:outline-none focus:border-red-600 focus:ring-1 focus:ring-red-600">
                                                    <option value="">-- Seleccionar Motivo de Defecto * --</option>
                                                    <option value="Puntos negros">Puntos negros</option>
                                                    <option value="Quemados">Quemados</option>
                                                    <option value="Crudos">Crudos</option>
                                                    <option value="Flash">Flash</option>
                                                    <option value="Rebaba">Rebaba</option>
                                                    <option value="Delaminado">Delaminado</option>
                                                    <option value="Contaminado">Contaminado</option>
                                                    <option value="Rayaduras">Rayaduras</option>
                                                    <option value="Short shot">Short shot (Incompleto)</option>
                                                    <option value="Variación de color">Variación de color</option>
                                                    <option value="Deformado">Deformado</option>
                                                    <option value="Manchas">Manchas</option>
                                                    <option value="Burbujas / Porosidad">Burbujas / Porosidad</option>
                                                    <option value="Otro">Otro defecto</option>
                                                </select>
                                            </div>
                                            <div x-show="cav.estado !== 'FUERA_DE_RANGO'" class="text-gray-400 font-normal italic text-[11px]">
                                                Sin scrap (Cumple especificaciones)
                                            </div>
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
                    <span>Guardar Inspección por Cavidades</span>
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
