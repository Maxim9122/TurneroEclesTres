<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\EmpresaHorario;
use App\Models\Profesional;
use App\Models\ProfesionalHorario;
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

/**
 * Horario general opcional: si el negocio no cargó ninguno, se atiende todos los días de 07:00 a 00:00.
 */
class HorarioPorDefectoTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Usuario $admin;
    private Profesional $profesional;
    private Servicio $corte;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->app->instance(PushService::class, Mockery::mock(PushService::class)->shouldIgnoreMissing());

        $this->empresa = Empresa::create(['nombre' => 'Studio', 'rubro' => 'peluqueria', 'estado' => 'activa']);
        $this->admin = Usuario::create([
            'empresa_id' => $this->empresa->id, 'nombre' => 'Admin', 'email' => 'admin@test.com',
            'password' => 'secreto123', 'rol' => 'admin', 'activo' => true,
        ]);
        $this->profesional = Profesional::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Katherin', 'activo' => true, 'porcentaje_comision' => 0]);
        $this->corte = Servicio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Corte', 'duracion_minutos' => 30, 'precio' => 5000, 'activo' => true]);
    }

    private function slots(Carbon $fecha, ?Servicio $servicio = null, ?int $profesionalId = null): array
    {
        return array_values(app(DisponibilidadService::class)
            ->slotsDisponibles($this->empresa->fresh(), $fecha, collect([$servicio ?? $this->corte]), $profesionalId));
    }

    public function test_sin_horario_cargado_se_atiende_todos_los_dias_de_7_a_24(): void
    {
        $lunes = Carbon::now()->next(Carbon::MONDAY);

        for ($i = 0; $i < 7; $i++) {
            $slots = $this->slots($lunes->copy()->addDays($i));
            $this->assertSame('07:00', $slots[0], 'Abre a las 07:00 el día ' . $i);
            $this->assertSame('23:00', end($slots), 'El último turno de 30 min empieza 23:00 y termina 23:30');
        }

        // También vale eligiendo la profesional en particular
        $this->assertSame('07:00', $this->slots($lunes, null, $this->profesional->id)[0]);
    }

    public function test_ningun_turno_termina_despues_de_medianoche(): void
    {
        $largo = Servicio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Reflejos', 'duracion_minutos' => 180, 'precio' => 90, 'activo' => true]);
        $slots = $this->slots(Carbon::now()->next(Carbon::MONDAY), $largo);

        $this->assertSame('20:30', end($slots), '180 min desde las 20:30 termina 23:30; desde las 21:00 terminaría a medianoche o después');
    }

    public function test_el_cliente_puede_reservar_sin_que_el_negocio_cargue_horarios(): void
    {
        $cliente = Cliente::create(['nombre' => 'Juan', 'email' => 'juan@test.com', 'password' => 'secreto123']);
        $fecha = Carbon::now()->next(Carbon::WEDNESDAY)->toDateString();

        $this->getJson(route('publico.reserva.horarios', $this->empresa) . "?fecha={$fecha}&servicios[]={$this->corte->id}")
            ->assertOk()->assertJsonFragment(['07:00']);

        $this->actingAs($cliente, 'cliente')->post(route('publico.reserva.confirmar', $this->empresa), [
            'fecha' => $fecha, 'hora' => '22:30', 'servicios' => [$this->corte->id],
        ])->assertRedirect();

        $turno = Turno::first();
        $this->assertNotNull($turno);
        $this->assertSame('23:00', substr($turno->hora_fin, 0, 5));
    }

    public function test_al_cargar_un_horario_rige_solo_ese(): void
    {
        $lunes = Carbon::now()->next(Carbon::MONDAY);

        $this->actingAs($this->admin, 'web')->post(route('staff.empresa.horario-general.store'), [
            'dias' => [1], 'hora_inicio' => '09:00', 'hora_fin' => '12:00',
        ])->assertSessionHasNoErrors();

        $this->assertSame(['09:00', '09:30', '10:00', '10:30', '11:00', '11:30'], $this->slots($lunes));
        $this->assertSame([], $this->slots($lunes->copy()->addDay()), 'El martes no se cargó: queda sin atención');
    }

    public function test_al_quitar_todos_los_rangos_vuelve_el_horario_por_defecto(): void
    {
        $horario = EmpresaHorario::create(['empresa_id' => $this->empresa->id, 'dia_semana' => 1, 'hora_inicio' => '09:00', 'hora_fin' => '12:00']);
        $martes = Carbon::now()->next(Carbon::TUESDAY);
        $this->assertSame([], $this->slots($martes));

        $this->actingAs($this->admin, 'web')->delete(route('staff.empresa.horario-general.destroy', $horario));

        $this->assertSame('07:00', $this->slots($martes)[0]);
    }

    public function test_el_horario_propio_del_profesional_tiene_prioridad(): void
    {
        ProfesionalHorario::create(['profesional_id' => $this->profesional->id, 'dia_semana' => 1, 'hora_inicio' => '14:00', 'hora_fin' => '15:00']);
        $lunes = Carbon::now()->next(Carbon::MONDAY);

        $this->assertSame(['14:00', '14:30'], $this->slots($lunes, null, $this->profesional->id));
        $this->assertSame([], $this->slots($lunes->copy()->addDay(), null, $this->profesional->id));
    }

    public function test_las_pantallas_muestran_el_horario_por_defecto(): void
    {
        $this->actingAs($this->admin, 'web')->get(route('staff.empresa.horario-general.edit'))
            ->assertOk()->assertSee('horario por defecto: todos los días de')->assertSee('07:00 — 00:00')->assertSee('(por defecto)');

        $this->actingAs($this->admin, 'web')->get(route('staff.empresa.profesionales.horarios.edit', $this->profesional))
            ->assertOk()->assertSee('rige el horario por defecto');

        EmpresaHorario::create(['empresa_id' => $this->empresa->id, 'dia_semana' => 1, 'hora_inicio' => '09:00', 'hora_fin' => '12:00']);

        $this->actingAs($this->admin, 'web')->get(route('staff.empresa.horario-general.edit'))
            ->assertOk()->assertDontSee('(por defecto)')->assertSee('Sin atención este día.');
    }
}
