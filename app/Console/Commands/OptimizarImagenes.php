<?php

namespace App\Console\Commands;

use App\Support\Imagenes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Optimiza las imágenes ya subidas (logos, fondos y fotos): las achica y las pasa a WebP,
 * actualiza la ruta en la base y borra el archivo original. Las nuevas ya se suben optimizadas.
 *
 *   php artisan imagenes:optimizar --simular   (muestra qué haría, sin cambiar nada)
 *   php artisan imagenes:optimizar
 */
class OptimizarImagenes extends Command
{
    protected $signature = 'imagenes:optimizar {--simular : Solo muestra lo que haría}';

    protected $description = 'Achica y pasa a WebP las imágenes ya subidas por los negocios';

    /** [tabla, columna, tamaño máximo] */
    private const DESTINOS = [
        ['empresas', 'logo_path', Imagenes::LOGO],
        ['empresas', 'imagen_fondo_path', Imagenes::FONDO],
        ['servicios', 'foto_path', Imagenes::FOTO],
        ['servicio_imagenes', 'path', Imagenes::FOTO],
        ['productos', 'foto_path', Imagenes::FOTO],
        ['producto_imagenes', 'path', Imagenes::FOTO],
        ['profesionales', 'foto_path', Imagenes::FOTO],
    ];

    public function handle(): int
    {
        $simular = (bool) $this->option('simular');
        $disco = Storage::disk('public');
        $antes = 0;
        $despues = 0;
        $procesadas = 0;

        foreach (self::DESTINOS as [$tabla, $columna, $maximo]) {
            $filas = DB::table($tabla)->whereNotNull($columna)->where($columna, '!=', '')->get(['id', $columna]);

            foreach ($filas as $fila) {
                $ruta = $fila->{$columna};

                if (str_ends_with(strtolower($ruta), '.webp') || str_ends_with(strtolower($ruta), '.svg')) {
                    continue; // ya optimizada o vectorial
                }

                if (!$disco->exists($ruta)) {
                    $this->warn("No existe el archivo: {$ruta} ({$tabla} #{$fila->id})");
                    continue;
                }

                $original = $disco->get($ruta);
                $webp = Imagenes::optimizar($original, $maximo);

                if ($webp === null) {
                    $this->warn("No se pudo procesar: {$ruta}");
                    continue;
                }

                if (strlen($webp) >= strlen($original)) {
                    continue; // ya era liviana: no vale la pena cambiarla
                }

                $antes += strlen($original);
                $despues += strlen($webp);
                $procesadas++;

                $this->line(sprintf('%-48s %7s KB → %5s KB', $ruta, number_format(strlen($original) / 1024, 0, ',', '.'), number_format(strlen($webp) / 1024, 0, ',', '.')));

                if ($simular) {
                    continue;
                }

                $base = preg_replace('/-[a-z0-9]{8}$/', '', pathinfo($ruta, PATHINFO_FILENAME));
                $nueva = trim(dirname($ruta), '/.') . '/' . $base . '-' . Str::lower(Str::random(8)) . '.webp';

                $disco->put($nueva, $webp);
                // Sin tocar updated_at: no es un cambio del negocio.
                DB::table($tabla)->where('id', $fila->id)->update([$columna => $nueva]);
                $disco->delete($ruta);
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d imágenes: %s KB → %s KB',
            $simular ? 'Se optimizarían' : 'Optimizadas',
            $procesadas,
            number_format($antes / 1024, 0, ',', '.'),
            number_format($despues / 1024, 0, ',', '.'),
        ));

        return self::SUCCESS;
    }
}
