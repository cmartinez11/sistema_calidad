@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header y Acciones -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <a href="{{ route('atc.index') }}" class="inline-flex items-center text-xs font-bold text-gray-500 hover:text-emerald-700 transition-colors mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver a la lista de ATC
            </a>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black text-gray-900 font-mono tracking-tight">{{ $atc->correlativo }}</h1>
                @if($atc->tipo === 'Reclamo')
                    <span class="px-3 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-full text-xs font-bold">
                        Reclamo
                    </span>
                @else
                    <span class="px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-bold">
                        Devolución
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-400 font-medium mt-1">Registrado el {{ $atc->fecha ? $atc->fecha->format('d/m/Y') : '-' }} por {{ $atc->user->name ?? 'Usuario de Calidad' }}</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('atc.pdf', $atc->id) }}" target="_blank"
               class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Descargar PDF</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-sm flex items-center space-x-3">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-bold text-emerald-800">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Ficha del Reporte -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden divide-y divide-gray-100">
        <!-- Sección Cliente -->
        <div class="p-6 space-y-4">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Información del Cliente</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                <div>
                    <span class="block text-[11px] font-bold text-gray-400">Cliente / Razón Social</span>
                    <span class="text-sm font-black text-gray-900">{{ $atc->cliente_nombre }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold text-gray-400">RUC del Cliente</span>
                    <span class="text-sm font-mono font-bold text-gray-800">{{ $atc->cliente_ruc ?? 'No especificado' }}</span>
                </div>
            </div>
        </div>

        <!-- Sección Detalle del Producto -->
        <div class="p-6 space-y-4">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Detalles del Producto y Defecto</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                    <span class="block text-[11px] font-bold text-gray-400">Producto</span>
                    <span class="text-xs font-bold text-gray-900">{{ $atc->producto }}</span>
                </div>
                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                    <span class="block text-[11px] font-bold text-gray-400">Cantidad Afectada</span>
                    <span class="text-sm font-black text-gray-900">{{ number_format($atc->cantidad, 2) }}</span>
                </div>
                <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                    <span class="block text-[11px] font-bold text-gray-400">Lote</span>
                    <span class="text-xs font-mono font-bold text-gray-800">{{ $atc->lote ?? 'N/A' }}</span>
                </div>
            </div>

            <div>
                <span class="block text-[11px] font-bold text-gray-500 mb-1">Descripción del Reclamo / Devolución</span>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-xs font-medium text-gray-800 leading-relaxed whitespace-pre-line">
                    {{ $atc->descripcion }}
                </div>
            </div>
        </div>

        <!-- Sección PNC Vinculado -->
        @if($atc->pnc)
            <div class="p-6 space-y-4 bg-amber-50/30">
                <h2 class="text-xs font-bold text-amber-700 uppercase tracking-wider flex items-center">
                    <svg class="w-4 h-4 mr-1 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Producto No Conforme (PNC) Vinculado
                </h2>
                <div class="bg-white p-4 rounded-xl border border-amber-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-mono font-black text-amber-800">{{ $atc->pnc->codigo_pnc }}</span>
                        <p class="text-xs text-gray-600 mt-0.5">Estado: <span class="font-bold uppercase text-amber-700">{{ $atc->pnc->estado_pnc }}</span></p>
                    </div>
                    <a href="{{ route('pnc.show', $atc->pnc->id) }}" class="px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-800 rounded-lg text-xs font-bold transition-all">
                        Ver Reporte PNC
                    </a>
                </div>
            </div>
        @endif

        <!-- Sección Acciones y Plan de Acción -->
        <div class="p-6 space-y-6">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Acciones Implementadas y Plan de Acción</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <span class="block text-[11px] font-bold text-gray-500 mb-1">Acción Correctiva</span>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-xs font-medium text-gray-800 leading-relaxed whitespace-pre-line min-h-[100px]">
                        {{ $atc->accion_correctiva ?: 'Sin acción correctiva registrada.' }}
                    </div>
                </div>

                <div>
                    <span class="block text-[11px] font-bold text-gray-500 mb-1">Plan de Acción / Causa Raíz</span>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-xs font-medium text-gray-800 leading-relaxed whitespace-pre-line min-h-[100px]">
                        {{ $atc->plan_accion ?: 'Sin plan de acción registrado.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
