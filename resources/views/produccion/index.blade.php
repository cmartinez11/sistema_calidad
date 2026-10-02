@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Módulo de Producción</h1>
            <p class="text-sm text-gray-500 mt-1">Registro de turnos de producción, mermas y tiempos operativos.</p>
        </div>
        <a href="{{ route('produccion.create') }}" class="px-5 py-2.5 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Registrar Producción</span>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-bold">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-4">Nro / Fechas</th>
                        <th class="px-4 py-4">Turno</th>
                        <th class="px-4 py-4">Producto</th>
                        <th class="px-4 py-4">Máquina / Operario</th>
                        <th class="px-4 py-4 text-right">Cant. Prod.</th>
                        <th class="px-4 py-4 text-right">Total Kg</th>
                        <th class="px-4 py-4 text-right">Millares</th>
                        <th class="px-4 py-4 text-right">Scrap / %</th>
                        <th class="px-4 py-4 text-center">Ciclo</th>
                        <th class="px-4 py-4 text-center">Cavidades</th>
                        <th class="px-4 py-4 text-center">Hrs. Op.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs text-gray-700">
                    @forelse($producciones as $prod)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-4">
                                <span class="font-mono font-bold text-gray-900 block">{{ $prod->nro_produccion }}</span>
                                <span class="text-[10px] text-gray-500 block">Prod: {{ $prod->fecha_produccion }}</span>
                                <span class="text-[10px] text-gray-400 block">Reg: {{ $prod->fecha_registro ?? $prod->created_at->format('Y-m-d') }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $prod->turno == 'DIA' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                    {{ $prod->turno }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-semibold text-gray-900">{{ $prod->producto->nombre ?? 'N/D' }}</td>
                            <td class="px-4 py-4">
                                <span class="block text-gray-800">{{ $prod->maquina->nombre ?? 'N/D' }}</span>
                                <span class="text-[10px] text-gray-400">Op: {{ $prod->operario->nombre ?? 'N/D' }}</span>
                            </td>
                            <td class="px-4 py-4 text-right font-bold">{{ number_format($prod->cantidad_producida, 2) }}</td>
                            <td class="px-4 py-4 text-right font-bold text-fenix">{{ number_format($prod->total_kg, 2) }} kg</td>
                            <td class="px-4 py-4 text-right font-semibold">{{ number_format($prod->total_millares, 2) }}</td>
                            <td class="px-4 py-4 text-right">
                                <span class="font-bold text-red-600 block">{{ number_format($prod->scrap, 2) }} kg</span>
                                <span class="text-[10px] text-gray-500 block">{{ number_format($prod->porcentaje_scrap, 2) }}%</span>
                                @if($prod->motivo_scrap)
                                    <span class="inline-block mt-1 px-1.5 py-0.5 bg-red-50 text-red-700 text-[9px] rounded font-semibold">{{ $prod->motivo_scrap }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center font-mono">{{ $prod->ciclo ? number_format($prod->ciclo, 2) : '-' }}</td>
                            <td class="px-4 py-4 text-center">
                                <span class="font-bold block text-gray-800">{{ $prod->cavidades_trabajadas }} / {{ $prod->cavidades }}</span>
                                <span class="text-[9px] text-gray-400">Trab / Matriz</span>
                            </td>
                            <td class="px-4 py-4 text-center font-mono font-bold text-gray-600">
                                @php
                                    $horasTotal = $prod->horas_operativas; // ej. 9.5
                                    $horasEnteras = floor($horasTotal); // 9
                                    $minutosRestantes = round(($horasTotal - $horasEnteras) * 60); // 30
                                @endphp
                                {{ $horasEnteras }}h {{ $minutosRestantes }}m
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-12 text-center text-gray-400">No hay registros de producción todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $producciones->links() }}
        </div>
    </div>
</div>
@endsection