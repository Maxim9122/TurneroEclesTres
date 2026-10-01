<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SesionesTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;
    private Usuario $superAdmin;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $empresa = Empresa::create(['nombre' => 'Negocio', 'rubro' => 'barberia', 'estado' => 'activa']);
        $this->admin = Usuario::create([
            'empresa_id' => $empresa->id, 'nombre' => 'Admin', 'email' => 'admin@test.com',
            'password' => 'secreto123', 'rol' => 'admin', 'activo' => true,
        ]);
        $this->superAdmin = Usuario::create([
            'nombre' => 'Super', 'email' => 'super@test.com', 'password' => 'secreto123', 'rol' => 'super_admin', 'activo' => true,
        ]);
        $this->cliente = Cliente::create(['nombre' => 'Juan', 'email' => 'juan@test.com', 'password' => 'secreto123']);

        // Rutas de prueba que simulan un formulario con token vencido (en tests el CSRF está desactivado).
        Route::middleware('web')->post('/_prueba-419', fn () => throw new TokenMismatchException('CSRF token mismatch.'));
        Route::middleware('web')->post('/_prueba-logout-419', fn () => throw new TokenMismatchException('CSRF token mismatch.'))->name('staff.logout');
    }

    public function test_staff_logueado_que_abre_el_login_va_a_su_panel(): void
    {
        $this->actingAs($this->admin, 'web')->get('/staff/login')->assertRedirect(route('staff.empresa.dashboard'));
        $this->actingAs($this->admin, 'web')->get('/staff/registro-empresa')->assertRedirect(route('staff.empresa.dashboard'));
    }

    public function test_super_admin_logueado_que_abre_el_login_va_a_la_plataforma(): void
    {
        $this->actingAs($this->superAdmin, 'web')->get('/staff/login')->assertRedirect(route('staff.plataforma.dashboard'));
    }

    public function test_staff_logueado_que_vuelve_a_enviar_el_login_va_a_su_panel_sin_reloguear(): void
    {
        $this->actingAs($this->admin, 'web')
            ->post('/staff/login', ['email' => 'admin@test.com', 'password' => 'secreto123'])
            ->assertRedirect(route('staff.empresa.dashboard'));
    }

    public function test_cliente_logueado_que_abre_login_o_registro_va_a_su_cuenta(): void
    {
        $this->actingAs($this->cliente, 'cliente')->get('/cuenta/login')->assertRedirect(route('cliente.home'));
        $this->actingAs($this->cliente, 'cliente')->get('/cuenta/registro')->assertRedirect(route('cliente.home'));
    }

    public function test_staff_logueado_puede_ver_el_login_de_clientes(): void
    {
        $this->actingAs($this->admin, 'web')->get('/cuenta/login')->assertOk();
    }

    public function test_sin_sesion_las_paginas_protegidas_mandan_al_login_que_corresponde(): void
    {
        $this->get('/staff/empresa/dashboard')->assertRedirect(route('staff.login'));
        $this->get('/staff/plataforma/empresas')->assertRedirect(route('staff.login'));
        $this->get('/cuenta/mis-turnos')->assertRedirect(route('cliente.login'));
        $this->get('/cuenta')->assertRedirect(route('cliente.login'));
    }

    public function test_el_login_sigue_funcionando_para_quien_no_tiene_sesion(): void
    {
        $this->get('/staff/login')->assertOk();
        $this->post('/staff/login', ['email' => 'admin@test.com', 'password' => 'secreto123'])
            ->assertRedirect(route('staff.empresa.dashboard'));
        $this->assertAuthenticatedAs($this->admin, 'web');
    }

    public function test_token_vencido_vuelve_a_la_pagina_con_aviso_y_sin_la_contrasena(): void
    {
        $this->from('/staff/login')
            ->post('/_prueba-419', ['email' => 'admin@test.com', 'password' => 'secreto123'])
            ->assertRedirect('/staff/login')
            ->assertSessionHasErrors('sesion')
            ->assertSessionHasInput('email', 'admin@test.com')
            ->assertSessionMissing('_old_input.password');
    }

    public function test_token_vencido_en_llamadas_fetch_sigue_devolviendo_419(): void
    {
        $this->postJson('/_prueba-419')->assertStatus(419);
    }

    public function test_cerrar_sesion_con_token_vencido_cierra_igual(): void
    {
        $this->actingAs($this->admin, 'web')
            ->post('/_prueba-logout-419')
            ->assertRedirect(route('staff.login'));
        $this->assertGuest('web');
    }
}
