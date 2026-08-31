@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100 space-y-6">
        
        <!-- CABECERA DE FORMULARIO -->
        <div class="flex justify-between items-center border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 tracking-tight">
                    Editar Resina
                </h2>
                <p class="text-xs text-gray-400 mt-1">
                    Modifica la información técnica de la resina registrada
                </p>
            </div>
            <a href="{{ route('resinas.index') }}" 
               class="text-xs font-bold text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1">
                <span>← Volver al Listado</span>
            </a>
        </div>

        <form action="{{ route('resinas.update', $resina->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Código -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Código de Resina *</label>
                <input type="text" name="codigo" value="{{ old('codigo', $resina->codigo) }}" placeholder="Ej. PP-H03" required
                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix font-mono font-bold">
                @error('codigo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Nombre -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nombre de la Resina *</label>
                <input type="text" name="nombre" value="{{ old('nombre', $resina->nombre) }}" placeholder="Ej. Polipropileno Homopolímero" required
                       class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-fenix focus:ring-1 focus:ring-fenix font-medium">
                @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Activo Checkbox -->
            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" name="activo" id="activo_resina_edit" value="1" 
                       {{ old('activo', $resina->activo) ? 'checked' : '' }}
                       class="w-4 h-4 text-fenix rounded border-gray-300 focus:ring-fenix">
                <label for="activo_resina_edit" class="text-sm font-medium text-gray-700">Resina Habilitada / Activa</label>
            </div>

            <!-- BOTONES DE ACCIÓN -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-100">
                <a href="{{ route('resinas.index') }}" 
                   class="px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transition-all">
                    Actualizar Resina
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
