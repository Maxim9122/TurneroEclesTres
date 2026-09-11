<?php

namespace App\Support;

class WhatsApp
{
    /**
     * Arma un link "wa.me" a partir de un teléfono argentino cargado en cualquier formato,
     * y un mensaje ya redactado.
     */
    public static function linkChat(?string $telefono, string $mensaje): ?string
    {
        if (!$telefono) {
            return null;
        }

        $numero = preg_replace('/\D+/', '', $telefono); // deja solo dígitos

        if ($numero === '') {
            return null;
        }

        // Si no arranca con el código de país de Argentina, se lo agregamos.
        if (!str_starts_with($numero, '549') && !str_starts_with($numero, '54')) {
            $numero = '549' . ltrim($numero, '0'); // saca el 0 de área si lo tenía
        } elseif (str_starts_with($numero, '54') && !str_starts_with($numero, '549')) {
            $numero = '549' . substr($numero, 2);
        }

        return 'https://wa.me/' . $numero . '?text=' . urlencode($mensaje);
    }
}