@extends('layouts.app')

@section('content')
<div x-data="{
    createModalOpen: {{ $errors->any() && !old('_method') ? 'true' : 'false' }},
    editModalOpen: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }},
    deleteModalOpen: false,
    importModalOpen: false,
    paramModalOpen: false,
    editProduct: {
        id: '{{ old('id') }}',
        codigo: '{{ old('codigo') }}',
        nombre: '{{ old('nombre') }}',
        tipo_producto: '{{ old('tipo_producto', 'PREFORMA') }}',
        unidad_medida: '{{ old('unidad_medida', 'UNIDADES') }}',
        peso_unitario: '{{ old('peso_unitario') }}',
        activo: {{ old('activo') ? 'true' : 'false' }}
    },
    paramProduct: {
        id: '',
        codigo: '',
        nombre: '',
        numero_cavidades: 0,
        peso_nominal: '',
        peso_min: '',
        peso_max: '',
        esp_pared_min: '',
        esp_pared_max: '',
        esp_fondo_min: '',
        esp_fondo_max: '',
        altura_min: '',
        altura_max: ''
    },
    editUrl: '',
    deleteUrl: '',
    paramUrl: ''
}" class="space-y-6">

    <!-- ALERTAS FLASH DE ÉXITO Y ERROR -->
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

    <!-- TARJETA SUPERIOR CABECERA -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Catálogo General de Productos</h2>
            <p class="text-xs text-gray-400 mt-1">Gestión genérica y flexible para líneas de Preforma, Termoformado y Laminados</p>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Botón Búsqueda -->
            <form method="GET" action="{{ route('productos.index') }}" class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por código, nombre, tipo..." 
                       class="pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix w-64 transition-all">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>

            <!-- Botón Subir Masivo -->
            @if(Auth::check() && Auth::user()->isAdmin())
                <button @click="importModalOpen = true" 
                        class="border-2 border-fenix text-fenix hover:bg-fenix hover:text-white px-4 py-2.5 rounded-xl font-semibold text-sm shadow-sm transition-all flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>Subir Masivo</span>
                </button>
            @endif

            <!-- Botón Nuevo Producto -->
            <button @click="createModalOpen = true" 
                    class="bg-fenix hover:bg-fenix-dark text-white px-5 py-2.5 rounded-xl font-medium text-sm shadow-md hover:shadow-lg transition-all flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Producto</span>
            </button>
        </div>
    </div>

    <!-- TABLA PRINCIPAL DE PRODUCTOS -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Código</th>
                        <th class="px-6 py-4">Nombre del Producto</th>
                        <th class="px-6 py-4 text-center">Tipo de Producto</th>
                        <th class="px-6 py-4 text-center">Unidad de Medida</th>
                        <th class="px-6 py-4 text-center">Peso Unitario</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($productos as $producto)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Código -->
                            <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">
                                <span class="bg-gray-100 text-gray-800 px-2.5 py-1 rounded-lg text-xs font-mono border border-gray-200">
                                    {{ $producto->codigo }}
                                </span>
                            </td>

                            <!-- Nombre -->
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $producto->nombre }}
                            </td>

                            <!-- Tipo de Producto -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($producto->tipo_producto === 'TERMO')
                                    <span class="px-3 py-1 bg-purple-100 text-purple-800 border border-purple-200 text-xs font-bold rounded-full inline-flex items-center space-x-1">
                                        <span>TERMOFORMADO</span>
                                    </span>
                                @elseif($producto->tipo_producto === 'LAMINADO')
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 border border-blue-200 text-xs font-bold rounded-full inline-flex items-center space-x-1">
                                        <span>LAMINADOS</span>
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold rounded-full inline-flex items-center space-x-1">
                                        <span>PREFORMA</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Unidad de Medida -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="bg-gray-100 text-gray-700 border border-gray-200 text-xs px-2.5 py-1 rounded-lg font-mono font-medium">
                                    {{ $producto->unidad_medida ?? 'UNIDADES' }}
                                </span>
                            </td>

                            <!-- Peso Unitario -->
                            <td class="px-6 py-4 text-center whitespace-nowrap font-mono font-bold text-fenix-dark">
                                @if($producto->peso_unitario)
                                    <span class="bg-green-50 text-fenix px-2.5 py-1 rounded-lg text-xs border border-green-200">
                                        {{ number_format($producto->peso_unitario, 1) }}
                                    </span>
                                @else
                                    <span class="text-gray-400 font-normal text-xs">-</span>
                                @endif
                            </td>

                            <!-- Estado -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($producto->activo)
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-200">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span> ACTIVO
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full border border-gray-200">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span> INACTIVO
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-1">
                                <!-- Botón Parámetros Técnicos (Preforma) -->
                                @if(($producto->tipo_producto ?? 'PREFORMA') === 'PREFORMA')
                                    <button @click="
                                                paramProduct = {
                                                    id: {{ $producto->id }},
                                                    codigo: '{{ e($producto->codigo) }}',
                                                    nombre: '{{ e($producto->nombre) }}',
                                                    numero_cavidades: '{{ $producto->parametroPreforma->numero_cavidades ?? 0 }}',
                                                    peso_nominal: '{{ $producto->parametroPreforma->peso_nominal ?? $producto->peso_unitario ?? '' }}',
                                                    peso_min: '{{ $producto->parametroPreforma->peso_min ?? '' }}',
                                                    peso_max: '{{ $producto->parametroPreforma->peso_max ?? '' }}',
                                                    esp_pared_min: '{{ $producto->parametroPreforma->esp_pared_min ?? '' }}',
                                                    esp_pared_max: '{{ $producto->parametroPreforma->esp_pared_max ?? '' }}',
                                                    esp_fondo_min: '{{ $producto->parametroPreforma->esp_fondo_min ?? '' }}',
                                                    esp_fondo_max: '{{ $producto->parametroPreforma->esp_fondo_max ?? '' }}',
                                                    altura_min: '{{ $producto->parametroPreforma->altura_min ?? '' }}',
                                                    altura_max: '{{ $producto->parametroPreforma->altura_max ?? '' }}'
                                                };
                                                paramUrl = '{{ route('productos.parametros.store', $producto->id) }}';
                                                paramModalOpen = true;
                                            " 
                                            class="p-2 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition-colors inline-flex items-center"
                                            title="Parámetros Técnicos de Preforma">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </button>
                                @endif

                                <!-- Botón Editar -->
                                <button @click="
                                            editProduct = {
                                                id: {{ $producto->id }},
                                                codigo: '{{ e($producto->codigo) }}',
                                                nombre: '{{ e($producto->nombre) }}',
                                                tipo_producto: '{{ e($producto->tipo_producto ?? 'PREFORMA') }}',
                                                unidad_medida: '{{ e($producto->unidad_medida ?? 'UNIDADES') }}',
                                                peso_unitario: '{{ $producto->peso_unitario }}',
                                                activo: {{ $producto->activo ? 'true' : 'false' }}
                                            };
                                            editUrl = '{{ route('productos.update', $producto->id) }}';
                                            editModalOpen = true;
                                        " 
                                        class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors inline-flex items-center"
                                        title="Editar Producto">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <!-- Botón Eliminar -->
                                <button @click="
                                            deleteUrl = '{{ route('productos.destroy', $producto->id) }}';
                                            deleteModalOpen = true;
                                        " 
                                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors inline-flex items-center"
                                        title="Eliminar Producto">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <p class="text-base font-medium text-gray-500">No se encontraron productos registrados</p>
                                    <p class="text-xs text-gray-400">Haz clic en "Nuevo Producto" para agregar el primero.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        @if($productos->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $productos->links() }}
            </div>
        @endif
    </div>


    <!-- MODAL CREAR PRODUCTO -->
    <div x-show="createModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden border border-gray-100 my-8" @click.away="createModalOpen = false">
            <div class="bg-fenix text-white px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold">Crear Nuevo Producto</h3>
                <button @click="createModalOpen = false" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form action="{{ route('productos.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Código -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Código del Producto *</label>
                        <input type="text" name="codigo" value="{{ old('codigo') }}" placeholder="Ej. PET-500ML" required
                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix">
                        @error('codigo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Tipo de Producto (Obligatorio) -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tipo de Producto *</label>
                        <select name="tipo_producto" required class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix font-medium">
                            <option value="PREFORMA" {{ old('tipo_producto') == 'PREFORMA' ? 'selected' : '' }}>🍼 PREFORMA</option>
                            <option value="TERMO" {{ old('tipo_producto') == 'TERMO' ? 'selected' : '' }}>📦 TERMOFORMADO</option>
                            <option value="LAMINADO" {{ old('tipo_producto') == 'LAMINADO' ? 'selected' : '' }}>📜 LAMINADOS</option>
                        </select>
                        @error('tipo_producto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Nombre -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nombre del Producto *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. Preforma PET 500ml 28g" required
                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix">
                    @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Unidad de Medida -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Unidad de Medida *</label>
                        <select name="unidad_medida" required class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                            <option value="UNIDADES" {{ old('unidad_medida') == 'UNIDADES' ? 'selected' : '' }}>UNIDADES (u)</option>
                            <option value="MILLARES" {{ old('unidad_medida') == 'MILLARES' ? 'selected' : '' }}>MILLARES (mil)</option>
                            <option value="CAJAS" {{ old('unidad_medida') == 'CAJAS' ? 'selected' : '' }}>CAJAS</option>
                            <option value="SACOS" {{ old('unidad_medida') == 'SACOS' ? 'selected' : '' }}>SACOS</option>
                            <option value="KILOS" {{ old('unidad_medida') == 'KILOS' ? 'selected' : '' }}>KILOGRAMOS (kg)</option>
                            <option value="JUMBOS" {{ old('unidad_medida') == 'JUMBOS' ? 'selected' : '' }}>JUMBOS</option>
                        </select>
                        @error('unidad_medida') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Peso Unitario -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Peso Unitario (Opcional)</label>
                        <input type="number" step="0.0001" name="peso_unitario" value="{{ old('peso_unitario') }}" placeholder="Ej. 28.00"
                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix font-mono">
                        <span class="text-[11px] text-gray-400">Peso en gramos o kg según línea</span>
                    </div>
                </div>

                <!-- Activo Checkbox -->
                <div class="flex items-center space-x-2 pt-2">
                    <input type="checkbox" name="activo" id="create_activo" value="1" {{ old('activo', '1') ? 'checked' : '' }}
                           class="w-4 h-4 text-fenix rounded border-gray-300 focus:ring-fenix">
                    <label for="create_activo" class="text-sm font-medium text-gray-700">Producto Habilitado / Activo</label>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="createModalOpen = false" 
                            class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" 
                            class="px-5 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-sm font-semibold shadow-md">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>


    <!-- MODAL EDITAR PRODUCTO -->
    <div x-show="editModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl overflow-hidden border border-gray-100 my-8" @click.away="editModalOpen = false">
            <div class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold">Editar Producto</h3>
                <button @click="editModalOpen = false" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form :action="editUrl" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" :value="editProduct.id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Código -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Código del Producto *</label>
                        <input type="text" name="codigo" x-model="editProduct.codigo" required
                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix">
                    </div>

                    <!-- Tipo de Producto -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tipo de Producto *</label>
                        <select name="tipo_producto" x-model="editProduct.tipo_producto" required class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix font-medium">
                            <option value="PREFORMA">PREFORMA</option>
                            <option value="TERMO">TERMOFORMADO</option>
                            <option value="LAMINADO">LAMINADOS</option>
                        </select>
                    </div>
                </div>

                <!-- Nombre -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nombre del Producto *</label>
                    <input type="text" name="nombre" x-model="editProduct.nombre" required
                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Unidad de Medida -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Unidad de Medida *</label>
                        <select name="unidad_medida" x-model="editProduct.unidad_medida" required class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                            <option value="UNIDADES">UNIDADES (u)</option>
                            <option value="CAJAS">CAJAS</option>
                            <option value="SACOS">SACOS</option>
                            <option value="KILOS">KILOGRAMOS (kg)</option>
                            <option value="MILLARES">MILLARES (mil)</option>
                            <option value="JUMBOS">JUMBOS</option>
                        </select>
                    </div>

                    <!-- Peso Unitario -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Peso Unitario (Opcional)</label>
                        <input type="number" step="0.0001" name="peso_unitario" x-model="editProduct.peso_unitario" placeholder="Ej. 28.00"
                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix font-mono">
                    </div>
                </div>

                <!-- Activo Checkbox -->
                <div class="flex items-center space-x-2 pt-2">
                    <input type="checkbox" name="activo" id="edit_activo" value="1" :checked="editProduct.activo"
                           class="w-4 h-4 text-fenix rounded border-gray-300 focus:ring-fenix">
                    <label for="edit_activo" class="text-sm font-medium text-gray-700">Producto Habilitado / Activo</label>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false" 
                            class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" 
                            class="px-5 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-sm font-semibold shadow-md">Actualizar Producto</button>
                </div>
            </form>
        </div>
    </div>


    <!-- MODAL PARÁMETROS TÉCNICOS DE PREFORMA -->
    <div x-show="paramModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden border border-gray-100 my-8" @click.away="paramModalOpen = false">
            <div class="bg-emerald-800 text-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <div>
                        <h3 class="text-lg font-bold">Parámetros Técnicos de Preforma</h3>
                        <p class="text-xs text-emerald-200 font-mono" x-text="paramProduct.codigo + ' - ' + paramProduct.nombre"></p>
                    </div>
                </div>
                <button @click="paramModalOpen = false" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form :action="paramUrl" method="POST" class="p-6 space-y-4">
                @csrf
                
                <!-- SECCIÓN 1: CAVIDADES Y TOLERANCIAS DE PESO -->
                <div class="bg-emerald-50/60 p-4 rounded-xl border border-emerald-200 space-y-3">
                    <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider flex items-center space-x-1.5">
                        <span>Cavidades y Tolerancias de Peso (Gramos)</span>
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <!-- Cavidades -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">N° Cavidades *</label>
                            <input type="number" name="numero_cavidades" x-model="paramProduct.numero_cavidades" required placeholder="Ej. 48"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-emerald-600 font-bold">
                        </div>

                        <!-- Peso Nominal -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Peso Unitario(g) *</label>
                            <input type="number" step="0.01" name="peso_nominal" x-model="paramProduct.peso_nominal" required placeholder="Ej. 28.00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-emerald-600 font-bold text-emerald-800">
                        </div>

                        <!-- Peso Min -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Peso Mínimo (g) *</label>
                            <input type="number" step="0.01" name="peso_min" x-model="paramProduct.peso_min" required placeholder="Ej. 27.50"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-emerald-600 font-mono">
                        </div>

                        <!-- Peso Max -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Peso Máximo (g) *</label>
                            <input type="number" step="0.01" name="peso_max" x-model="paramProduct.peso_max" required placeholder="Ej. 28.50"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-emerald-600 font-mono">
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: ESPESORES Y ALTURA -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Espesor de Pared (mm) -->
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2">
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center space-x-1">
                            <span>📐</span> <span>Espesor Pared (mm)</span>
                        </h4>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600">Espesor Pared Mín.</label>
                                <input type="number" step="0.01" name="esp_pared_min" x-model="paramProduct.esp_pared_min" placeholder="Ej. 2.10"
                                       class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-mono focus:outline-none focus:border-emerald-600">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600">Espesor Pared Máx.</label>
                                <input type="number" step="0.01" name="esp_pared_max" x-model="paramProduct.esp_pared_max" placeholder="Ej. 2.40"
                                       class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-mono focus:outline-none focus:border-emerald-600">
                            </div>
                        </div>
                    </div>

                    <!-- Espesor de Fondo (mm) -->
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2">
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center space-x-1">
                            <span>🎯</span> <span>Espesor Fondo (mm)</span>
                        </h4>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600">Espesor Fondo Mín.</label>
                                <input type="number" step="0.01" name="esp_fondo_min" x-model="paramProduct.esp_fondo_min" placeholder="Ej. 1.80"
                                       class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-mono focus:outline-none focus:border-emerald-600">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600">Espesor Fondo Máx.</label>
                                <input type="number" step="0.01" name="esp_fondo_max" x-model="paramProduct.esp_fondo_max" placeholder="Ej. 2.20"
                                       class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-mono focus:outline-none focus:border-emerald-600">
                            </div>
                        </div>
                    </div>

                    <!-- Altura Total (mm) -->
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2">
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center space-x-1">
                            <span>📏</span> <span>Altura Total (mm)</span>
                        </h4>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600">Altura Mínima</label>
                                <input type="number" step="0.01" name="altura_min" x-model="paramProduct.altura_min" placeholder="Ej. 110.00"
                                       class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-mono focus:outline-none focus:border-emerald-600">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600">Altura Máxima</label>
                                <input type="number" step="0.01" name="altura_max" x-model="paramProduct.altura_max" placeholder="Ej. 112.50"
                                       class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-mono focus:outline-none focus:border-emerald-600">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="paramModalOpen = false" 
                            class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" 
                            class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-sm font-semibold shadow-md flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Guardar Parámetros</span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- MODAL CONFIRMAR ELIMINACIÓN -->
    <div x-show="deleteModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-gray-100" @click.away="deleteModalOpen = false">
            <div class="p-6 text-center space-y-4">
                <div class="w-14 h-14 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">¿Eliminar Producto?</h3>
                <p class="text-sm text-gray-500">Esta acción no se puede deshacer. Si el producto ya tiene lotes o inspecciones registradas, no podrá ser eliminado.</p>

                <form :action="deleteUrl" method="POST" class="flex justify-center space-x-3 pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteModalOpen = false" 
                            class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" 
                            class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold shadow-md">Sí, Eliminar</button>
                </form>
            </div>
        </div>
    </div>


    <!-- MODAL IMPORTACIÓN / SUBIDA MASIVA DE PRODUCTOS -->
    <div x-show="importModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-gray-100 my-8" @click.away="importModalOpen = false">
            <div class="bg-fenix text-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <h3 class="text-lg font-bold">Subida Masiva de Productos</h3>
                </div>
                <button @click="importModalOpen = false" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form action="{{ route('productos.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf
                
                <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl text-xs text-emerald-900 space-y-2">
                    <div class="flex items-center justify-between font-semibold">
                        <span class="flex items-center space-x-1.5">
                            <span>Plantilla de Ejemplo</span>
                        </span>
                        <a href="{{ route('productos.plantilla') }}" class="inline-flex items-center space-x-1 text-fenix hover:text-fenix-dark font-bold underline transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Descargar Plantilla CSV</span>
                        </a>
                    </div>
                    <p class="text-[11px] text-emerald-700 leading-relaxed">
                        Descarga la plantilla de ejemplo para asegurar que tu archivo contenga las columnas requeridas: <code class="font-mono bg-white/70 px-1 py-0.5 rounded border border-emerald-200">codigo</code>, <code class="font-mono bg-white/70 px-1 py-0.5 rounded border border-emerald-200">nombre</code>, <code class="font-mono bg-white/70 px-1 py-0.5 rounded border border-emerald-200">tipo_producto</code>, <code class="font-mono bg-white/70 px-1 py-0.5 rounded border border-emerald-200">unidad_medida</code>, <code class="font-mono bg-white/70 px-1 py-0.5 rounded border border-emerald-200">peso_unitario</code>.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Seleccionar Archivo (Excel / CSV) *</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-5 text-center hover:border-fenix transition-colors bg-gray-50/50">
                        <input type="file" name="archivo" accept=".xlsx,.xls,.csv,.txt" required
                               class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-fenix file:text-white hover:file:bg-fenix-dark cursor-pointer">
                        <p class="text-[11px] text-gray-400 mt-2">Formatos permitidos: .xlsx, .xls, .csv (Máx. 10 MB)</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="importModalOpen = false" 
                            class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" 
                            class="px-5 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-sm font-semibold shadow-md flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Importar Productos</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
