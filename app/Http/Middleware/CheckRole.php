<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $rolEsperado): Response
    {
        $user = $request->user();

        if (!$user || (!$user->esAdmin() && optional($user->rol)->nombre_rol !== $rolEsperado)) {
            abort(403, 'No tienes permisos para acceder a esta sección de BIO-GEST.');
        }

        return $next($request);
    }
}
