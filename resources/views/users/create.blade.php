@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- BOTÓN VOLVER Y TÍTULO -->
    <div class="flex items-center justify-between">
        <a href="{{ route('users.index') }}" 
           class="text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-200 px-4 py-2 rounded-xl transition-all shadow-sm flex items-center space-x-2">
            <span>← Volver al Listado</span>
        </a>

        <h2 class="text-xl font-bold text-gray-800 tracking-tight">Registrar Nuevo Usuario</h2>
    </div>

    <!-- ERRORES DE VALIDACIÓN -->
    @if($errors->any())
        <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded-r-xl shadow-sm space-y-1">
            <span class="font-bold text-xs">Por favor corrige los siguientes errores:</span>
            <ul class="list-disc list-inside text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- TARJETA DEL FORMULARIO -->
    <form action="{{ route('users.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf

        <div class="p-6 space-y-6">
            <div class="border-b border-gray-100 pb-4">
                <h3 class="text-sm font-extrabold text-gray-800 uppercase tracking-wider flex items-center space-x-2">
                    <span class="w-2 h-2 bg-fenix rounded-full"></span>
                    <span>Información del Usuario</span>
                </h3>
                <p class="text-xs text-gray-400 mt-1">Completa los campos obligatorios para dar de alta una nueva cuenta en el sistema Grupo Fénix.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nombre Completo -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nombre Completo *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="Ej. Juan Pérez"
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-900 focus:ring-fenix focus:border-fenix">
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nombre de Usuario (Username) *</label>
                    <input type="text" name="username" value="{{ old('username') }}" required
                           placeholder="Ej. jperez"
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-mono font-semibold text-gray-900 focus:ring-fenix focus:border-fenix">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Correo Electrónico *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="Ej. jperez@grupofenix.com"
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-900 focus:ring-fenix focus:border-fenix">
                </div>

                <!-- Rol -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Rol Asignado *</label>
                    <select name="role_id" required class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-900 focus:ring-fenix focus:border-fenix">
                        <option value="">-- Seleccionar Rol --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Contraseña *</label>
                    <input type="password" name="password" required
                           placeholder="Mínimo 6 caracteres"
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-900 focus:ring-fenix focus:border-fenix">
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Confirmar Contraseña *</label>
                    <input type="password" name="password_confirmation" required
                           placeholder="Repite la contraseña"
                           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-xs font-semibold text-gray-900 focus:ring-fenix focus:border-fenix">
                </div>
            </div>

            <!-- BOTONES -->
            <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                <a href="{{ route('users.index') }}" 
                   class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                    Cancelar
                </a>

                <button type="submit" 
                        class="px-6 py-2.5 bg-fenix hover:bg-fenix-dark text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Guardar Usuario</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
