<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Verifica en cada petición que el usuario autenticado tenga estado "Activo".
     * Si el administrador desactivó la cuenta, cierra la sesión de inmediato.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Refrescar estado real desde la base de datos por seguridad
            $freshUser = $user->fresh();

            if (!$freshUser || $freshUser->estado !== 'Activo') {
                $isApi = $request->is('api/*') || $request->expectsJson();

                // Si tiene tokens de API (Sanctum), revocarlos
                if ($freshUser && method_exists($freshUser, 'tokens')) {
                    $freshUser->tokens()->delete();
                }

                // Cerrar sesión web e invalidar cookies/token CSRF
                if (Auth::guard('web')->check()) {
                    Auth::guard('web')->logout();
                }

                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                if ($isApi) {
                    return response()->json([
                        'active'   => false,
                        'success'  => false,
                        'message'  => 'Tu cuenta ha sido desactivada por un administrador. Tu sesión se ha cerrado.',
                        'redirect' => route('login'),
                    ], 401);
                }

                return redirect()->route('login')->withErrors([
                    'correo' => 'Tu cuenta ha sido desactivada por un administrador. Tu sesión fue cerrada.',
                ]);
            }
        }

        return $next($request);
    }
}
