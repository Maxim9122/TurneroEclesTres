<?php

namespace App\Support;

use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;

class Contacto
{
    private const CACHE_KEY = 'contacto.superadmin';

    /**
     * Teléfono de contacto de la plataforma: el que tenga cargado el super_admin
     * en su ficha (Mi perfil). Si no cargó ninguno, usa un valor de respaldo.
     */
    public static function telefono(): string
    {
        return self::datos()['telefono'] ?: '3841670079';
    }

    /**
     * Email público de contacto (pie de página): el "email de contacto" del super_admin,
     * o su email de ingreso si no cargó uno aparte.
     */
    public static function email(): ?string
    {
        return self::datos()['email'];
    }

    /**
     * Llamar después de que el super_admin cambie sus datos, para que se vean enseguida.
     */
    public static function olvidar(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private static function datos(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            $superAdmin = Usuario::where('rol', 'super_admin')->orderBy('id')->first();

            return [
                'telefono' => $superAdmin?->telefono,
                'email' => $superAdmin?->email_contacto ?: $superAdmin?->email,
            ];
        });
    }
}
