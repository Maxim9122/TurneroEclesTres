<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * A dónde mandar a cada usuario según su sesión. Lo usan los middleware
 * 'guest' (ya logueado → su panel) y 'auth' (sin sesión → su login).
 */
class Paneles
{
    public static function esZonaCliente(Request $request): bool
    {
        return $request->is('cuenta', 'cuenta/*');
    }

    /** Panel principal del que ya tiene sesión iniciada. */
    public static function panel(Request $request): string
    {
        if (self::esZonaCliente($request)) {
            return route('cliente.home');
        }

        $usuario = Auth::guard('web')->user();

        return $usuario?->esSuperAdmin()
            ? route('staff.plataforma.dashboard')
            : route('staff.empresa.dashboard');
    }

    /** Login correspondiente para el que entra sin sesión. */
    public static function login(Request $request): string
    {
        return self::esZonaCliente($request) ? route('cliente.login') : route('staff.login');
    }
}
