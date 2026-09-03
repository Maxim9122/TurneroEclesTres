<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUsuarioRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user('web'); // guard 'web' = staff

        if (!$usuario || !in_array($usuario->rol, $roles, true)) {
            abort(403, 'No tenés permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}