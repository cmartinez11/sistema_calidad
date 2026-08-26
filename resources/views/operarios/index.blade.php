@extends('layouts.app')

@section('content')
<div x-data="{
    createModalOpen: {{ $errors->any() && !old('_method') ? 'true' : 'false' }},
    editModalOpen: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }},
    deleteModalOpen: false,
    editOperario: {
        id: '{{ old('id') }}',
        nombre: '{{ old('nombre') }}',
        codigo_operario: '{{ old('codigo_operario') }}',
        activo: {{ old('activo', '1') ? 'true' : 'false' }}
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
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Gestión de Operarios y Encargados</h2>
            <p class="text-xs text-gray-400 mt-1">Administración de personal técnico de planta, inspectores y supervisores</p>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Botón Búsqueda -->
            <form method="GET" action="{{ route('operarios.index') }}" class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre, DNI..." 
                       class="pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix w-64 transition-all">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>

            <!-- Botón Nuevo Operario -->
            <button @click="createModalOpen = true" 
                    class="bg-fenix hover:bg-fenix-dark text-white px-5 py-2.5 rounded-xl font-medium text-sm shadow-md hover:shadow-lg transition-all flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Operario</span>
            </button>
        </div>
    </div>

    <!-- TABLA PRINCIPAL DE OPERARIOS -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Nombre del Operario</th>
                        <th class="px-6 py-4">Código / DNI</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($operarios as $operario)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Nombre -->
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-fenix/10 text-fenix font-bold flex items-center justify-center text-sm">
                                        {{ strtoupper(substr($operario->nombre, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-gray-800">{{ $operario->nombre }}</span>
                                </div>
                            </td>

                            <!-- Código / DNI -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($operario->codigo_operario)
                                    <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-lg text-xs font-mono font-bold border border-gray-200">
                                        {{ $operario->codigo_operario }}
                                    </span>
                                @else
                                    <span class="text-gray-400 font-normal text-xs">-</span>
                                @endif
                            </td>

                            <!-- Estado -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($operario->activo)
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-200">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span> ACTIVO
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full border border-gray-200">
                                        <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span> INACTIVO
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                <!-- Botón Editar -->
                                <button @click="
                                            editOperario = {
                                                id: {{ $operario->id }},
                                                nombre: '{{ e($operario->nombre) }}',
                                                codigo_operario: '{{ e($operario->codigo_operario) }}',
                                                activo: {{ $operario->activo ? 'true' : 'false' }}
                                            };
                                            editUrl = '{{ route('operarios.update', $operario->id) }}';
                                            editModalOpen = true;
                                        " 
                                        class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors inline-flex items-center"
                                        title="Editar Operario">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <!-- Botón Eliminar -->
                                <button @click="
                                            deleteUrl = '{{ route('operarios.destroy', $operario->id) }}';
                                            deleteModalOpen = true;
                                        " 
                                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors inline-flex items-center"
                                        title="Eliminar Operario">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-12 text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <span class="text-4xl">👷</span>
                                    <p class="text-base font-medium text-gray-500">No se encontraron operarios registrados</p>
                                    <p class="text-xs text-gray-400">Haz clic en "Nuevo Operario" para agregar el primero.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        @if($operarios->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $operarios->links() }}
            </div>
        @endif
    </div>


    <!-- MODAL CREAR OPERARIO -->
    <div x-show="createModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-gray-100 my-8" @click.away="createModalOpen = false">
            <div class="bg-fenix text-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <h3 class="text-lg font-bold">Registrar Nuevo Operario</h3>
                </div>
                <button @click="createModalOpen = false" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form action="{{ route('operarios.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <!-- Nombre Completo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nombre Completo del Operario *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. Jhosimar Pérez" required
                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix font-medium">
                    @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Código / DNI -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Código o DNI (Opcional)</label>
                    <input type="text" name="codigo_operario" value="{{ old('codigo_operario') }}" placeholder="Ej. OP-01 o 72345678"
                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix font-mono font-bold">
                    @error('codigo_operario') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Activo Checkbox -->
                <div class="flex items-center space-x-2 pt-2">
                    <input type="checkbox" name="activo" id="create_activo" value="1" {{ old('activo', '1') ? 'checked' : '' }}
                           class="w-4 h-4 text-fenix rounded border-gray-300 focus:ring-fenix">
                    <label for="create_activo" class="text-sm font-medium text-gray-700">Operario Habilitado / Activo</label>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="createModalOpen = false" 
                            class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" 
                            class="px-5 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-sm font-semibold shadow-md">Guardar Operario</button>
                </div>
            </form>
        </div>
    </div>


    <!-- MODAL EDITAR OPERARIO -->
    <div x-show="editModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-gray-100 my-8" @click.away="editModalOpen = false">
            <div class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <h3 class="text-lg font-bold">Editar Operario</h3>
                </div>
                <button @click="editModalOpen = false" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form :action="editUrl" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" :value="editOperario.id">

                <!-- Nombre Completo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nombre Completo del Operario *</label>
                    <input type="text" name="nombre" x-model="editOperario.nombre" required
                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix font-medium">
                </div>

                <!-- Código / DNI -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Código o DNI (Opcional)</label>
                    <input type="text" name="codigo_operario" x-model="editOperario.codigo_operario" placeholder="Ej. OP-01 o 72345678"
                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix font-mono font-bold">
                </div>

                <!-- Activo Checkbox -->
                <div class="flex items-center space-x-2 pt-2">
                    <input type="checkbox" name="activo" id="edit_activo" value="1" :checked="editOperario.activo"
                           class="w-4 h-4 text-fenix rounded border-gray-300 focus:ring-fenix">
                    <label for="edit_activo" class="text-sm font-medium text-gray-700">Operario Habilitado / Activo</label>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false" 
                            class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" 
                            class="px-5 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-sm font-semibold shadow-md">Actualizar Operario</button>
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
                <h3 class="text-lg font-bold text-gray-800">¿Eliminar Operario?</h3>
                <p class="text-sm text-gray-500">Esta acción no se puede deshacer. Si el operario tiene lotes o inspecciones asociadas, no podrá ser eliminado.</p>

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
