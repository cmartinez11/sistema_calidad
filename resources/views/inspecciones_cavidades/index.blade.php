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
            <!-- Botón Búsqueda -->
            <form method="GET" action="{{ route('inspecciones-cavidades.index') }}" class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por código, producto..." 
                       class="pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix w-64 transition-all">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </form>

            <!-- Botón Nueva Auditoría -->
            <a href="{{ route('inspecciones-cavidades.create') }}" 
               class="bg-fenix hover:bg-fenix-dark text-white px-5 py-2.5 rounded-xl font-medium text-sm shadow-md hover:shadow-lg transition-all flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva Auditoría</span>
            </a>
        </div>
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
                                @if(($insp->defectos_count ?? 0) > 0)
                                    <span class="inline-flex items-center px-3 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded-full border border-orange-300">
                                        <span class="w-1.5 h-1.5 bg-orange-500 rounded-full mr-1.5"></span> OBSERVADO ({{ $insp->defectos_count }})
                                    </span>
                                @elseif(($insp->pasables_count ?? 0) > 0)
                                    <span class="inline-flex items-center px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full border border-amber-300">
                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-1.5"></span> PASABLE ({{ $insp->pasables_count }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-200">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span> CONFORME
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
