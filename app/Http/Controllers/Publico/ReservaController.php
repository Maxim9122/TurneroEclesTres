<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\Turno;
use App\Services\DisponibilidadService;
use App\Services\PushService;
use App\Services\SeleccionServicios;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Illuminate\Support\defer;
use Illuminate\View\View;

class ReservaController extends Controller
{
    public function __construct(private DisponibilidadService $disponibilidad)
    {
    }

    public function iniciar(Request $request, Empresa $empresa): View|RedirectResponse
    {
        if (!$empresa->estaActiva()) {
            abort(404);
        }

        if (!Auth::guard('cliente')->check()) {
            return redirect()->guest(route('cliente.login'));
        }

        $seleccion = SeleccionServicios::desde(
            $empresa->id,
            (array) $request->query('servicios', []),
            (array) $request->query('adicionales', []),
        );

        if ($seleccion->vacia()) {
            return redirect()->route('publico.empresa', $empresa)
                ->with('status', 'Elegí al menos un servicio para reservar.');
        }

        $profesionalId = $request->query('profesional');
        $profesional = $profesionalId
            ? Profesional::where('empresa_id', $empresa->id)->where('activo', true)->find($profesionalId)
            : null;

        return view('publico.reserva-fecha', compact('empresa', 'seleccion', 'profesional'));
    }

    public function horariosDisponibles(Request $request, Empresa $empresa): JsonResponse
    {
        $data = $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*' => ['integer'],
            'adicionales' => ['nullable', 'array'],
            'adicionales.*' => ['integer'],
            'profesional' => ['nullable', 'integer'],
        ]);

        $seleccion = SeleccionServicios::desde($empresa->id, $data['servicios'], $data['adicionales'] ?? []);

        $slots = $this->disponibilidad->slotsDisponibles(
            $empresa,
            Carbon::parse($data['fecha']),
            $seleccion->servicios,
            $data['profesional'] ?? null,
            $seleccion->minutosAdicionales(),
        );

        return response()->json(['slots' => $slots]);
    }

    public function confirmar(Request $request, Empresa $empresa): RedirectResponse
    {
        if (!Auth::guard('cliente')->check()) {
            return redirect()->guest(route('cliente.login'));
        }

        $data = $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*' => ['integer'],
            'adicionales' => ['nullable', 'array'],
            'adicionales.*' => ['integer'],
            'profesional' => ['nullable', 'integer'],
        ]);

        $seleccion = SeleccionServicios::desde($empresa->id, $data['servicios'], $data['adicionales'] ?? []);

        if ($seleccion->vacia()) {
            return back()->withErrors(['servicios' => 'Los servicios elegidos ya no están disponibles.']);
        }

        $fecha = Carbon::parse($data['fecha']);

        // Revalidamos disponibilidad al confirmar, por si alguien más
        // reservó ese horario mientras el cliente completaba el formulario.
        $slotsVigentes = $this->disponibilidad->slotsDisponibles(
            $empresa, $fecha, $seleccion->servicios, $data['profesional'] ?? null, $seleccion->minutosAdicionales()
        );

        if (!in_array($data['hora'], $slotsVigentes, true)) {
            return back()->withErrors(['hora' => 'Ese horario ya no está disponible. Elegí otro, por favor.']);
        }

        $horaFin = Carbon::parse($data['hora'])->addMinutes($seleccion->duracionMinutos())->format('H:i');

        $turno = DB::transaction(function () use ($empresa, $data, $fecha, $horaFin, $seleccion) {
            $turno = Turno::create([
                'empresa_id' => $empresa->id,
                'cliente_id' => Auth::guard('cliente')->id(),
                'profesional_id' => $data['profesional'] ?? null,
                'creado_por_usuario_id' => null,
                'fecha' => $fecha->toDateString(),
                'hora_inicio' => $data['hora'],
                'hora_fin' => $horaFin,
                'estado' => 'confirmado',
            ]);

            $seleccion->guardarEn($turno);

            return $turno;
        });

        // Aviso push al staff, después de responderle al cliente (no demora la reserva).
        defer(fn () => app(PushService::class)->nuevoTurno($turno));

        return redirect()->route('cliente.turnos.confirmado', $turno)
            ->with('status', 'Turno reservado correctamente.');
    }

    public function confirmado(Turno $turno): View
    {
        abort_if($turno->cliente_id !== Auth::guard('cliente')->id(), 403);

        $turno->load(['empresa', 'profesional', 'servicios', 'adicionales']);

        return view('publico.reserva-confirmada', compact('turno'));
    }
}