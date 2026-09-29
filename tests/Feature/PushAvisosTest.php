<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\EmpresaHorario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Profesional;
use App\Models\PushSubscription;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\Usuario;
use App\Services\DisponibilidadService;
use App\Services\PushService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PushAvisosTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Cliente $cliente;
    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::create(['nombre' => 'Barbería Test', 'rubro' => 'barberia', 'estado' => 'activa']);
        $this->cliente = Cliente::create(['nombre' => 'Juan Cliente', 'email' => 'juan@test.com', 'password' => 'secreto123']);
        $this->admin = Usuario::create([
            'empresa_id' => $this->empresa->id, 'nombre' => 'Admin', 'email' => 'admin@test.com',
            'password' => 'secreto123', 'rol' => 'admin', 'activo' => true,
        ]);
        $this->admin->forceFill(['email_verified_at' => now()])->save();
    }

    public function test_reserva_de_cliente_sigue_funcionando_y_avisa_por_push(): void
    {
        Profesional::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Pepe', 'activo' => true, 'porcentaje_comision' => 50]);
        $servicio = Servicio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Corte', 'duracion_minutos' => 30, 'precio' => 5000, 'activo' => true]);
        foreach (range(0, 6) as $dia) {
            EmpresaHorario::create(['empresa_id' => $this->empresa->id, 'dia_semana' => $dia, 'hora_inicio' => '09:00', 'hora_fin' => '18:00']);
        }

        $fecha = Carbon::tomorrow();
        $hora = app(DisponibilidadService::class)->slotsDisponibles($this->empresa, $fecha, collect([$servicio]))[0];

        $push = Mockery::mock(PushService::class);
        $push->shouldReceive('nuevoTurno')->once()->with(Mockery::on(fn ($t) => $t instanceof Turno && $t->hora_inicio !== null));
        $this->app->instance(PushService::class, $push);

        $respuesta = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->actingAs($this->cliente, 'cliente')
            ->post(route('publico.reserva.confirmar', $this->empresa), [
                'fecha' => $fecha->toDateString(),
                'hora' => $hora,
                'servicios' => [$servicio->id],
            ]);

        $turno = Turno::first();
        $this->assertNotNull($turno, 'No se creó el turno');
        $respuesta->assertRedirect(route('cliente.turnos.confirmado', $turno));
        $this->assertSame('confirmado', $turno->estado);
    }

    public function test_compra_de_cliente_sigue_funcionando_y_avisa_por_push(): void
    {
        $producto = Producto::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Cera', 'precio' => 3000, 'stock' => 10, 'activo' => true]);

        $push = Mockery::mock(PushService::class);
        $push->shouldReceive('nuevoPedido')->once()->with(Mockery::type(Pedido::class));
        $this->app->instance(PushService::class, $push);

        $respuesta = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->actingAs($this->cliente, 'cliente')
            ->withSession(["carrito.empresa.{$this->empresa->id}" => [$producto->id => 2]])
            ->post(route('publico.pedido.confirmar', $this->empresa), ['metodo_entrega' => 'retiro']);

        $pedido = Pedido::first();
        $this->assertNotNull($pedido, 'No se creó el pedido');
        $respuesta->assertRedirect(route('cliente.pedidos.confirmado', $pedido));
        $this->assertEquals(6000, (float) $pedido->total);
        $this->assertSame(8, $producto->fresh()->stock);
    }

    public function test_si_el_push_falla_la_reserva_no_se_rompe(): void
    {
        // Suscripción con claves inválidas: el envío falla, pero se registra en el log y no corta nada.
        PushSubscription::create([
            'usuario_id' => $this->admin->id, 'endpoint' => 'https://fcm.googleapis.com/fcm/send/invalido',
            'endpoint_hash' => PushSubscription::hashEndpoint('https://fcm.googleapis.com/fcm/send/invalido'),
            'public_key' => 'clave-invalida', 'auth_token' => 'invalido',
        ]);
        config(['services.webpush.public_key' => 'x', 'services.webpush.private_key' => 'y']);

        $producto = Producto::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Cera', 'precio' => 3000, 'stock' => 10, 'activo' => true]);

        $respuesta = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->actingAs($this->cliente, 'cliente')
            ->withSession(["carrito.empresa.{$this->empresa->id}" => [$producto->id => 1]])
            ->post(route('publico.pedido.confirmar', $this->empresa), ['metodo_entrega' => 'retiro']);

        $respuesta->assertRedirect(route('cliente.pedidos.confirmado', Pedido::first()));
    }

    public function test_suscribir_y_desuscribir_dispositivo(): void
    {
        $endpoint = 'https://fcm.googleapis.com/fcm/send/abc:123';

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->actingAs($this->admin, 'web')
            ->postJson(route('staff.empresa.push.suscribir'), ['endpoint' => $endpoint, 'keys' => ['p256dh' => 'pk', 'auth' => 'au']])
            ->assertOk();
        $this->assertSame(1, PushSubscription::where('usuario_id', $this->admin->id)->count());

        // Rechaza endpoints que no sean https
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->actingAs($this->admin, 'web')
            ->postJson(route('staff.empresa.push.suscribir'), ['endpoint' => 'http://malo.com', 'keys' => ['p256dh' => 'pk', 'auth' => 'au']])
            ->assertStatus(422);

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->actingAs($this->admin, 'web')
            ->postJson(route('staff.empresa.push.desuscribir'), ['endpoint' => $endpoint])
            ->assertOk();
        $this->assertSame(0, PushSubscription::count());
    }

    public function test_clientes_no_pueden_suscribirse_como_staff(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->actingAs($this->cliente, 'cliente')
            ->postJson(route('staff.empresa.push.suscribir'), ['endpoint' => 'https://x.com/a', 'keys' => ['p256dh' => 'pk', 'auth' => 'au']])
            ->assertStatus(401);
    }
}
