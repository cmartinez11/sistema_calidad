@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Clientes SIF</h1>
            <p class="text-sm text-gray-500">Listado sincronizado en tiempo real desde el sistema comercial (Solo Lectura).</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            {{ count($clientes) }} Clientes encontrados
        </span>
    </div>

    <!-- Formulario de Búsqueda -->
    <div class="bg-white shadow rounded-lg p-4 border border-gray-200">
        <form method="GET" action="{{ route('clientes.sif') }}" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por RUC o Razón Social..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Buscar
                </button>
                @if(!empty($search))
                    <a href="{{ route('clientes.sif') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Resultados -->
    <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-600 font-semibold uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">RUC</th>
                        <th class="px-6 py-3 text-left">Razón Social</th>
                        <th class="px-6 py-3 text-left">Dirección</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($clientes as $cliente)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                {{ $cliente['ruc'] ?? 'S/N' }}
                            </td>
                            <td class="px-6 py-4 text-gray-800 font-semibold">
                                {{ $cliente['nombre'] ?? 'Sin Nombre' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $cliente['direccion'] ?? 'No registrada' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                No se encontraron registros que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection