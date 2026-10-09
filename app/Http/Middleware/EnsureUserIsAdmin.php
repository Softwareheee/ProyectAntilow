<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Impide acceder a rutas administrativas a usuarios sin rol admin. */
class EnsureUserIsAdmin
{
    /** Comprueba el rol antes de continuar con la petición. */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role === 'admin', 403, 'Se requiere una cuenta de administrador.');

        return $next($request);
    }
}