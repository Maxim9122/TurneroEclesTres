<?php

namespace App\Support;

use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;

class Contacto
{
    /**
     * Teléfono de contacto de la plataforma: el que tenga cargado el super_admin
     * en su ficha (Mi perfil). Si no cargó ninguno, usa un valor de respaldo.
     */
    public static function telefono(): string
    {
        return Cache::remember('contacto.telefono_superadmin', 300, function () {
            $superAdmin = Usuario::where('rol', 'super_admin')
                ->whereNotNull('telefono')
                ->first();

            return $superAdmin?->telefono ?: '3841670079';
        });
    }
}