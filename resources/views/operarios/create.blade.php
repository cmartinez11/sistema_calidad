@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
        <div class="flex justify-between items-center border-b pb-4">
            <h2 class="text-lg font-bold text-gray-800">Registrar Nuevo Operario</h2>
            <a href="{{ route('catalogos.index') }}" class="text-xs text-gray-500 hover:text-gray-700 font-bold">← Volver a Catálogos</a>
        </div>

        <form action="{{ route('operarios.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre Completo</label>
                <input type="text" name="nombre" required placeholder="Ej. Juan Pérez" 
                       class="w-full rounded-xl border-gray-300 text-xs shadow-sm focus:border-fenix focus:ring-fenix">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Código de Operario</label>
                <input type="text" name="codigo_operario" placeholder="Ej. OP-001" 
                       class="w-full rounded-xl border-gray-300 text-xs shadow-sm focus:border-fenix focus:ring-fenix font-mono">
            </div>

            <div class="flex justify-end space-x-2 pt-4">
                <a href="{{ route('catalogos.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-all">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-xs font-bold shadow-md transition-all">Guardar Operario</button>
            </div>
        </form>
    </div>
</div>
@endsection