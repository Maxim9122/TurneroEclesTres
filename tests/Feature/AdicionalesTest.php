<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\EmpresaHorario;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\ServicioAdicional;
use App\Models\Turno;
use App\Models\Usuario;
use App\Services\DisponibilidadService;
use App\Services\PushService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AdicionalesTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Usuario $admin;
    private Cliente $cliente;
    private Profesional $profesional;
    private Servicio $corte;
    private ServicioAdicional $diseno;
    private ServicioAdicional $lineas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->app->instance(PushService::class, Mockery::mock(PushService::class)->shouldIgnoreMissing());

        $this->empresa = Empresa::create(['nombre' => 'Barbería', 'rubro' => 'barberia', 'estado' => 'activa']);
        $this->admin = Usuario::create([
            'empresa_id' => $this->empresa->id, 'nombre' => 'Admin', 'email' => 'admin@test.com',
            'password' => 'secreto123', 'rol' => 'admin', 'activo' => true,
        ]);
        $this->cliente = Cliente::create(['nombre' => 'Juan', 'email' => 'juan@test.com', 'password' => 'secreto123']);
        $this->profesional = Profesional::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Pepe', 'activo' => true, 'porcentaje_comision' => 50]);
        foreach (range(0, 6) as $dia) {
            EmpresaHorario::create(['empresa_id' => $this->empresa->id, 'dia_semana' => $dia, 'hora_inicio' => '09:00', 'hora_fin' => '18:00']);
        }

        $this->corte = Servicio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Corte', 'duracion_minutos' => 30, 'precio' => 5000, 'activo' => true]);
        $this->diseno = $this->corte->adicionales()->create(['nombre' => 'Diseño', 'precio' => 2000, 'duracion_minutos' => 15]);
        $this->lineas = $this->corte->adicionales()->create(['nombre' => 'Líneas', 'precio' => 1000, 'duracion_minutos' => 0]);
    }

    private function reservar(array $servicios, array $adicionales = [], ?string $hora = null): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->cliente, 'cliente')->post(route('publico.reserva.confirmar', $this->empresa), [
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora' => $hora ?? '10:00',
            'servicios' => $servicios,
            'adicionales' => $adicionales,
        ]);
    }

    // ---------------------------------------------------------------- Carga en el servicio

    public function test_el_admin_crea_un_servicio_con_adicionales(): void
    {
        $this->actingAs($this->admin, 'web')->post(route('staff.empresa.servicios.store'), [
            'nombre' => 'Barba', 'duracion_minutos' => 20, 'precio' => 3000,
            'adicionales' => [
                ['nombre' => 'Toalla caliente', 'precio' => 800, 'duracion_minutos' => 5],
                ['nombre' => 'Perfilado', 'precio' => 500],
            ],
        ])->assertRedirect(route('staff.empresa.servicios.index'))->assertSessionHasNoErrors();

        $barba = Servicio::where('nombre', 'Barba')->first();
        $this->assertSame(['Toalla caliente', 'Perfilado'], $barba->adicionales->pluck('nombre')->all());
        $this->assertSame(0, $barba->adicionales->last()->duracion_minutos);
    }

    public function test_editar_actualiza_agrega_y_quita_adicionales(): void
    {
        $this->actingAs($this->admin, 'web')->put(route('staff.empresa.servicios.update', $this->corte), [
            'nombre' => 'Corte', 'duracion_minutos' => 30, 'precio' => 5000,
            'adicionales' => [
                ['id' => $this->diseno->id, 'nombre' => 'Diseño', 'precio' => 2500, 'duracion_minutos' => 20],
                ['nombre' => 'Lavado', 'precio' => 700, 'duracion_minutos' => 10],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['Diseño', 'Lavado'], $this->corte->fresh()->adicionales->pluck('nombre')->all());
        $this->assertEquals(2500, $this->diseno->fresh()->precio);
        $this->assertNull($this->lineas->fresh(), 'Líneas se quitó del formulario');
    }

    public function test_no_se_puede_editar_un_adicional_de_otro_servicio(): void
    {
        $otro = Servicio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Barba', 'duracion_minutos' => 20, 'precio' => 3000, 'activo' => true]);

        $this->actingAs($this->admin, 'web')->put(route('staff.empresa.servicios.update', $otro), [
            'nombre' => 'Barba', 'duracion_minutos' => 20, 'precio' => 3000,
            'adicionales' => [['id' => $this->diseno->id, 'nombre' => 'Robado', 'precio' => 1]],
        ]);

        $this->assertSame('Diseño', $this->diseno->fresh()->nombre, 'El adicional del Corte no se toca');
        $this->assertSame(['Robado'], $otro->fresh()->adicionales->pluck('nombre')->all(), 'Se creó como nuevo en Barba');
    }

    public function test_adicional_sin_nombre_o_precio_se_rechaza(): void
    {
        $this->actingAs($this->admin, 'web')->put(route('staff.empresa.servicios.update', $this->corte), [
            'nombre' => 'Corte', 'duracion_minutos' => 30, 'precio' => 5000,
            'adicionales' => [['nombre' => '', 'precio' => '']],
        ])->assertSessionHasErrors(['adicionales.0.nombre', 'adicionales.0.precio']);
    }

    // ---------------------------------------------------------------- Reserva del cliente

    public function test_la_pagina_del_negocio_muestra_los_adicionales(): void
    {
        $this->get(route('publico.empresa', $this->empresa))
            ->assertOk()->assertSee('Adicionales (opcional)')->assertSee('Diseño')->assertSee('+$2.000,00', false);
    }

    public function test_el_cliente_reserva_con_adicionales_y_se_suman_precio_y_minutos(): void
    {
        $this->reservar([$this->corte->id], [$this->diseno->id, $this->lineas->id])->assertRedirect();

        $turno = Turno::with(['servicios', 'adicionales'])->first();
        $this->assertSame(2, $turno->adicionales->count());
        $this->assertEquals(8000, $turno->precioTotal());             // 5000 + 2000 + 1000
        $this->assertSame(45, $turno->duracionTotalMinutos());          // 30 + 15 + 0
        $this->assertSame('10:45', substr($turno->hora_fin, 0, 5));
        $this->assertSame('Corte (+ Diseño, Líneas)', $turno->descripcionServicios());
    }

    public function test_la_pantalla_de_fecha_muestra_el_resumen_con_adicionales(): void
    {
        $this->actingAs($this->cliente, 'cliente')
            ->get(route('publico.reserva.iniciar', $this->empresa) . "?servicios[]={$this->corte->id}&adicionales[]={$this->diseno->id}")
            ->assertOk()->assertSee('+ Diseño')->assertSee('45 min')->assertSee('$7.000,00', false);
    }

    public function test_adicional_de_otro_negocio_o_de_un_servicio_no_elegido_se_ignora(): void
    {
        $otraEmpresa = Empresa::create(['nombre' => 'Otra', 'rubro' => 'barberia', 'estado' => 'activa']);
        $ajeno = Servicio::create(['empresa_id' => $otraEmpresa->id, 'nombre' => 'X', 'duracion_minutos' => 30, 'precio' => 1, 'activo' => true])
            ->adicionales()->create(['nombre' => 'Ajeno', 'precio' => 99999]);
        $barba = Servicio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Barba', 'duracion_minutos' => 20, 'precio' => 3000, 'activo' => true]);

        // Se reserva solo Barba, pero se intenta colar el Diseño (del Corte) y un adicional de otro negocio
        $this->reservar([$barba->id], [$this->diseno->id, $ajeno->id])->assertRedirect();

        $turno = Turno::with(['servicios', 'adicionales'])->first();
        $this->assertSame(0, $turno->adicionales->count());
        $this->assertEquals(3000, $turno->precioTotal());
    }

    public function test_los_horarios_tienen_en_cuenta_los_minutos_extra(): void
    {
        EmpresaHorario::query()->update(['hora_inicio' => '09:00', 'hora_fin' => '10:00']);
        $largo = $this->corte->adicionales()->create(['nombre' => 'Diseño complejo', 'precio' => 5000, 'duracion_minutos' => 40]);
        $disponibilidad = app(DisponibilidadService::class);
        $manana = Carbon::tomorrow();

        $sinExtra = $disponibilidad->slotsDisponibles($this->empresa, $manana, collect([$this->corte]));
        $conExtra = $disponibilidad->slotsDisponibles($this->empresa, $manana, collect([$this->corte]), null, $largo->duracion_minutos);

        $this->assertSame(['09:00', '09:30'], array_values($sinExtra));
        $this->assertSame([], array_values($conExtra), '30 + 40 min no entran en una hora');

        $this->getJson(route('publico.reserva.horarios', $this->empresa) . '?fecha=' . $manana->toDateString() . "&servicios[]={$this->corte->id}&adicionales[]={$largo->id}")
            ->assertOk()->assertJson(['slots' => []]);
    }

    public function test_el_precio_queda_fijo_aunque_despues_cambie_o_se_borre_el_adicional(): void
    {
        $this->reservar([$this->corte->id], [$this->diseno->id]);

        $this->diseno->update(['precio' => 9999]);
        $this->assertEquals(7000, Turno::first()->precioTotal());

        $this->diseno->delete();
        $turno = Turno::with(['servicios', 'adicionales'])->first();
        $this->assertEquals(7000, $turno->precioTotal());
        $this->assertSame('Corte (+ Diseño)', $turno->descripcionServicios());
    }

    // ---------------------------------------------------------------- Staff

    public function test_el_staff_agrega_un_adicional_al_editar_y_lo_ya_reservado_conserva_su_precio(): void
    {
        $this->reservar([$this->corte->id], [$this->lineas->id]);
        $turno = Turno::first();
        $this->lineas->update(['precio' => 4000]); // subió el precio después de reservar

        $this->actingAs($this->admin, 'web')->put(route('staff.empresa.turnos.update', $turno), [
            'profesional_id' => $this->profesional->id,
            'servicios' => [$this->corte->id],
            'adicionales' => [$this->lineas->id, $this->diseno->id],
        ])->assertSessionHasNoErrors();

        $turno = Turno::with(['servicios', 'adicionales'])->first();
        $this->assertEquals(8000, $turno->precioTotal(), 'Líneas sigue a 1000 (precio reservado) + Diseño 2000 nuevo');
        $this->assertSame('10:45', substr($turno->hora_fin, 0, 5));
    }

    public function test_el_staff_quita_un_adicional_y_se_recalcula_el_fin(): void
    {
        $this->reservar([$this->corte->id], [$this->diseno->id]);
        $turno = Turno::first();

        $this->actingAs($this->admin, 'web')->put(route('staff.empresa.turnos.update', $turno), [
            'servicios' => [$this->corte->id],
        ])->assertSessionHasNoErrors();

        $turno = Turno::with(['servicios', 'adicionales'])->first();
        $this->assertSame(0, $turno->adicionales->count());
        $this->assertSame('10:30', substr($turno->hora_fin, 0, 5));
    }

    public function test_orden_de_llegada_con_adicionales(): void
    {
        $this->actingAs($this->admin, 'web')->postJson(route('staff.empresa.turnos.rapido.confirmar'), [
            'cliente_id' => $this->cliente->id,
            'servicios' => [$this->corte->id],
            'adicionales' => [$this->diseno->id],
        ])->assertOk();

        $turno = Turno::with(['servicios', 'adicionales'])->first();
        $this->assertEquals(7000, $turno->precioTotal());
        $this->assertSame(45, $turno->duracionTotalMinutos());
    }

    public function test_agendar_a_mano_con_adicionales(): void
    {
        $this->actingAs($this->admin, 'web')->post(route('staff.empresa.turnos.manual.confirmar'), [
            'cliente_id' => $this->cliente->id,
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora' => '11:00',
            'servicios' => [$this->corte->id],
            'adicionales' => [$this->diseno->id],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(7000, Turno::with(['servicios', 'adicionales'])->first()->precioTotal());
    }

    public function test_la_comision_incluye_los_adicionales(): void
    {
        $this->reservar([$this->corte->id], [$this->diseno->id]);
        Turno::first()->update(['profesional_id' => $this->profesional->id, 'estado' => 'completado']);

        $this->actingAs($this->admin, 'web')
            ->get(route('staff.empresa.reportes.comisiones', ['desde' => now()->subDay()->toDateString(), 'hasta' => now()->addDays(2)->toDateString()]))
            ->assertOk()
            ->assertSee('Corte (+ Diseño)')
            ->assertSee('$7.000,00', false)   // recaudado
            ->assertSee('$3.500,00', false);  // 50% de comisión
    }

    public function test_la_agenda_y_editar_muestran_los_adicionales(): void
    {
        $this->reservar([$this->corte->id], [$this->diseno->id]);
        $turno = Turno::first();

        $this->actingAs($this->admin, 'web')->get(route('staff.empresa.turnos.index', ['fecha' => $turno->fecha->toDateString()]))
            ->assertOk()->assertSee('Corte (+ Diseño)');
        $this->actingAs($this->admin, 'web')->get(route('staff.empresa.turnos.edit', $turno))
            ->assertOk()->assertSee('+ Diseño')->assertSee('+ Líneas');
    }
}
