<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmpresaActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user('web');

        // El super_admin no depende de ninguna empresa, pasa siempre
        if (!$usuario || $usuario->esSuperAdmin()) {
            return $next($request);
        }

        $empresa = $usuario->empresa;

        if (!$empresa || !$empresa->estaActiva()) {
            return response()->view('empresa.bloqueada', [
                'empresa' => $empresa,
            ]);
        }

        return $next($request);
    }
}