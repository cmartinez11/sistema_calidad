@extends('layouts.app')

@section('content')
<div class="space-y-6">

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

    <!-- TARJETA SUPERIOR CABECERA -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Historial de Auditorías por Cavidades</h2>
            <p class="text-xs text-gray-400 mt-1">Registro histórico de pesajes unitarios cavidad por cavidad y reportes imprimibles</p>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Botón Nueva Auditoría -->
            <a href="{{ route('inspecciones-cavidades.create') }}" 
               class="bg-fenix hover:bg-fenix-dark text-white px-5 py-2.5 rounded-xl font-medium text-sm shadow-md hover:shadow-lg transition-all flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Auditoría</span>
            </a>
        </div>
    </div>

    <!-- PANEL DE FILTROS AVANZADOS -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100" x-data="{ expanded: {{ ($fechaInicio || $fechaFin || $productoId || $lote || $estado) ? 'true' : 'true' }} }">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-fenix flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-800">Filtros Avanzados</h3>
                @if($fechaInicio || $fechaFin || $productoId || $lote || $estado || $search)
                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold rounded-full border border-emerald-300">
                        Filtros Activos
                    </span>
                @endif
            </div>

            @if($fechaInicio || $fechaFin || $productoId || $lote || $estado || $search)
                <a href="{{ route('inspecciones-cavidades.index') }}" 
                   class="text-xs text-red-600 hover:text-red-800 font-medium flex items-center space-x-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Limpiar Filtros</span>
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('inspecciones-cavidades.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Rango de Fechas: Desde -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" 
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-fenix focus:border-fenix text-gray-700 font-medium">
                </div>

                <!-- Rango de Fechas: Hasta -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ $fechaFin }}" 
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-fenix focus:border-fenix text-gray-700 font-medium">
                </div>

                <!-- Filtro por Producto -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Producto</label>
                    <select name="producto_id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-fenix focus:border-fenix text-gray-700 font-medium">
                        <option value="">Todos los Productos</option>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->id }}" {{ (string)$productoId === (string)$prod->id ? 'selected' : '' }}>
                                {{ $prod->codigo }} - {{ $prod->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por Lote -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Número de Lote</label>
                    <input type="text" name="lote" value="{{ $lote }}" placeholder="Ej. PET2636M01" 
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-fenix focus:border-fenix text-gray-700 font-medium">
                </div>

                <!-- Filtro por Estado -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Estado</label>
                    <select name="estado" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-fenix focus:border-fenix text-gray-700 font-medium">
                        <option value="">Todos los Estados</option>
                        <option value="CONFORME" {{ $estado === 'CONFORME' ? 'selected' : '' }}>🟢 CONFORME</option>
                        <option value="OBSERVADO" {{ $estado === 'OBSERVADO' ? 'selected' : '' }}>🟠 OBSERVADO</option>
                        <option value="PNC" {{ $estado === 'PNC' ? 'selected' : '' }}>🔴 PNC</option>
                        <option value="RETENIDO" {{ $estado === 'RETENIDO' ? 'selected' : '' }}>⏳ RETENIDO</option>
                    </select>
                </div>

                <!-- Texto libre / Búsqueda -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Búsqueda General</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Código, Operario..." 
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-fenix focus:border-fenix text-gray-700 font-medium">
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-gray-50">
                <a href="{{ route('inspecciones-cavidades.index') }}" 
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition-all flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Limpiar Filtros</span>
                </a>
                <button type="submit" 
                        class="px-5 py-2 bg-fenix hover:bg-fenix-dark text-white font-bold rounded-xl text-xs shadow-md hover:shadow-lg transition-all flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Filtrar</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TABLA DE HISTORIAL -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Código Auditoría</th>
                        <th class="px-6 py-4">Fecha y Hora</th>
                        <th class="px-6 py-4">Producto</th>
                        <th class="px-6 py-4">Inyectora / Operario</th>
                        <th class="px-6 py-4 text-center">Cavidades Evaluadas</th>
                        <th class="px-6 py-4 text-center">Estado Global</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inspecciones as $insp)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Código Inspección -->
                            <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">
                                <span class="bg-emerald-50 text-fenix px-3 py-1 rounded-lg text-xs font-mono font-bold border border-emerald-200">
                                    {{ $insp->codigo_inspeccion }}
                                </span>
                            </td>

                            <!-- Fecha y Hora -->
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                {{ \Carbon\Carbon::parse($insp->created_at)->format('d/m/Y h:i A') }}
                            </td>

                            <!-- Producto -->
                            <td class="px-6 py-4 font-medium text-gray-800">
                                <div class="font-bold text-gray-900">{{ $insp->producto->codigo ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $insp->producto->nombre ?? '-' }}</div>
                            </td>

                            <!-- Inyectora / Operario -->
                            <td class="px-6 py-4 text-xs whitespace-nowrap">
                                <div><strong class="text-gray-700">Máq:</strong> {{ $insp->maquina->codigo ?? 'N/A' }}</div>
                                <div class="text-gray-400"><strong class="text-gray-600">Ope:</strong> {{ $insp->operario->nombre ?? 'N/A' }}</div>
                            </td>

                            <!-- Cavidades Evaluadas -->
                            <td class="px-6 py-4 text-center whitespace-nowrap font-mono font-bold text-gray-700">
                                {{ $insp->total_cavidades }} Cavidades
                            </td>

                            <!-- Estado Global -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if(!empty($insp->tiene_pnc) || ($insp->estado_evaluacion ?? null) === 'PNC')
                                    <span class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-full shadow-sm">
                                        <span class="w-1.5 h-1.5 bg-white rounded-full mr-1.5 animate-pulse"></span> 🔴 PNC
                                    </span>
                                @elseif(($insp->estado_evaluacion ?? null) === 'OBSERVADO')
                                    <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded-full border border-orange-300">
                                        <span class="w-1.5 h-1.5 bg-orange-500 rounded-full mr-1.5"></span> 🟠 OBSERVADO
                                    </span>
                                @elseif(($insp->defectos_count ?? 0) > 0 && empty($insp->estado_evaluacion))
                                    <span class="inline-flex items-center px-3 py-1 bg-amber-200 text-amber-900 text-xs font-bold rounded-full border border-amber-300" title="Auditoría bloqueada preventivamente hasta definir su flujo de salida">
                                        <span class="w-1.5 h-1.5 bg-amber-600 rounded-full mr-1.5 animate-ping"></span> ⏳ RETENIDO
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-200">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span> 🟢 CONFORME
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('inspecciones-cavidades.show', ['codigo' => $insp->codigo_inspeccion]) }}" class="px-3.5 py-1.5 bg-gray-100 hover:bg-fenix hover:text-white text-gray-700 rounded-xl text-xs font-semibold border border-gray-200 transition-all inline-flex items-center space-x-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Ver / Imprimir PDF</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <span class="text-4xl">🧪</span>
                                    <p class="text-base font-medium text-gray-500">No se encontraron auditorías registradas</p>
                                    <p class="text-xs text-gray-400">Haz clic en "Nueva Auditoría" para registrar el primer pesaje.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        @if($inspecciones->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $inspecciones->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
