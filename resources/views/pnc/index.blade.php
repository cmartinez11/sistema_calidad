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

    <!-- BUSCADOR -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('pnc.index') }}" class="flex gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Buscar por código PNC, código auditoría, producto o lote..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-xs focus:ring-fenix focus:border-fenix font-medium">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-xl text-xs font-bold transition-all">
                Buscar
            </button>
        </form>
    </div>

    <!-- TABLA DE REPORTES PNC -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Código PNC</th>
                        <th class="px-4 py-3.5">Auditoría Asoc.</th>
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
                                <span class="px-2.5 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-full border border-red-200">
                                    {{ $pnc->estado_pnc }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
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
