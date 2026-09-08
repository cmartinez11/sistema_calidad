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

    <!-- CABECERA -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Reportes de Producto No Conforme (PNC)</h2>
            <p class="text-xs text-gray-400 mt-1">Gestión documental oficial bajo el formato metrológico FE-SIG-FOR-30-V de Grupo Fénix</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('pnc.create') }}" 
               class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Emitir Nuevo Reporte PNC</span>
            </a>
        </div>
    </div>

    <!-- PANEL DE FILTROS AVANZADOS -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-800">Filtros Avanzados PNC</h3>
                @if($fechaInicio || $fechaFin || $productoId || $lote || $estado || $search)
                    <span class="px-2.5 py-0.5 bg-red-100 text-red-800 text-[10px] font-extrabold rounded-full border border-red-300">
                        Filtros Activos
                    </span>
                @endif
            </div>

            @if($fechaInicio || $fechaFin || $productoId || $lote || $estado || $search)
                <a href="{{ route('pnc.index') }}" 
                   class="text-xs text-red-600 hover:text-red-800 font-medium flex items-center space-x-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Limpiar Filtros</span>
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('pnc.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Rango de Fechas: Desde -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" 
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-red-500 focus:border-red-500 text-gray-700 font-medium">
                </div>

                <!-- Rango de Fechas: Hasta -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ $fechaFin }}" 
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-red-500 focus:border-red-500 text-gray-700 font-medium">
                </div>

                <!-- Filtro por Producto -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Producto</label>
                    <select name="producto_id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-red-500 focus:border-red-500 text-gray-700 font-medium">
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
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-red-500 focus:border-red-500 text-gray-700 font-medium">
                </div>

                <!-- Filtro por Estado PNC -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Estado PNC</label>
                    <select name="estado" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-red-500 focus:border-red-500 text-gray-700 font-medium">
                        <option value="">Todos los Estados</option>
                        <option value="PENDIENTE" {{ $estado === 'PENDIENTE' ? 'selected' : '' }}>⏳ PENDIENTE</option>
                        <option value="PROCESADO" {{ $estado === 'PROCESADO' ? 'selected' : '' }}>✅ PROCESADO</option>
                    </select>
                </div>

                <!-- Texto libre / Búsqueda -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider mb-1">Búsqueda General</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Código PNC, Auditoría..." 
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-red-500 focus:border-red-500 text-gray-700 font-medium">
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-gray-50">
                <a href="{{ route('pnc.index') }}" 
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition-all flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Limpiar Filtros</span>
                </a>
                <button type="submit" 
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-md hover:shadow-lg transition-all flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Filtrar</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TABLA DE REPORTES PNC -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Código PNC</th>
                        <th class="px-4 py-3.5">Inspección Asoc.</th>
                        <th class="px-4 py-3.5">Fecha</th>
                        <th class="px-4 py-3.5">Producto</th>
                        <th class="px-4 py-3.5">Lote</th>
                        <th class="px-4 py-3.5 text-center">Cantidad No Conforme</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-4 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pncs as $pnc)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-4 py-3 font-mono font-bold text-red-700">
                                {{ $pnc->codigo_pnc }}
                            </td>
                            <td class="px-4 py-3 font-mono font-medium text-gray-600">
                                {{ $pnc->codigo_inspeccion ?: '-' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-500">
                                {{ $pnc->fecha ? $pnc->fecha->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-800">
                                {{ $pnc->producto->codigo ?? '' }} - {{ $pnc->producto->nombre ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-600">
                                {{ $pnc->lote->codigo_lote ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-gray-900">
                                {{ number_format($pnc->cantidad, 2) }} {{ $pnc->unidad_medida }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($pnc->estado_pnc === 'PROCESADO')
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold rounded-full border border-emerald-300 inline-flex items-center space-x-1 shadow-2xs">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>PROCESADO</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-[10px] font-extrabold rounded-full border border-amber-300">
                                        {{ $pnc->estado_pnc }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                @if(auth()->user()?->hasRole('Supervisor|Administrador') && $pnc->estado_pnc !== 'PROCESADO')
                                    <form action="{{ route('pnc.procesar', $pnc->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de cambiar el estado de la PNC {{ $pnc->codigo_pnc }} a PROCESADO?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition-all text-xs inline-flex items-center space-x-1 shadow-sm cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>PROCESAR PNC</span>
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('pnc.show', $pnc->id) }}" 
                                   class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition-all text-xs inline-flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Ver Formato</span>
                                </a>
                                <a href="{{ route('pnc.pdf', $pnc->id) }}" target="_blank"
                                   class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-lg transition-all text-xs inline-flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>PDF</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400">
                                No se encontraron reportes de Producto No Conforme (PNC).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $pncs->links() }}
        </div>
    </div>
</div>
@endsection
