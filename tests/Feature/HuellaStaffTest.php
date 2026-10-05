<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Usuario;
use App\Models\WebauthnCredencial;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ingreso con huella (WebAuthn). La verificación criptográfica completa se prueba con el
 * autenticador virtual de Chrome; acá se cubren opciones, permisos y casos de error.
 */
class HuellaStaffTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;
    private Usuario $otro;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $empresa = Empresa::create(['nombre' => 'Negocio', 'rubro' => 'barberia', 'estado' => 'activa']);
        $this->admin = Usuario::create([
            'empresa_id' => $empresa->id, 'nombre' => 'Admin', 'email' => 'admin@test.com',
            'password' => 'secreto123', 'rol' => 'admin', 'activo' => true,
        ]);
        $this->otro = Usuario::create([
            'empresa_id' => $empresa->id, 'nombre' => 'Otro', 'email' => 'otro@test.com',
            'password' => 'secreto123', 'rol' => 'operador', 'activo' => true,
        ]);
    }

    public function test_opciones_de_registro_exigen_verificacion_y_llave_del_dispositivo(): void
    {
        $r = $this->actingAs($this->admin, 'web')->postJson(route('staff.huella.registro.opciones'))->assertOk();

        $this->assertSame(parse_url(config('app.url'), PHP_URL_HOST), $r->json('rp.id'), 'La llave queda atada al dominio del sitio');
        $this->assertSame('admin@test.com', $r->json('user.name'));
        $this->assertSame('required', $r->json('authenticatorSelection.userVerification'));
        $this->assertSame('required', $r->json('authenticatorSelection.residentKey'));
        $this->assertSame('none', $r->json('attestation'));
        $this->assertNotEmpty($r->json('challenge'));
        $this->assertNotSame(base64_encode('staff:' . $this->admin->id), $r->json('user.id'), 'El id de usuario de la llave no debe ser predecible');
    }

    public function test_cada_pedido_genera_un_desafio_distinto(): void
    {
        $a = $this->postJson(route('staff.huella.ingreso.opciones'))->assertOk()->json('challenge');
        $b = $this->postJson(route('staff.huella.ingreso.opciones'))->assertOk()->json('challenge');

        $this->assertNotSame($a, $b);
        $this->assertSame('required', $this->postJson(route('staff.huella.ingreso.opciones'))->json('userVerification'));
    }

    public function test_sin_sesion_no_se_puede_activar_la_huella(): void
    {
        $this->postJson(route('staff.huella.registro.opciones'))->assertUnauthorized();
        $this->postJson(route('staff.huella.registro'), ['id' => 'x'])->assertUnauthorized();
    }

    public function test_registrar_sin_pedir_opciones_antes_se_rechaza(): void
    {
        $this->actingAs($this->admin, 'web')
            ->postJson(route('staff.huella.registro'), ['id' => 'x', 'rawId' => 'x', 'type' => 'public-key', 'response' => []])
            ->assertStatus(422);

        $this->assertSame(0, WebauthnCredencial::count());
    }

    public function test_ingresar_sin_pedir_opciones_antes_se_rechaza(): void
    {
        $this->postJson(route('staff.huella.ingreso'), ['id' => 'x'])->assertStatus(422);
        $this->assertGuest('web');
    }

    public function test_respuesta_basura_no_rompe_y_no_loguea(): void
    {
        $this->postJson(route('staff.huella.ingreso.opciones'))->assertOk();
        $this->postJson(route('staff.huella.ingreso'), ['id' => 'abc', 'rawId' => 'abc', 'type' => 'public-key', 'response' => ['signature' => 'x']])
            ->assertStatus(422);
        $this->assertGuest('web');
    }

    public function test_con_sesion_iniciada_el_ingreso_con_huella_redirige_al_panel(): void
    {
        $this->actingAs($this->admin, 'web')->post(route('staff.huella.ingreso.opciones'))
            ->assertRedirect(route('staff.empresa.dashboard'));
    }

    public function test_no_se_puede_quitar_la_huella_de_otro_usuario(): void
    {
        $ajena = WebauthnCredencial::create([
            'usuario_id' => $this->otro->id, 'credencial_hash' => str_repeat('a', 64), 'registro' => '{}', 'nombre' => 'Android · Chrome',
        ]);

        $this->actingAs($this->admin, 'web')->delete(route('staff.huella.eliminar', $ajena))->assertNotFound();
        $this->assertNotNull($ajena->fresh());
    }

    public function test_quitar_la_huella_propia(): void
    {
        $propia = WebauthnCredencial::create([
            'usuario_id' => $this->admin->id, 'credencial_hash' => str_repeat('b', 64), 'registro' => '{}', 'nombre' => 'iPhone · Safari',
        ]);

        $this->actingAs($this->admin, 'web')->from(route('staff.mi-perfil.edit'))
            ->delete(route('staff.huella.eliminar', $propia))
            ->assertRedirect(route('staff.mi-perfil.edit'))
            ->assertSessionHas('status');
        $this->assertNull($propia->fresh());
    }

    public function test_mi_perfil_muestra_la_seccion_y_el_login_el_boton(): void
    {
        $this->actingAs($this->admin, 'web')->get(route('staff.mi-perfil.edit'))
            ->assertOk()->assertSee('Ingreso con huella, rostro o patrón');

        auth('web')->logout();
        $this->get(route('staff.login'))->assertOk()->assertSee('Ingresar con huella, rostro o patrón');
    }
}
