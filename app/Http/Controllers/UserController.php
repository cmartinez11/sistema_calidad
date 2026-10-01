<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Muestra el listado paginado de usuarios con búsqueda.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $users = User::with('role')
            ->when($search, function ($query, $search) {
                $query->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('username', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhereHas('role', fn($q) => $q->where('nombre', 'ILIKE', "%{$search}%"));
            })
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    /**
     * Muestra el formulario para registrar un nuevo usuario.
     */
    public function create(): View
    {
        $roles = Role::where('estado', 'activo')->orderBy('nombre', 'asc')->get();

        return view('users.create', compact('roles'));
    }

    /**
     * Guarda un nuevo usuario en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'El nombre de usuario ya se encuentra registrado.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'El correo electrónico ya se encuentra registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'role_id.required' => 'El rol de usuario es obligatorio.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'accion' => 'CREAR_USUARIO',
            'descripcion' => "Se registró el nuevo usuario {$user->name} ({$user->username})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('users.index')
            ->with('success', "¡El usuario {$user->name} ha sido creado exitosamente!");
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     */
    public function edit(User $user): View
    {
        $roles = Role::where('estado', 'activo')->orderBy('nombre', 'asc')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Actualiza los datos de un usuario en la base de datos.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'El nombre de usuario ya está en uso por otro usuario.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'El correo electrónico ya está en uso por otro usuario.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'role_id.required' => 'El rol de usuario es obligatorio.',
        ]);

        $userData = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'accion' => 'ACTUALIZAR_USUARIO',
            'descripcion' => "Se actualizaron los datos del usuario ID {$user->id} ({$user->name})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('users.index')
            ->with('success', "¡El usuario {$user->name} ha sido actualizado correctamente!");
    }

    /**
     * Elimina un usuario del sistema.
     */
    public function destroy(User $user, Request $request): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario mientras mantienes la sesión activa.');
        }

        $userName = $user->name;
        $user->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'accion' => 'ELIMINAR_USUARIO',
            'descripcion' => "Se eliminó al usuario {$userName} del sistema",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('users.index')
            ->with('success', "El usuario {$userName} fue eliminado correctamente del sistema.");
    }
}
