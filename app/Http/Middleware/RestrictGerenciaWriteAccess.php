<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictGerenciaWriteAccess
{
    /**
     * Intercepta y bloquea peticiones de escritura (POST, PUT, PATCH, DELETE) para el rol de Gerencia.
     * Retorna error de autorización 403.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Excluir explícitamente el cierre de sesión (logout)
        if ($request->is('logout') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Verificar si el usuario está autenticado, tiene rol Gerencia y la petición NO es un método seguro (GET / HEAD)
        if ($user && $user->isGerencia() && !$request->isMethodSafe()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Acceso denegado. El rol de Gerencia posee acceso exclusivamente de lectura (consulta).'
                ], 403);
            }

            abort(403, 'Acceso denegado (403): El rol de Gerencia cuenta con permisos exclusivos de lectura. No tiene autorizadas operaciones de creación, modificación o eliminación.');
        }

        return $next($request);
    }
}
