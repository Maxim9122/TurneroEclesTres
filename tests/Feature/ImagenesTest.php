<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Support\Imagenes;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImagenesTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->empresa = Empresa::create(['nombre' => 'Negocio', 'rubro' => 'barberia', 'estado' => 'activa']);
        $this->admin = Usuario::create([
            'empresa_id' => $this->empresa->id, 'nombre' => 'Admin', 'email' => 'admin@test.com',
            'password' => 'secreto123', 'rol' => 'admin', 'activo' => true,
        ]);
    }

    /** JPEG "de celular": grande y con ruido, para que pese como una foto real. */
    private function fotoGrande(int $ancho = 3000, int $alto = 2000): string
    {
        $img = imagecreatetruecolor($ancho, $alto);
        for ($i = 0; $i < 4000; $i++) {
            imagefilledellipse($img, random_int(0, $ancho), random_int(0, $alto), random_int(5, 80), random_int(5, 80), random_int(0, 0xFFFFFF));
        }
        ob_start();
        imagejpeg($img, null, 92);
        return ob_get_clean();
    }

    private function subir(string $contenido, string $nombre): UploadedFile
    {
        $ruta = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($ruta, $contenido);

        return new UploadedFile($ruta, $nombre, null, null, true);
    }

    public function test_el_logo_se_achica_y_se_guarda_en_webp(): void
    {
        $original = $this->fotoGrande();

        $this->actingAs($this->admin, 'web')->post(route('staff.empresa.branding.update'), [
            'logo' => $this->subir($original, 'logo.jpg'),
        ])->assertSessionHasNoErrors();

        $ruta = $this->empresa->fresh()->logo_path;
        $this->assertMatchesRegularExpression('#^empresas/\d+/logo-[a-z0-9]{8}\.webp$#', $ruta);
        Storage::disk('public')->assertExists($ruta);

        [$ancho, $alto] = getimagesizefromstring(Storage::disk('public')->get($ruta));
        $this->assertLessThanOrEqual(400, $ancho);
        $this->assertLessThanOrEqual(400, $alto);
        $this->assertSame(400, $ancho, 'Mantiene la proporción: 3000x2000 → 400x267');
        $this->assertLessThan(strlen($original) / 10, strlen(Storage::disk('public')->get($ruta)), 'Pesa mucho menos que la original');
    }

    public function test_cambiar_el_logo_usa_otro_nombre_y_borra_el_anterior(): void
    {
        $this->actingAs($this->admin, 'web')->post(route('staff.empresa.branding.update'), ['logo' => $this->subir($this->fotoGrande(800, 800), 'a.jpg')]);
        $primero = $this->empresa->fresh()->logo_path;

        $this->actingAs($this->admin, 'web')->post(route('staff.empresa.branding.update'), ['logo' => $this->subir($this->fotoGrande(800, 800), 'b.jpg')]);
        $segundo = $this->empresa->fresh()->logo_path;

        $this->assertNotSame($primero, $segundo, 'Nombre nuevo: el navegador no muestra el logo viejo');
        Storage::disk('public')->assertMissing($primero);
        Storage::disk('public')->assertExists($segundo);
    }

    public function test_una_foto_de_celular_de_5_mb_se_acepta(): void
    {
        $servicio = Servicio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Maquillaje', 'duracion_minutos' => 90, 'precio' => 85000, 'activo' => true]);
        $foto = UploadedFile::fake()->image('IMG_1234.jpg', 4000, 3000)->size(5000); // como una foto de iPhone

        $this->actingAs($this->admin, 'web')->put(route('staff.empresa.servicios.update', $servicio), [
            'nombre' => 'Maquillaje', 'duracion_minutos' => 90, 'precio' => 85000, 'foto' => $foto,
        ])->assertSessionHasNoErrors();

        $this->assertStringEndsWith('.webp', $servicio->fresh()->foto_path);
    }

    public function test_una_foto_de_mas_de_10_mb_se_rechaza_con_un_mensaje_claro(): void
    {
        $servicio = Servicio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Maquillaje', 'duracion_minutos' => 90, 'precio' => 85000, 'activo' => true]);

        $this->actingAs($this->admin, 'web')->put(route('staff.empresa.servicios.update', $servicio), [
            'nombre' => 'Maquillaje', 'duracion_minutos' => 90, 'precio' => 85000,
            'foto' => UploadedFile::fake()->image('enorme.jpg')->size(11000),
        ])->assertSessionHasErrors(['foto' => 'La foto no puede pesar más de 10 MB.']);
    }

    public function test_todas_las_validaciones_usadas_tienen_mensaje_en_espanol(): void
    {
        app()->setLocale('es');
        $claves = [
            'required', 'required_with', 'required_without', 'email', 'unique', 'confirmed', 'string', 'integer', 'numeric',
            'decimal', 'array', 'in', 'boolean', 'date', 'date_format', 'after', 'after_or_equal', 'starts_with', 'url',
            'image', 'mimes', 'file', 'uploaded', 'exists', 'regex',
            'min.string', 'min.numeric', 'min.file', 'min.array', 'max.string', 'max.numeric', 'max.file', 'max.array',
            'between.string', 'between.numeric', 'between.file', 'between.array',
        ];

        foreach ($claves as $clave) {
            $this->assertNotSame("validation.{$clave}", __("validation.{$clave}"), "Falta el mensaje en español de '{$clave}'");
        }
    }

    public function test_un_logo_svg_se_guarda_tal_cual(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><rect width="10" height="10"/></svg>';
        $guardado = Imagenes::guardar($this->subir($svg, 'logo.svg'), 'empresas/1', 'logo', Imagenes::LOGO);

        $this->assertStringEndsWith('.svg', $guardado);
        $this->assertSame($svg, Storage::disk('public')->get($guardado));
    }

    public function test_la_foto_de_un_servicio_se_optimiza(): void
    {
        $servicio = Servicio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Corte', 'duracion_minutos' => 30, 'precio' => 1, 'activo' => true]);

        $this->actingAs($this->admin, 'web')->put(route('staff.empresa.servicios.update', $servicio), [
            'nombre' => 'Corte', 'duracion_minutos' => 30, 'precio' => 1,
            'foto' => $this->subir($this->fotoGrande(), 'foto.jpg'),
        ])->assertSessionHasNoErrors();

        $ruta = $servicio->fresh()->foto_path;
        $this->assertStringEndsWith('.webp', $ruta);
        [$ancho] = getimagesizefromstring(Storage::disk('public')->get($ruta));
        $this->assertSame(1200, $ancho);
    }

    public function test_la_foto_girada_del_celular_se_endereza(): void
    {
        // Foto apaisada (300x100) marcada con orientación EXIF 6 = "girar 90° a la derecha"
        $img = imagecreatetruecolor(300, 100);
        ob_start(); imagejpeg($img); $jpeg = ob_get_clean();
        $exif = "Exif\0\0" . "MM\0\x2A\0\0\0\x08" . "\0\x01" . "\x01\x12\0\x03\0\0\0\x01\0\x06\0\0" . "\0\0\0\0";
        $app1 = "\xFF\xE1" . pack('n', strlen($exif) + 2) . $exif;
        $conExif = substr($jpeg, 0, 2) . $app1 . substr($jpeg, 2);

        [$ancho, $alto] = getimagesizefromstring(Imagenes::optimizar($conExif, Imagenes::FOTO));
        $this->assertSame([100, 300], [$ancho, $alto], 'Queda vertical, como se sacó');
    }

    public function test_el_comando_optimiza_las_imagenes_ya_subidas(): void
    {
        Storage::disk('public')->put('empresas/1/logo.jpg', $this->fotoGrande(1500, 1500));
        Storage::disk('public')->put('empresas/1/logo.svg', '<svg/>');
        DB::table('empresas')->where('id', $this->empresa->id)->update(['logo_path' => 'empresas/1/logo.jpg', 'imagen_fondo_path' => 'empresas/1/no-existe.jpg']);
        $actualizadoAntes = $this->empresa->fresh()->updated_at;

        // Simulación: no cambia nada
        $this->artisan('imagenes:optimizar', ['--simular' => true])->assertSuccessful();
        $this->assertSame('empresas/1/logo.jpg', $this->empresa->fresh()->logo_path);
        Storage::disk('public')->assertExists('empresas/1/logo.jpg');

        // De verdad
        $this->artisan('imagenes:optimizar')->assertSuccessful();
        $empresa = $this->empresa->fresh();
        $this->assertMatchesRegularExpression('#^empresas/1/logo-[a-z0-9]{8}\.webp$#', $empresa->logo_path);
        Storage::disk('public')->assertExists($empresa->logo_path);
        Storage::disk('public')->assertMissing('empresas/1/logo.jpg');
        $this->assertSame('empresas/1/no-existe.jpg', $empresa->imagen_fondo_path, 'Si falta el archivo, no se toca');
        $this->assertEquals($actualizadoAntes, $empresa->updated_at, 'No cuenta como cambio del negocio');

        // Correrlo de nuevo no hace nada
        $this->artisan('imagenes:optimizar')->expectsOutputToContain('0 imágenes')->assertSuccessful();
    }
}
