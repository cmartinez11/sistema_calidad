@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- TARJETA CABECERA -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight flex items-center space-x-2">
                <span>📜 Historial de Auditoría del Sistema</span>
            </h2>
            <p class="text-xs text-gray-400 mt-1">Registro detallado y trazabilidad global de creaciones, modificaciones y eliminaciones de registros en el sistema Grupo Fénix.</p>
        </div>

        <div class="flex items-center space-x-2">
            <span class="bg-blue-50 text-blue-700 text-xs font-bold px-3 py-1.5 rounded-xl border border-blue-200">
                Auditoría Activa 🛡️
            </span>
        </div>
    </div>

    <!-- PANEL DE FILTROS -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <!-- Buscar -->
            <div class="md:col-span-2">
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Búsqueda General</label>
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Buscar por usuario, IP, entidad..." 
                       class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-xs font-medium focus:ring-fenix focus:border-fenix">
            </div>

            <!-- Tipo de Acción -->
            <div>
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Acción</label>
                <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-800">
                    <option value="">-- Todas las Acciones --</option>
                    <option value="CREAR" {{ $action === 'CREAR' ? 'selected' : '' }}>🟢 CREAR</option>
                    <option value="EDITAR" {{ $action === 'EDITAR' ? 'selected' : '' }}>🔵 EDITAR</option>
                    <option value="ELIMINAR" {{ $action === 'ELIMINAR' ? 'selected' : '' }}>🔴 ELIMINAR</option>
                </select>
            </div>

            <!-- Módulo / Entidad -->
            <div>
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Módulo / Entidad</label>
                <select name="model_type" class="w-full px-3 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-800">
                    <option value="">-- Todas las Entidades --</option>
                    @foreach($modelTypes as $mType)
                        <option value="{{ $mType }}" {{ $modelType === $mType ? 'selected' : '' }}>{{ $mType }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Rango de Fechas y Botones -->
            <div class="md:col-span-5 flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100">
                <div class="flex items-center space-x-3">
                    <div>
                        <span class="text-[11px] font-bold text-gray-500 uppercase mr-1">Desde:</span>
                        <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" class="px-2.5 py-1.5 border border-gray-300 rounded-lg text-xs font-mono">
                    </div>
                    <div>
                        <span class="text-[11px] font-bold text-gray-500 uppercase mr-1">Hasta:</span>
                        <input type="date" name="fecha_fin" value="{{ $fechaFin }}" class="px-2.5 py-1.5 border border-gray-300 rounded-lg text-xs font-mono">
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <a href="{{ route('audit-logs.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-all">
                        Limpiar Filtros
                    </a>
                    <button type="submit" class="px-5 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-xs font-bold shadow-md transition-all">
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TABLA PRINCIPAL DE LOGS -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Fecha / Hora</th>
                        <th class="px-6 py-4">Usuario / IP</th>
                        <th class="px-6 py-4 text-center">Acción</th>
                        <th class="px-6 py-4">Entidad / Registro</th>
                        <th class="px-6 py-4 text-right">Detalle de Cambios</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-gray-50/80 transition-colors" x-data="{ showModal: false }">
                            <!-- Fecha / Hora -->
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-700">
                                <div class="font-bold text-gray-900">{{ $log->created_at ? $log->created_at->format('d/m/Y') : '-' }}</div>
                                <div class="text-[11px] text-gray-400">{{ $log->created_at ? $log->created_at->format('H:i:s') : '-' }}</div>
                            </td>

                            <!-- Usuario -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->user)
                                    <div class="font-bold text-gray-900">{{ $log->user->name }}</div>
                                    <div class="text-[11px] text-gray-400 font-mono">@ {{ $log->user->username }} | IP: {{ $log->ip_address ?? 'N/A' }}</div>
                                @else
                                    <div class="font-bold text-gray-500 italic">Sistema / Automático</div>
                                    <div class="text-[11px] text-gray-400 font-mono">IP: {{ $log->ip_address ?? '127.0.0.1' }}</div>
                                @endif
                            </td>

                            <!-- Acción -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($log->action === 'CREAR')
                                    <span class="px-3 py-1 bg-green-100 text-green-800 text-[11px] font-bold rounded-full border border-green-200">
                                        🟢 CREACIÓN
                                    </span>
                                @elseif($log->action === 'EDITAR')
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-[11px] font-bold rounded-full border border-blue-200">
                                        🔵 EDICIÓN
                                    </span>
                                @elseif($log->action === 'ELIMINAR')
                                    <span class="px-3 py-1 bg-red-100 text-red-800 text-[11px] font-bold rounded-full border border-red-200">
                                        🔴 ELIMINACIÓN
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-[11px] font-bold rounded-full border border-gray-200">
                                        {{ $log->action }}
                                    </span>
                                @endif
                            </td>

                            <!-- Entidad -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-bold text-gray-800">{{ $log->model_type }}</span>
                                @if($log->model_id)
                                    <span class="text-xs text-gray-500 font-mono">#{{ $log->model_id }}</span>
                                @endif
                            </td>

                            <!-- Detalle de Cambios (Modal Alpine) -->
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <button type="button" @click="showModal = true"
                                        class="px-3.5 py-1.5 bg-gray-100 hover:bg-fenix hover:text-white text-gray-700 rounded-xl text-xs font-semibold border border-gray-200 transition-all inline-flex items-center space-x-1.5 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Ver Inspección</span>
                                </button>

                                <!-- MODAL DE INSPECCIÓN DE DATOS (JSON DIFF) -->
                                <div x-show="showModal" x-cloak
                                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0">
                                    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 text-left space-y-4 border border-gray-100 max-h-[85vh] overflow-y-auto"
                                         @click.away="showModal = false">
                                        
                                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                            <div class="flex items-center space-x-2">
                                                <span class="p-2 bg-fenix/10 rounded-lg text-fenix font-bold">📜</span>
                                                <div>
                                                    <h3 class="text-sm font-bold text-gray-900">Auditoría: {{ $log->action }} en {{ $log->model_type }} #{{ $log->model_id }}</h3>
                                                    <p class="text-[11px] text-gray-400 font-mono">Realizado por: {{ $log->user->name ?? 'Sistema' }} | {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}</p>
                                                </div>
                                            </div>
                                            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
                                        </div>

                                        <!-- COMPARATIVA DE VALORES ANTERIORES Y NUEVOS -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
                                            <!-- VALORES ANTERIORES -->
                                            <div class="bg-red-50/50 p-4 rounded-xl border border-red-100 space-y-2">
                                                <h4 class="font-sans font-bold text-red-800 uppercase tracking-wider text-[11px] border-b border-red-200 pb-1">
                                                    ⏮️ Valores Anteriores (Old)
                                                </h4>
                                                @if(!empty($log->old_values))
                                                    <ul class="space-y-1 text-[11px]">
                                                        @foreach($log->old_values as $key => $val)
                                                            <li class="break-words">
                                                                <strong class="text-red-900">{{ $key }}:</strong> 
                                                                <span class="text-red-700">{{ is_array($val) ? json_encode($val) : (is_null($val) ? 'null' : (is_bool($val) ? ($val ? 'true' : 'false') : $val)) }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="text-gray-400 italic text-[11px]">Ninguno (Registro Nuevo)</p>
                                                @endif
                                            </div>

                                            <!-- VALORES NUEVOS -->
                                            <div class="bg-green-50/50 p-4 rounded-xl border border-green-100 space-y-2">
                                                <h4 class="font-sans font-bold text-green-800 uppercase tracking-wider text-[11px] border-b border-green-200 pb-1">
                                                    ⏭️ Valores Nuevos (New)
                                                </h4>
                                                @if(!empty($log->new_values))
                                                    <ul class="space-y-1 text-[11px]">
                                                        @foreach($log->new_values as $key => $val)
                                                            <li class="break-words">
                                                                <strong class="text-green-900">{{ $key }}:</strong> 
                                                                <span class="text-green-700">{{ is_array($val) ? json_encode($val) : (is_null($val) ? 'null' : (is_bool($val) ? ($val ? 'true' : 'false') : $val)) }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="text-gray-400 italic text-[11px]">Ninguno (Registro Eliminado)</p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 text-[11px] text-gray-500 font-mono flex items-center justify-between">
                                            <span><strong>IP:</strong> {{ $log->ip_address ?? 'N/A' }}</span>
                                            <span class="truncate max-w-xs" title="{{ $log->user_agent }}"><strong>Navegador:</strong> {{ Str::limit($log->user_agent, 40) }}</span>
                                        </div>

                                        <div class="flex justify-end pt-2">
                                            <button type="button" @click="showModal = false"
                                                    class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white font-bold text-xs rounded-xl transition-all">
                                                Cerrar Inspección
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                No se encontraron registros de auditoría que coincidan con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $auditLogs->links() }}
        </div>
    </div>
</div>
@endsection
