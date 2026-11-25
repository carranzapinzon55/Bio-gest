<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        $isApi = $request->is('api/*') || $request->expectsJson();

        if (!$user) {
            return $isApi
                ? response()->json([
                    'success'  => false,
                    'message'  => 'No autenticado. Token de acceso no proporcionado o inválido.',
                    'redirect' => route('home'),
                ], 401)
                : redirect()->route('home');
        }

        // Obtener el nombre del rol del usuario autenticado
        $nombreRol = optional($user->rol)->nombre_rol ?? ($user->id_rol === 1 ? 'Administrador' : 'Cliente');

        // Procesar roles permitidos (admite parámetros múltiples o separados por comas)
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $allowedRoles[] = strtolower($trimmed);
                }
            }
        }

        // Si se especificaron roles y el rol del usuario no coincide
        if (!empty($allowedRoles) && !in_array(strtolower($nombreRol), $allowedRoles)) {
            return $isApi
                ? response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Se requiere uno de los siguientes roles: ' . implode(', ', $roles) . '.'
                ], 403)
                : abort(403, 'No tienes permisos suficientes para acceder a este módulo.');
        }

        return $next($request);
    }
}
