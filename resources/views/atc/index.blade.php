@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Encabezado Principal y Botón Nuevo Registro -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-emerald-50 text-emerald-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Atención al Cliente (ATC)</h1>
                    <p class="text-sm text-gray-500 font-medium">Registro y gestión de reclamos y devoluciones de clientes</p>
                </div>
            </div>
        </div>

        <a href="{{ route('atc.create') }}" 
           class="inline-flex items-center justify-center px-5 py-3 bg-fenix hover:bg-emerald-800 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Nuevo Registro ATC</span>
        </a>
    </div>

    <!-- Mensajes de Alerta (Flash Messages) -->
    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-bold text-emerald-800">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-bold text-rose-800">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Tarjetas de Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Registros ATC</p>
                <p class="text-3xl font-black text-gray-900 mt-1">{{ number_format($totalCount) }}</p>
            </div>
            <div class="p-3 bg-gray-100 text-gray-600 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-rose-500 uppercase tracking-wider">Reclamos Registrados</p>
                <p class="text-3xl font-black text-rose-600 mt-1">{{ number_format($reclamosCount) }}</p>
            </div>
            <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-500 uppercase tracking-wider">Devoluciones Registradas</p>
                <p class="text-3xl font-black text-amber-600 mt-1">{{ number_format($devolucionesCount) }}</p>
            </div>
            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filtros y Buscador -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('atc.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Buscar -->
            <div class="md:col-span-1">
                <label class="block text-xs font-bold text-gray-600 mb-1">Buscar (Cliente, RUC, Correlativo)</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ej: ATC2026-001 o Empresa..." 
                           class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Tipo</label>
                <select name="tipo" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
                    <option value="">Todos los Tipos</option>
                    <option value="Reclamo" {{ $tipo == 'Reclamo' ? 'selected' : '' }}>Reclamo</option>
                    <option value="Devolucion" {{ $tipo == 'Devolucion' ? 'selected' : '' }}>Devolución</option>
                </select>
            </div>

            <!-- Fecha Inicio -->
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Fecha Desde</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" 
                       class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-fenix focus:bg-white transition-all">
            </div>

            <!-- Botones Filtrar / Limpiar -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-bold shadow transition-all">
                    Filtrar
                </button>
                <a href="{{ route('atc.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-bold transition-all">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Registros ATC -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Correlativo</th>
                        <th class="py-4 px-6">Fecha</th>
                        <th class="py-4 px-6">Cliente</th>
                        <th class="py-4 px-6">Tipo</th>
                        <th class="py-4 px-6">Producto / Lote</th>
                        <th class="py-4 px-6 text-right">Cantidad</th>
                        <th class="py-4 px-6">Registrado por</th>
                        <th class="py-4 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($atcs as $atc)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-emerald-700">
                                {{ $atc->correlativo }}
                            </td>
                            <td class="py-4 px-6 text-gray-600 font-medium whitespace-nowrap">
                                {{ $atc->fecha ? $atc->fecha->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-gray-800">{{ $atc->cliente_nombre }}</div>
                                @if($atc->cliente_ruc)
                                    <div class="text-[10px] text-gray-400 font-mono">RUC: {{ $atc->cliente_ruc }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($atc->tipo === 'Reclamo')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                                        Reclamo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                        Devolución
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-gray-700">{{ $atc->producto }}</div>
                                @if($atc->lote)
                                    <div class="text-[10px] text-gray-400 font-mono">Lote: {{ $atc->lote }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-bold text-gray-900">
                                {{ number_format($atc->cantidad, 2) }}
                            </td>
                            <td class="py-4 px-6 text-gray-600 font-medium">
                                {{ $atc->user->name ?? 'Sistema' }}
                            </td>
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1">
                                    <a href="{{ route('atc.show', $atc->id) }}" 
                                       title="Ver Detalle"
                                       class="p-1.5 text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('atc.pdf', $atc->id) }}" 
                                       title="Exportar PDF"
                                       target="_blank"
                                       class="p-1.5 text-gray-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-400 font-medium">
                                No se encontraron registros de Atención al Cliente (ATC).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($atcs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $atcs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
