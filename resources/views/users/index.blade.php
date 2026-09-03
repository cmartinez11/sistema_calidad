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

    <!-- TARJETA CABECERA -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Gestión de Usuarios</h2>
            <p class="text-xs text-gray-400 mt-1">Administración de cuentas de acceso, asignación de roles y permisos del sistema Grupo Fénix</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('users.create') }}" 
               class="bg-fenix hover:bg-fenix-dark text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>+ Registrar Nuevo Usuario</span>
            </a>
        </div>
    </div>

    <!-- BUSCADOR -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('users.index') }}" class="flex gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Buscar por nombre, usuario, correo o rol..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-xs focus:ring-fenix focus:border-fenix font-medium">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-xl text-xs font-bold transition-all">
                Buscar
            </button>
        </form>
    </div>

    <!-- TABLA DE USUARIOS -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Usuario / Nombre</th>
                        <th class="px-6 py-4">Correo Electrónico</th>
                        <th class="px-6 py-4 text-center">Rol Asignado</th>
                        <th class="px-6 py-4 text-center">Fecha Registro</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $usr)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900 text-sm">{{ $usr->name }}</div>
                                <div class="text-xs text-gray-400 font-mono">@ {{ $usr->username }}</div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">
                                {{ $usr->email }}
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @php
                                    $roleName = strtoupper($usr->role->nombre ?? 'SIN ROL');
                                @endphp
                                @if($roleName === 'ADMINISTRADOR')
                                    <span class="px-3 py-1 bg-purple-100 text-purple-800 text-[11px] font-bold rounded-full border border-purple-200">
                                        🛡️ ADMINISTRADOR
                                    </span>
                                @elseif($roleName === 'SUPERVISOR')
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-[11px] font-bold rounded-full border border-blue-200">
                                        👨‍💼 SUPERVISOR
                                    </span>
                                @elseif($roleName === 'GERENCIA')
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-full border border-emerald-200">
                                        💼 GERENCIA
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-[11px] font-bold rounded-full border border-gray-200">
                                        👤 {{ $roleName }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center font-mono text-gray-500 whitespace-nowrap">
                                {{ $usr->created_at ? $usr->created_at->format('d/m/Y') : '-' }}
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-2" x-data="{ showDeleteModal: false }">
                                <a href="{{ route('users.edit', $usr->id) }}" 
                                   class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition-all text-xs inline-flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Editar</span>
                                </a>

                                @if($usr->id !== Auth::id())
                                    <button type="button" @click="showDeleteModal = true"
                                            class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-lg transition-all text-xs inline-flex items-center space-x-1">
                                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Eliminar</span>
                                    </button>

                                    <!-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN -->
                                    <div x-show="showDeleteModal" x-cloak
                                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0">
                                        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-left space-y-4 border border-gray-100"
                                             @click.away="showDeleteModal = false">
                                            <div class="flex items-center space-x-3 text-red-600">
                                                <div class="p-3 bg-red-100 rounded-xl">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                </div>
                                                <h3 class="text-base font-bold text-gray-900">¿Eliminar Usuario?</h3>
                                            </div>

                                            <p class="text-xs text-gray-600 leading-relaxed">
                                                ¿Estás seguro de que deseas eliminar al usuario <strong class="text-gray-900">{{ $usr->name }}</strong> ({{ $usr->email }})? Esta acción no se puede deshacer.
                                            </p>

                                            <div class="flex justify-end space-x-3 pt-2">
                                                <button type="button" @click="showDeleteModal = false"
                                                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition-all">
                                                    Cancelar
                                                </button>

                                                <form action="{{ route('users.destroy', $usr->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition-all">
                                                        Sí, Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-[11px] px-2 py-1 bg-gray-100 rounded-md">Sesión Activa</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                No se encontraron usuarios registrados en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
