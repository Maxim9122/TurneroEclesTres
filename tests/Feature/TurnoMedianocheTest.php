<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\Turno;
use App\Models\Usuario;
use App\Services\DisponibilidadService;
use App\Services\PushService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/** Ningún turno pasa al día siguiente: como mucho termina a medianoche. */
class TurnoMedianocheTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Usuario $admin;
    private Profesional $katherin;
    private Cliente $cliente;
    private Servicio $color;
    private Servicio $babylights;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->app->instance(PushService::class, Mockery::mock(PushService::class)->shouldIgnoreMissing());

        $this->empresa = Empresa::create(['nombre' => 'Studio', 'rubro' => 'peluqueria', 'estado' => 'activa']);
        $this->admin = Usuario::create([
            'empresa_id' => $this->empresa->id, 'nombre' => 'Katherin', 'email' => 'k@test.com',
            'password' => 'secreto123', 'rol' => 'admin', 'activo' => true,
        ]);
        $this->katherin = Profesional::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Katherin', 'activo' => true, 'porcentaje_comision' => 0]);
        $this->cliente = Cliente::create(['nombre' => 'Victoria', 'email' => 'v@test.com', 'password' => 'secreto123']);
        $this->color = Servicio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Color global', 'duracion_minutos' => 30, 'precio' => 50, 'activo' => true]);
        $this->babylights = Servicio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Babylights', 'duracion_minutos' => 180, 'precio' => 105, 'activo' => true]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_calculo_de_la_hora_de_fin(): void
    {
        $this->assertSame(['10:30', false], Turno::calcularFin('10:00', 30));
        $this->assertSame(['23:59', false], Turno::calcularFin('23:00', 59), 'Justo antes de medianoche no recorta');
        $this->assertSame(['23:59', true], Turno::calcularFin('23:30', 30), 'Termina exactamente a medianoche');
        $this->assertSame(['23:59', true], Turno::calcularFin('23:45', 210), 'El caso de Victoria: 23:45 + 3,5 h');
        $this->assertSame(['23:59', true], Turno::calcularFin('23:45:00', 30), 'Acepta horas con segundos');
    }

    public function test_orden_de_llegada_tarde_queda_hasta_medianoche_y_avisa(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-27 23:45'));

        $this->actingAs($this->admin, 'web')->postJson(route('staff.empresa.turnos.rapido.confirmar'), [
            'cliente_id' => $this->cliente->id,
            'servicios' => [$this->color->id, $this->babylights->id],
        ])->assertOk()->assertJsonFragment(['ok' => true])
            ->assertSessionHas('status', fn ($m) => str_contains($m, 'quedó registrado hasta las 00:00'));

        $turno = Turno::first();
        $this->assertSame('23:45', substr($turno->hora_inicio, 0, 5));
        $this->assertSame('23:59', substr($turno->hora_fin, 0, 5));
        $this->assertSame('00:00', $turno->horaFinParaMostrar());
        $this->assertSame('2026-10-27', $turno->fecha->toDateString(), 'Sigue siendo del mismo día');
    }

    public function test_orden_de_llegada_a_una_hora_normal_no_cambia(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-27 15:00'));

        $this->actingAs($this->admin, 'web')->post(route('staff.empresa.turnos.rapido.confirmar'), [
            'cliente_id' => $this->cliente->id,
            'servicios' => [$this->babylights->id],
        ])->assertSessionHas('status', fn ($m) => !str_contains($m, '00:00'));

        $this->assertSame('18:00', substr(Turno::first()->hora_fin, 0, 5));
    }

    public function test_editar_un_turno_nocturno_recorta_a_medianoche(): void
    {
        $turno = Turno::create([
            'empresa_id' => $this->empresa->id, 'cliente_id' => $this->cliente->id, 'profesional_id' => $this->katherin->id,
            'fecha' => '2026-10-27', 'hora_inicio' => '22:00', 'hora_fin' => '22:30', 'estado' => 'confirmado',
        ]);
        $turno->servicios()->attach($this->color->id, ['precio_al_momento' => 50, 'duracion_al_momento' => 30]);

        $this->actingAs($this->admin, 'web')->put(route('staff.empresa.turnos.update', $turno), [
            'profesional_id' => $this->katherin->id,
            'servicios' => [$this->color->id, $this->babylights->id], // 30 + 180 min desde las 22:00
        ])->assertSessionHas('status', fn ($m) => str_contains($m, 'ahora termina a las 00:00'));

        $this->assertSame('23:59', substr($turno->fresh()->hora_fin, 0, 5));
    }

    public function test_el_turno_recortado_ocupa_hasta_medianoche_en_los_horarios_libres(): void
    {
        Turno::create([
            'empresa_id' => $this->empresa->id, 'cliente_id' => $this->cliente->id, 'profesional_id' => $this->katherin->id,
            'fecha' => '2026-10-29', 'hora_inicio' => '22:00', 'hora_fin' => '23:59', 'estado' => 'confirmado',
        ]);

        $slots = app(DisponibilidadService::class)->slotsDisponibles($this->empresa, Carbon::parse('2026-10-29'), collect([$this->color]), $this->katherin->id);

        $this->assertContains('21:30', $slots);
        $this->assertNotContains('22:00', $slots);
        $this->assertNotContains('23:00', $slots, 'Después de las 22:00 ya está ocupada hasta medianoche');
    }

    public function test_la_agenda_muestra_00_00(): void
    {
        Turno::create([
            'empresa_id' => $this->empresa->id, 'cliente_id' => $this->cliente->id,
            'fecha' => '2026-10-27', 'hora_inicio' => '23:45', 'hora_fin' => '23:59', 'estado' => 'confirmado',
        ])->servicios()->attach($this->babylights->id, ['precio_al_momento' => 105, 'duracion_al_momento' => 180]);

        $this->actingAs($this->admin, 'web')->get(route('staff.empresa.turnos.index', ['fecha' => '2026-10-27']))
            ->assertOk()->assertSee('23:45 - 00:00');
    }
}
