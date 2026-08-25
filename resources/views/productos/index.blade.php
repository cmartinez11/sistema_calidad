@extends('layouts.app')

@section('content')
<div x-data="{
    createModalOpen: {{ $errors->any() && !old('_method') ? 'true' : 'false' }},
    editModalOpen: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }},
    deleteModalOpen: false,
    editProduct: {
        id: '{{ old('id') }}',
        codigo: '{{ old('codigo') }}',
        nombre: '{{ old('nombre') }}',
        presentacion: '{{ old('presentacion', 'Caja') }}',
        millares_presentacion: '{{ old('millares_presentacion', '1.0000') }}',
        gramaje: '{{ old('gramaje') }}',
        unidad_peso: '{{ old('unidad_peso', 'GRAMOS') }}',
        unidad_dimension: '{{ old('unidad_dimension', 'MILIMETROS') }}',
        unidad_produccion: '{{ old('unidad_produccion', 'UNIDADES') }}',
        factor_conversion_kg: '{{ old('factor_conversion_kg') }}',
        activo: {{ old('activo') ? 'true' : 'false' }}
    },
    editUrl: '',
    deleteUrl: ''
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
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Catálogo de Productos y Preformas</h2>
            <p class="text-xs text-gray-400 mt-1">Gestión de empaques, gramaje, millares de presentación y conversión de peso en planta</p>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Botón Búsqueda -->
            <form method="GET" action="{{ route('productos.index') }}" class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por código, nombre..." 
                       class="pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix w-64 transition-all">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>

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
                        <th class="px-6 py-4">Presentación & Millares</th>
                        <th class="px-6 py-4 text-center">Gramaje (g)</th>
                        <th class="px-6 py-4">Unidades (Peso / Dim. / Prod.)</th>
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

                            <!-- Presentación & Millares por Empaque -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold rounded-lg flex items-center space-x-1">
                                        @if($producto->presentacion === 'Saco') 🛍️ @elseif($producto->presentacion === 'Jumbo') 🐘 @elseif($producto->presentacion === 'Bolsa') 💼 @else 📦 @endif
                                        <span>{{ $producto->presentacion ?? 'Caja' }}</span>
                                    </span>
                                    <span class="text-xs text-gray-500 font-mono">
                                        ({{ number_format($producto->millares_presentacion ?? 1.0, 4) }} millar/empaque)
                                    </span>
                                </div>
                            </td>

                            <!-- Gramaje -->
                            <td class="px-6 py-4 text-center whitespace-nowrap font-mono font-bold text-fenix-dark">
                                @if($producto->gramaje)
                                    <span class="bg-green-50 text-fenix px-2.5 py-1 rounded-lg text-xs border border-green-200">
                                        {{ number_format($producto->gramaje, 2) }} g
                                    </span>
                                @else
                                    <span class="text-gray-400 font-normal text-xs">-</span>
                                @endif
                            </td>

                            <!-- Unidades de Medida -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="bg-blue-50 text-blue-700 border border-blue-200 text-xs px-2 py-0.5 rounded-md font-medium">
                                        ⚖️ {{ $producto->unidad_peso ?? 'GRAMOS' }}
                                    </span>
                                    <span class="bg-purple-50 text-purple-700 border border-purple-200 text-xs px-2 py-0.5 rounded-md font-medium">
                                        📏 {{ $producto->unidad_dimension ?? 'MILIMETROS' }}
                                    </span>
                                    <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs px-2 py-0.5 rounded-md font-medium">
                                        📦 {{ $producto->unidad_produccion ?? 'UNIDADES' }}
                                    </span>
                                </div>
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
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                <!-- Botón Editar -->
                                <button @click="
                                            editProduct = {
                                                id: {{ $producto->id }},
                                                codigo: '{{ e($producto->codigo) }}',
                                                nombre: '{{ e($producto->nombre) }}',
                                                presentacion: '{{ e($producto->presentacion ?? 'Caja') }}',
                                                millares_presentacion: '{{ $producto->millares_presentacion ?? '1.0000' }}',
                                                gramaje: '{{ $producto->gramaje }}',
                                                unidad_peso: '{{ e($producto->unidad_peso ?? 'GRAMOS') }}',
                                                unidad_dimension: '{{ e($producto->unidad_dimension ?? 'MILIMETROS') }}',
                                                unidad_produccion: '{{ e($producto->unidad_produccion ?? 'UNIDADES') }}',
                                                factor_conversion_kg: '{{ $producto->factor_conversion_kg }}',
                                                activo: {{ $producto->activo ? 'true' : 'false' }}
                                            };
                                            editUrl = '{{ route('productos.update', $producto->id) }}';
                                            editModalOpen = true;
                                        " 
                                        class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="Editar Producto">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <!-- Botón Eliminar -->
                                <button @click="
                                            deleteUrl = '{{ route('productos.destroy', $producto->id) }}';
                                            deleteModalOpen = true;
                                        " 
                                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Eliminar Producto">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <span class="text-4xl">📦</span>
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
        
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden border border-gray-100 my-8" @click.away="createModalOpen = false">
            <div class="bg-fenix text-white px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold">Crear Nuevo Producto / Preforma</h3>
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

                    <!-- Nombre -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nombre del Producto *</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. Preforma PET 500ml 28g" required
                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix">
                        @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- SECCIÓN PRESENTACIÓN Y GRAMAJE -->
                <div class="bg-amber-50/70 p-4 rounded-xl border border-amber-200 space-y-3">
                    <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center space-x-1">
                        <span>📦</span>
                        <span>Configuración de Empaque y Gramaje Planta</span>
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Presentación -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Presentación *</label>
                            <select name="presentacion" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                                <option value="Caja" {{ old('presentacion') == 'Caja' ? 'selected' : '' }}>📦 Caja</option>
                                <option value="Saco" {{ old('presentacion') == 'Saco' ? 'selected' : '' }}>🛍️ Saco</option>
                                <option value="Jumbo" {{ old('presentacion') == 'Jumbo' ? 'selected' : '' }}>🐘 Jumbo</option>
                                <option value="Bolsa" {{ old('presentacion') == 'Bolsa' ? 'selected' : '' }}>💼 Bolsa</option>
                            </select>
                        </div>

                        <!-- Millares por Presentación -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Millares por Empaque *</label>
                            <input type="number" step="0.0001" name="millares_presentacion" value="{{ old('millares_presentacion', '1.0000') }}" placeholder="Ej. 1.5000" required
                                   class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                            <span class="text-[11px] text-gray-500">Ej: 1.5 para sacos, 0.55 cajas</span>
                        </div>

                        <!-- Gramaje (g) -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Gramaje Unitario (g) *</label>
                            <input type="number" step="0.01" name="gramaje" value="{{ old('gramaje') }}" placeholder="Ej. 28.00" required
                                   class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix font-bold text-fenix-dark">
                            <span class="text-[11px] text-gray-500">Peso en gramos por preforma</span>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN UNIDADES DE MEDIDA SECUNDARIAS -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                    <h4 class="text-xs font-bold text-fenix uppercase tracking-wider">Unidades de Tolerancia y Laboratorio</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Unidad Peso -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Unidad Peso</label>
                            <select name="unidad_peso" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-fenix">
                                <option value="GRAMOS" {{ old('unidad_peso') == 'GRAMOS' ? 'selected' : '' }}>GRAMOS (g)</option>
                                <option value="KILOGRAMOS" {{ old('unidad_peso') == 'KILOGRAMOS' ? 'selected' : '' }}>KILOGRAMOS (kg)</option>
                                <option value="ONZAS" {{ old('unidad_peso') == 'ONZAS' ? 'selected' : '' }}>ONZAS (oz)</option>
                            </select>
                        </div>

                        <!-- Unidad Dimension -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Unidad Dimensión</label>
                            <select name="unidad_dimension" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-fenix">
                                <option value="MILIMETROS" {{ old('unidad_dimension') == 'MILIMETROS' ? 'selected' : '' }}>MILÍMETROS (mm)</option>
                                <option value="CENTIMETROS" {{ old('unidad_dimension') == 'CENTIMETROS' ? 'selected' : '' }}>CENTÍMETROS (cm)</option>
                                <option value="PULGADAS" {{ old('unidad_dimension') == 'PULGADAS' ? 'selected' : '' }}>PULGADAS (in)</option>
                            </select>
                        </div>

                        <!-- Unidad Producción -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Unidad Producción</label>
                            <select name="unidad_produccion" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-fenix">
                                <option value="UNIDADES" {{ old('unidad_produccion') == 'UNIDADES' ? 'selected' : '' }}>UNIDADES (u)</option>
                                <option value="MILLARES" {{ old('unidad_produccion') == 'MILLARES' ? 'selected' : '' }}>MILLARES (mil)</option>
                                <option value="CAJAS" {{ old('unidad_produccion') == 'CAJAS' ? 'selected' : '' }}>CAJAS</option>
                                <option value="BOLSAS" {{ old('unidad_produccion') == 'BOLSAS' ? 'selected' : '' }}>BOLSAS</option>
                                <option value="JUMBO" {{ old('unidad_produccion') == 'JUMBO' ? 'selected' : '' }}>JUMBO</option>
                                <option value="SACOS" {{ old('unidad_produccion') == 'SACOS' ? 'selected' : '' }}>SACOS</option>
                            </select>
                        </div>
                    </div>

                    <!-- Factor Conversion kg opcional -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Factor de Conversión a kg (opcional, calculado si se omite)</label>
                        <input type="number" step="0.0001" name="factor_conversion_kg" value="{{ old('factor_conversion_kg') }}" placeholder="Auto-calculado: (gramaje/1000) * millares"
                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                    </div>
                </div>

                <!-- Activo Checkbox -->
                <div class="flex items-center space-x-2 pt-1">
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
        
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden border border-gray-100 my-8" @click.away="editModalOpen = false">
            <div class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold">Editar Producto / Preforma</h3>
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

                    <!-- Nombre -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nombre del Producto *</label>
                        <input type="text" name="nombre" x-model="editProduct.nombre" required
                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix">
                    </div>
                </div>

                <!-- SECCIÓN PRESENTACIÓN Y GRAMAJE EDITAR -->
                <div class="bg-amber-50/70 p-4 rounded-xl border border-amber-200 space-y-3">
                    <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center space-x-1">
                        <span>📦</span>
                        <span>Configuración de Empaque y Gramaje Planta</span>
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Presentación -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Presentación *</label>
                            <select name="presentacion" x-model="editProduct.presentacion" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                                <option value="Caja">📦 Caja</option>
                                <option value="Saco">🛍️ Saco</option>
                                <option value="Jumbo">🐘 Jumbo</option>
                                <option value="Bolsa">💼 Bolsa</option>
                            </select>
                        </div>

                        <!-- Millares por Presentación -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Millares por Empaque *</label>
                            <input type="number" step="0.0001" name="millares_presentacion" x-model="editProduct.millares_presentacion" required
                                   class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                        </div>

                        <!-- Gramaje (g) -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Gramaje Unitario (g) *</label>
                            <input type="number" step="0.01" name="gramaje" x-model="editProduct.gramaje" required
                                   class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix font-bold text-fenix-dark">
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN UNIDADES DE MEDIDA EDITAR -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                    <h4 class="text-xs font-bold text-fenix uppercase tracking-wider">Unidades de Tolerancia y Laboratorio</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Unidad Peso -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Unidad Peso</label>
                            <select name="unidad_peso" x-model="editProduct.unidad_peso" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-fenix">
                                <option value="GRAMOS">GRAMOS (g)</option>
                                <option value="KILOGRAMOS">KILOGRAMOS (kg)</option>
                                <option value="ONZAS">ONZAS (oz)</option>
                            </select>
                        </div>

                        <!-- Unidad Dimension -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Unidad Dimensión</label>
                            <select name="unidad_dimension" x-model="editProduct.unidad_dimension" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-fenix">
                                <option value="MILIMETROS">MILÍMETROS (mm)</option>
                                <option value="CENTIMETROS">CENTÍMETROS (cm)</option>
                                <option value="PULGADAS">PULGADAS (in)</option>
                            </select>
                        </div>

                        <!-- Unidad Producción -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Unidad Producción</label>
                            <select name="unidad_produccion" x-model="editProduct.unidad_produccion" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-fenix">
                                <option value="UNIDADES">UNIDADES (u)</option>
                                <option value="MILLARES">MILLARES (mil)</option>
                                <option value="CAJAS">CAJAS</option>
                                <option value="BOLSAS">BOLSAS</option>
                                <option value="JUMBO">JUMBO</option>
                                <option value="SACOS">SACOS</option>
                            </select>
                        </div>
                    </div>

                    <!-- Factor Conversion kg -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Factor de Conversión a Kilogramos (kg)</label>
                        <input type="number" step="0.0001" name="factor_conversion_kg" x-model="editProduct.factor_conversion_kg" placeholder="Ej. 0.0280"
                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix">
                    </div>
                </div>

                <!-- Activo Checkbox -->
                <div class="flex items-center space-x-2 pt-1">
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

</div>
@endsection
