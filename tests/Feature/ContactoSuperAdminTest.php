<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Usuario;
use App\Support\Contacto;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ContactoSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->superAdmin = Usuario::create([
            'nombre' => 'Super', 'email' => 'ingreso@test.com', 'password' => 'secreto123',
            'rol' => 'super_admin', 'activo' => true, 'telefono' => '3800000000',
        ]);
        $this->superAdmin->forceFill(['email_verified_at' => now()])->save();
    }

    public function test_sin_email_de_contacto_el_pie_muestra_el_de_ingreso(): void
    {
        $this->get('/')->assertOk()->assertSee('ingreso@test.com')->assertSee('Tel: 3800000000');
    }

    public function test_super_admin_guarda_email_de_contacto_y_el_pie_se_actualiza_enseguida(): void
    {
        $this->get('/')->assertSee('ingreso@test.com'); // deja los datos en caché

        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($this->superAdmin, 'web')
            ->put(route('staff.mi-perfil.update'), [
                'nombre' => 'Super', 'email' => 'ingreso@test.com',
                'email_contacto' => 'contacto@test.com', 'telefono' => '3811111111',
            ])->assertSessionHasNoErrors();

        $this->assertSame('ingreso@test.com', $this->superAdmin->fresh()->email, 'El email de ingreso no debe cambiar');
        $this->assertSame('contacto@test.com', Contacto::email());
        $this->assertSame('3811111111', Contacto::telefono());

        $this->get('/')->assertSee('contacto@test.com')->assertDontSee('ingreso@test.com')->assertSee('Tel: 3811111111');
    }

    public function test_el_super_admin_ve_el_campo_y_un_admin_de_empresa_no(): void
    {
        $this->actingAs($this->superAdmin, 'web')->get(route('staff.mi-perfil.edit'))
            ->assertOk()->assertSee('Email de contacto (pie de página)');

        $empresa = Empresa::create(['nombre' => 'Negocio', 'rubro' => 'barberia', 'estado' => 'activa']);
        $admin = Usuario::create([
            'empresa_id' => $empresa->id, 'nombre' => 'Admin', 'email' => 'admin@test.com',
            'password' => 'secreto123', 'rol' => 'admin', 'activo' => true,
        ]);

        $this->actingAs($admin, 'web')->get(route('staff.mi-perfil.edit'))
            ->assertOk()->assertDontSee('Email de contacto (pie de página)');
    }

    public function test_un_admin_de_empresa_no_puede_cambiar_el_email_del_pie(): void
    {
        $empresa = Empresa::create(['nombre' => 'Negocio', 'rubro' => 'barberia', 'estado' => 'activa']);
        $admin = Usuario::create([
            'empresa_id' => $empresa->id, 'nombre' => 'Admin', 'email' => 'admin@test.com',
            'password' => 'secreto123', 'rol' => 'admin', 'activo' => true,
        ]);

        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($admin, 'web')
            ->put(route('staff.mi-perfil.update'), [
                'nombre' => 'Admin', 'email' => 'admin@test.com', 'email_contacto' => 'hacker@test.com',
            ]);

        $this->assertNull($admin->fresh()->email_contacto);
        $this->assertSame('ingreso@test.com', Contacto::email());
    }

    public function test_email_de_contacto_invalido_se_rechaza(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($this->superAdmin, 'web')
            ->put(route('staff.mi-perfil.update'), [
                'nombre' => 'Super', 'email' => 'ingreso@test.com', 'email_contacto' => 'no-es-un-email',
            ])->assertSessionHasErrors('email_contacto');
    }
}
