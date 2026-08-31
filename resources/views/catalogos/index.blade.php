@extends('layouts.app')

@section('content')
<div x-data="{ tab: 'maquinas' }" class="space-y-6">

    <!-- CABECERA Y TÍTULO -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Catálogos del Sistema</h2>
            <p class="text-xs text-gray-400 mt-1">Administración centralizada de máquinas, moldes, resinas y operarios de planta.</p>
        </div>
    </div>

    <!-- PESTAÑAS DE NAVEGACIÓN INTERNA -->
    <div class="flex border-b border-gray-200 space-x-6 bg-white px-6 pt-4 rounded-t-2xl shadow-sm">
        <button @click="tab = 'maquinas'" 
                :class="tab === 'maquinas' ? 'border-fenix text-fenix font-bold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="pb-3 border-b-2 text-sm transition-all focus:outline-none flex items-center space-x-2">
            <span>⚙️ Máquinas</span>
        </button>
        <button @click="tab = 'moldes'" 
                :class="tab === 'moldes' ? 'border-fenix text-fenix font-bold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="pb-3 border-b-2 text-sm transition-all focus:outline-none flex items-center space-x-2">
            <span>🧱 Moldes</span>
        </button>
        <button @click="tab = 'resinas'" 
                :class="tab === 'resinas' ? 'border-fenix text-fenix font-bold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="pb-3 border-b-2 text-sm transition-all focus:outline-none flex items-center space-x-2">
            <span>🧪 Resinas</span>
        </button>
        <button @click="tab = 'operarios'" 
                :class="tab === 'operarios' ? 'border-fenix text-fenix font-bold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="pb-3 border-b-2 text-sm transition-all focus:outline-none flex items-center space-x-2">
            <span>👥 Operarios</span>
        </button>
    </div>

    <!-- CONTENIDO DE LAS PESTAÑAS -->
    <div class="bg-white p-6 rounded-b-2xl shadow-sm border border-gray-100">

        <!-- 1. SECCIÓN MÁQUINAS -->
        <div x-show="tab === 'maquinas'" class="space-y-4" x-cloak>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h3 class="text-base font-bold text-gray-800">Listado de Máquinas / Inyectoras</h3>
                <a href="{{ route('maquinas.create') }}" 
                   class="px-4 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-xs font-bold shadow-md transition-all flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nueva Máquina</span>
                </a>
            </div>
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-100 border-b border-gray-200 text-gray-700 font-bold uppercase">
                        <tr>
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($maquinas as $maq)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 font-mono font-bold">{{ $maq->codigo }}</td>
                            <td class="px-4 py-2.5">{{ $maq->nombre }}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-bold text-[10px]">{{ $maq->estado }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. SECCIÓN MOLDES -->
        <div x-show="tab === 'moldes'" class="space-y-4" x-cloak>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h3 class="text-base font-bold text-gray-800">Listado de Moldes</h3>
                <a href="{{ route('moldes.create') }}" 
                   class="px-4 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-xs font-bold shadow-md transition-all flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo Molde</span>
                </a>
            </div>
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-100 border-b border-gray-200 text-gray-700 font-bold uppercase">
                        <tr>
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Cavidades</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($moldes as $mol)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 font-mono font-bold">{{ $mol->codigo }}</td>
                            <td class="px-4 py-2.5">{{ $mol->nombre }}</td>
                            <td class="px-4 py-2.5 font-mono font-bold text-fenix">{{ $mol->numero_cavidades }} Cav.</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. SECCIÓN RESINAS -->
        <div x-show="tab === 'resinas'" class="space-y-4" x-cloak>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h3 class="text-base font-bold text-gray-800">Listado de Resinas</h3>
                <a href="{{ route('resinas.create') }}" 
                   class="px-4 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-xs font-bold shadow-md transition-all flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nueva Resina</span>
                </a>
            </div>
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-100 border-b border-gray-200 text-gray-700 font-bold uppercase">
                        <tr>
                            <th class="px-4 py-3">Código</th>
                            <th class="px-4 py-3">Nombre de Resina</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($resinas as $res)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 font-mono font-bold">{{ $res->codigo }}</td>
                            <td class="px-4 py-2.5">{{ $res->nombre }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. SECCIÓN OPERARIOS -->
        <div x-show="tab === 'operarios'" class="space-y-4" x-cloak>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h3 class="text-base font-bold text-gray-800">Listado de Operarios / Encargados</h3>
                <a href="{{ route('operarios.create') }}" 
                   class="px-4 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-xs font-bold shadow-md transition-all flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo Operario</span>
                </a>
            </div>
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-100 border-b border-gray-200 text-gray-700 font-bold uppercase">
                        <tr>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Código Operario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($operarios as $ope)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 font-bold">{{ $ope->nombre }}</td>
                            <td class="px-4 py-2.5 font-mono">{{ $ope->codigo_operario ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection