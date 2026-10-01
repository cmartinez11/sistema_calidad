@extends('layouts.app')

@section('content')
<div x-data="{
    createModalOpen: {{ $errors->any() && !old('_method') ? 'true' : 'false' }},
    editModalOpen: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }},
    deleteModalOpen: false,
    historyModalOpen: false,
    editMachine: {
        id: '{{ old('id') }}',
        codigo: '{{ old('codigo') }}',
        nombre: '{{ old('nombre') }}',
        estado: '{{ old('estado', 'activo') }}',
        motivo_observacion: '{{ old('motivo_observacion') }}',
        fecha_hora_inicio: ''
    },
    selectedMachineHistory: {
        codigo: '',
        nombre: '',
        items: []
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
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Gestión de Máquinas e Inyectoras</h2>
            <p class="text-xs text-gray-400 mt-1">Control de inventario de equipos, trazabilidad de paradas e historial operativo</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <!-- Botón Búsqueda -->
            <form method="GET" action="{{ route('maquinas.index') }}" class="relative w-full sm:w-auto">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por código, nombre..." 
                       class="pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix w-full sm:w-64 transition-all">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>

            <!-- Botón Nueva Máquina -->
            @canWrite
                <button @click="createModalOpen = true" 
                        class="bg-fenix hover:bg-fenix-dark text-white px-5 py-2.5 rounded-xl font-medium text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nueva Máquina</span>
                </button>
            @endcanWrite
        </div>
    </div>

    <!-- TABLA PRINCIPAL DE MÁQUINAS -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Código</th>
                        <th class="px-6 py-4">Nombre / Descripción</th>
                        <th class="px-6 py-4 text-center">Estado Operativo</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($maquinas as $maquina)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Código -->
                            <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">
                                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-lg text-xs font-mono border border-gray-200">
                                    {{ $maquina->codigo }}
                                </span>
                            </td>

                            <!-- Nombre / Descripción -->
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $maquina->nombre }}
                            </td>

                            <!-- Estado Operativo -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($maquina->estado === 'activo')
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-200">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span> ACTIVA / OPERATIVA
                                    </span>
                                @elseif($maquina->estado === 'mantenimiento')
                                    <span class="inline-flex items-center px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full border border-amber-200">
                                        <span class="w-2 h-2 bg-amber-500 rounded-full mr-2 animate-pulse"></span> EN MANTENIMIENTO
                                    </span>
                                @elseif($maquina->estado === 'sin_programacion')
                                    <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full border border-purple-200">
                                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span> SIN PROGRAMACIÓN
                                    </span>
                                @elseif($maquina->estado === 'sin_resina')
                                    <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full border border-orange-200">
                                        <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span> SIN RESINA
                                    </span>
                                @elseif($maquina->estado === 'sin_personal')
                                    <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full border border-blue-200">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span> SIN PERSONAL
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full border border-gray-200">
                                        <span class="w-2 h-2 bg-gray-500 rounded-full mr-2"></span> {{ strtoupper($maquina->estado) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-1">
                                <!-- Botón Historial de Trazabilidad -->
                                <button @click="
                                        selectedMachineHistory = {
                                            codigo: '{{ e($maquina->codigo) }}',
                                            nombre: '{{ e($maquina->nombre) }}',
                                            items: {{ json_encode($maquina->historiales->map(fn($h) => [
                                                'id' => $h->id,
                                                'estado_anterior' => $h->estado_anterior,
                                                'nuevo_estado' => $h->nuevo_estado,
                                                'motivo_observacion' => $h->motivo_observacion ?? 'Sin observaciones especificadas',
                                                'fecha_hora_inicio' => \Carbon\Carbon::parse($h->fecha_hora_inicio)->format('d/m/Y H:i'),
                                                'fecha_hora_fin' => $h->fecha_hora_fin ? \Carbon\Carbon::parse($h->fecha_hora_fin)->format('d/m/Y H:i') : 'En curso 🔴',
                                                'duracion_formateada' => $h->duracion_formateada,
                                                'usuario' => $h->user->name ?? $h->user->username ?? 'Sistema'
                                            ])) }}
                                        };
                                        historyModalOpen = true;
                                    " 
                                    class="p-2 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition-colors inline-flex items-center space-x-1"
                                    title="Ver Bitácora de Paradas / Trazabilidad">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-xs font-bold hidden lg:inline">Historial</span>
                                </button>

                                @canWrite
                                    <!-- Botón Editar -->
                                    <button @click="
                                            editMachine = {
                                                id: {{ $maquina->id }},
                                                codigo: '{{ e($maquina->codigo) }}',
                                                nombre: '{{ e($maquina->nombre) }}',
                                                estado: '{{ e($maquina->estado ?? 'activo') }}',
                                                motivo_observacion: '',
                                                fecha_hora_inicio: ''
                                            };
                                            editUrl = '{{ route('maquinas.update', $maquina->id) }}';
                                            editModalOpen = true;
                                        " 
                                        class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors inline-flex items-center"
                                        title="Editar / Cambiar Estado de Máquina">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <!-- Botón Eliminar -->
                                    <button @click="
                                            deleteUrl = '{{ route('maquinas.destroy', $maquina->id) }}';
                                            deleteModalOpen = true;
                                        " 
                                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors inline-flex items-center"
                                        title="Eliminar Máquina">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endcanWrite
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-12 text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <span class="text-4xl">⚙️</span>
                                    <p class="text-base font-medium text-gray-500">No se encontraron máquinas registradas</p>
                                    <p class="text-xs text-gray-400">Haz clic en "Nueva Máquina" para agregar la primera inyectora.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        @if($maquinas->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $maquinas->links() }}
            </div>
        @endif
    </div>


    <!-- MODAL CREAR MÁQUINA -->
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
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <h3 class="text-lg font-bold">Registrar Nueva Máquina</h3>
                </div>
                <button @click="createModalOpen = false" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form action="{{ route('maquinas.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Código -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Código de Máquina *</label>
                        <input type="text" name="codigo" value="{{ old('codigo') }}" placeholder="Ej. INY-01" required
                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix font-mono font-bold">
                        @error('codigo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Estado Operativo -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Estado Operativo Inicial *</label>
                        <select name="estado" required class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix font-medium">
                            <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>🟢 ACTIVA / OPERATIVA</option>
                            <option value="mantenimiento" {{ old('estado') == 'mantenimiento' ? 'selected' : '' }}>🟡 EN MANTENIMIENTO</option>
                            <option value="sin_programacion" {{ old('estado') == 'sin_programacion' ? 'selected' : '' }}>🟣 SIN PROGRAMACIÓN</option>
                            <option value="sin_resina" {{ old('estado') == 'sin_resina' ? 'selected' : '' }}>🟠 SIN RESINA</option>
                            <option value="sin_personal" {{ old('estado') == 'sin_personal' ? 'selected' : '' }}>🔵 SIN PERSONAL</option>
                        </select>
                        @error('estado') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Nombre / Descripción -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nombre / Descripción *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. Inyectora Husky 300T Linea 1" required
                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix">
                    @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Observación Inicial / Motivo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Observación Inicial / Nota</label>
                    <textarea name="motivo_observacion" rows="2" placeholder="Nota inicial sobre el estado de la máquina..."
                              class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-xs focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix">{{ old('motivo_observacion') }}</textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="createModalOpen = false" 
                            class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" 
                            class="px-5 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-sm font-semibold shadow-md">Guardar Máquina</button>
                </div>
            </form>
        </div>
    </div>


    <!-- MODAL EDITAR MÁQUINA / CAMBIAR ESTADO -->
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
                    <h3 class="text-lg font-bold">Editar Máquina y Trazabilidad</h3>
                </div>
                <button @click="editModalOpen = false" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <form :action="editUrl" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" :value="editMachine.id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Código -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Código de Máquina *</label>
                        <input type="text" name="codigo" x-model="editMachine.codigo" required
                               class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix font-mono font-bold">
                    </div>

                    <!-- Estado Operativo -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Estado Operativo *</label>
                        <select name="estado" x-model="editMachine.estado" required class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix font-medium">
                            <option value="activo">🟢 ACTIVA / OPERATIVA</option>
                            <option value="mantenimiento">🟡 EN MANTENIMIENTO</option>
                            <option value="sin_programacion">🟣 SIN PROGRAMACIÓN</option>
                            <option value="sin_resina">🟠 SIN RESINA</option>
                            <option value="sin_personal">🔵 SIN PERSONAL</option>
                        </select>
                    </div>
                </div>

                <!-- Nombre / Descripción -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nombre / Descripción *</label>
                    <input type="text" name="nombre" x-model="editMachine.nombre" required
                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix">
                </div>

                <!-- Motivo / Observación de Parada -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Motivo / Observación del Cambio de Estado</label>
                    <textarea name="motivo_observacion" x-model="editMachine.motivo_observacion" rows="2" 
                              placeholder="Describa la causa de la parada, mantenimiento preventivo o motivo del cambio..."
                              class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-xs focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix"></textarea>
                    <span class="text-[10px] text-gray-400">Si cambia de estado, registre la causa de la parada o inactividad.</span>
                </div>

                <!-- Fecha y Hora de Inicio (Opcional) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Fecha y Hora de Inicio (Opcional)</label>
                    <input type="datetime-local" name="fecha_hora_inicio" x-model="editMachine.fecha_hora_inicio"
                           class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-xs font-mono focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix">
                    <span class="text-[10px] text-gray-400">Deje en blanco para registrar la fecha/hora actual de inmediato.</span>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false" 
                            class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" 
                            class="px-5 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-sm font-semibold shadow-md">Actualizar Máquina</button>
                </div>
            </form>
        </div>
    </div>


    <!-- MODAL HISTORIAL DE TRAZABILIDAD / BITÁCORA DE PARADAS -->
    <div x-show="historyModalOpen" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden border border-gray-100 my-8" @click.away="historyModalOpen = false">
            <div class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-emerald-500/20 text-emerald-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Bitácora de Trazabilidad e Historial de Paradas</h3>
                        <p class="text-xs text-gray-400">
                            Máquina: <strong class="text-emerald-400 font-mono" x-text="selectedMachineHistory.codigo"></strong> - <span x-text="selectedMachineHistory.nombre"></span>
                        </p>
                    </div>
                </div>
                <button @click="historyModalOpen = false" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
            </div>

            <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                <template x-if="selectedMachineHistory.items.length === 0">
                    <div class="text-center py-8 text-gray-400 space-y-2">
                        <span class="text-3xl">📋</span>
                        <p class="text-xs font-medium">No se registran eventos de cambio de estado o paradas anteriores para esta máquina.</p>
                    </div>
                </template>

                <template x-if="selectedMachineHistory.items.length > 0">
                    <div class="overflow-x-auto border border-gray-100 rounded-xl">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50 text-[11px] font-bold text-gray-500 uppercase border-b border-gray-200">
                                    <th class="py-2.5 px-3">Transición de Estado</th>
                                    <th class="py-2.5 px-3">Inicio</th>
                                    <th class="py-2.5 px-3">Cierre / Fin</th>
                                    <th class="py-2.5 px-3">Duración Parada</th>
                                    <th class="py-2.5 px-3">Motivo / Observación</th>
                                    <th class="py-2.5 px-3">Registrado Por</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="item in selectedMachineHistory.items" :key="item.id">
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <!-- Transición -->
                                        <td class="py-2.5 px-3 font-semibold whitespace-nowrap">
                                            <div class="flex items-center space-x-1.5">
                                                <span x-text="item.estado_anterior ? item.estado_anterior.toUpperCase().replace('_', ' ') : 'ALTA MÁQUINA'" class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-mono"></span>
                                                <span class="text-gray-400">➔</span>
                                                <span :class="{
                                                    'bg-green-100 text-green-700 border-green-200': item.nuevo_estado === 'activo',
                                                    'bg-amber-100 text-amber-700 border-amber-200': item.nuevo_estado === 'mantenimiento',
                                                    'bg-purple-100 text-purple-700 border-purple-200': item.nuevo_estado === 'sin_programacion',
                                                    'bg-orange-100 text-orange-700 border-orange-200': item.nuevo_estado === 'sin_resina',
                                                    'bg-blue-100 text-blue-700 border-blue-200': item.nuevo_estado === 'sin_personal'
                                                }" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border" x-text="item.nuevo_estado.replace('_', ' ')"></span>
                                            </div>
                                        </td>

                                        <!-- Inicio -->
                                        <td class="py-2.5 px-3 font-mono text-gray-700 whitespace-nowrap" x-text="item.fecha_hora_inicio"></td>

                                        <!-- Fin -->
                                        <td class="py-2.5 px-3 font-mono whitespace-nowrap">
                                            <span :class="item.fecha_hora_fin === 'En curso 🔴' ? 'text-rose-600 font-bold' : 'text-gray-700'" x-text="item.fecha_hora_fin"></span>
                                        </td>

                                        <!-- Duración -->
                                        <td class="py-2.5 px-3 font-mono font-bold whitespace-nowrap">
                                            <span :class="item.duracion_formateada.includes('En curso') ? 'text-amber-600 font-bold' : 'text-emerald-700'" x-text="item.duracion_formateada"></span>
                                        </td>

                                        <!-- Motivo -->
                                        <td class="py-2.5 px-3 text-gray-700 max-w-xs truncate" :title="item.motivo_observacion" x-text="item.motivo_observacion"></td>

                                        <!-- Usuario -->
                                        <td class="py-2.5 px-3 text-gray-500 font-medium whitespace-nowrap" x-text="item.usuario"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>

            <div class="flex justify-end p-4 border-t border-gray-100 bg-gray-50">
                <button type="button" @click="historyModalOpen = false" class="px-5 py-2 bg-gray-800 text-white rounded-xl text-xs font-bold hover:bg-gray-900 transition-all">
                    Cerrar Bitácora
                </button>
            </div>
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
                <h3 class="text-lg font-bold text-gray-800">¿Eliminar Máquina?</h3>
                <p class="text-sm text-gray-500">Esta acción no se puede deshacer. Si la máquina tiene lotes o inspecciones registradas, no podrá ser eliminada.</p>

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