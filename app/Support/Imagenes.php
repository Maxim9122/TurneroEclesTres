<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Guarda las imágenes que suben los negocios optimizadas para la web: las endereza según la
 * orientación de la cámara, las achica al tamaño en que se muestran y las pasa a WebP.
 *
 * Cada versión lleva un nombre nuevo ("logo-a1b2c3d4.webp") para que el navegador y el CDN nunca
 * muestren una imagen vieja cuando el negocio la cambia. Si la imagen no se puede procesar
 * (SVG, formato raro), se guarda tal cual, como antes.
 */
class Imagenes
{
    /** [ancho máximo, alto máximo] según dónde se usa la imagen. */
    public const LOGO = [400, 400];
    public const FONDO = [1920, 1080];
    public const FOTO = [1200, 1200];

    private const CALIDAD_WEBP = 80;

    /**
     * Guarda la imagen subida en $carpeta (disco "public") y devuelve su ruta.
     * Si $reemplazar es true, borra antes las versiones anteriores con el mismo nombre base.
     */
    public static function guardar(UploadedFile $archivo, string $carpeta, string $nombreBase, array $maximo, bool $reemplazar = true): string
    {
        if ($reemplazar) {
            self::borrarVersiones($carpeta, $nombreBase);
        }

        $contenido = (string) file_get_contents($archivo->getRealPath());
        $webp = self::optimizar($contenido, $maximo);
        $sufijo = '-' . Str::lower(Str::random(8));

        // Si no se pudo procesar, o si la original ya era más liviana, se guarda tal cual.
        if ($webp === null || (strlen($webp) >= strlen($contenido) && self::entraEn($contenido, $maximo))) {
            $extension = strtolower($archivo->getClientOriginalExtension() ?: 'jpg');
            return $archivo->storeAs($carpeta, "{$nombreBase}{$sufijo}.{$extension}", 'public');
        }

        $ruta = "{$carpeta}/{$nombreBase}{$sufijo}.webp";
        Storage::disk('public')->put($ruta, $webp);

        return $ruta;
    }

    /** Borra "logo.jpg", "logo-xxxx.webp", etc. (todas las versiones de ese nombre base). */
    public static function borrarVersiones(string $carpeta, string $nombreBase): void
    {
        foreach (Storage::disk('public')->files($carpeta) as $existente) {
            $nombre = basename($existente);
            if (str_starts_with($nombre, $nombreBase . '.') || str_starts_with($nombre, $nombreBase . '-')) {
                Storage::disk('public')->delete($existente);
            }
        }
    }

    /**
     * Devuelve la imagen como WebP achicada a $maximo, o null si no se puede procesar.
     * Nunca la agranda.
     */
    public static function optimizar(string $contenido, array $maximo): ?string
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp') || $contenido === '') {
            return null;
        }

        $original = @imagecreatefromstring($contenido);
        if ($original === false) {
            return null;
        }

        $original = self::enderezar($original, $contenido);

        [$ancho, $alto] = [imagesx($original), imagesy($original)];
        $escala = min(1, $maximo[0] / $ancho, $maximo[1] / $alto);
        $nuevoAncho = max(1, (int) round($ancho * $escala));
        $nuevoAlto = max(1, (int) round($alto * $escala));

        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        // Conserva la transparencia (logos PNG).
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
        imagefill($destino, 0, 0, imagecolorallocatealpha($destino, 0, 0, 0, 127));
        imagecopyresampled($destino, $original, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

        ob_start();
        $ok = imagewebp($destino, null, self::CALIDAD_WEBP);
        $webp = ob_get_clean();

        imagedestroy($original);
        imagedestroy($destino);

        return $ok && $webp !== '' ? $webp : null;
    }

    /** ¿La imagen ya cabe en el tamaño máximo (no hace falta achicarla)? */
    private static function entraEn(string $contenido, array $maximo): bool
    {
        $info = @getimagesizefromstring($contenido);

        return $info !== false && $info[0] <= $maximo[0] && $info[1] <= $maximo[1];
    }

    /** Las fotos de celular guardan la orientación aparte (EXIF): se aplica para que no queden giradas. */
    private static function enderezar(\GdImage $imagen, string $contenido): \GdImage
    {
        if (!function_exists('exif_read_data') || !str_starts_with($contenido, "\xFF\xD8")) {
            return $imagen; // solo los JPEG traen orientación EXIF
        }

        $exif = @exif_read_data('data://image/jpeg;base64,' . base64_encode($contenido));
        $orientacion = (int) ($exif['Orientation'] ?? 1);

        $resultado = match ($orientacion) {
            3 => imagerotate($imagen, 180, 0),
            6 => imagerotate($imagen, -90, 0),
            8 => imagerotate($imagen, 90, 0),
            default => $imagen,
        };

        if ($orientacion === 2 || $orientacion === 4) {
            imageflip($resultado, $orientacion === 2 ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
        }

        return $resultado ?: $imagen;
    }
}
