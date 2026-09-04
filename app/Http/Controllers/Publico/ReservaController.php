<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\Turno;
use App\Services\DisponibilidadService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $servicioIds = (array) $request->query('servicios', []);

        $servicios = Servicio::where('empresa_id', $empresa->id)
            ->where('activo', true)
            ->whereIn('id', $servicioIds)
            ->get();

        if ($servicios->isEmpty()) {
            return redirect()->route('publico.empresa', $empresa)
                ->with('status', 'Elegí al menos un servicio para reservar.');
        }

        $profesionalId = $request->query('profesional');
        $profesional = $profesionalId
            ? Profesional::where('empresa_id', $empresa->id)->where('activo', true)->find($profesionalId)
            : null;

        return view('publico.reserva-fecha', compact('empresa', 'servicios', 'profesional'));
    }

    public function horariosDisponibles(Request $request, Empresa $empresa): JsonResponse
    {
        $data = $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*' => ['integer'],
            'profesional' => ['nullable', 'integer'],
        ]);

        $servicios = Servicio::where('empresa_id', $empresa->id)
            ->where('activo', true)
            ->whereIn('id', $data['servicios'])
            ->get();

        $slots = $this->disponibilidad->slotsDisponibles(
            $empresa,
            Carbon::parse($data['fecha']),
            $servicios,
            $data['profesional'] ?? null
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
            'profesional' => ['nullable', 'integer'],
        ]);

        $servicios = Servicio::where('empresa_id', $empresa->id)
            ->where('activo', true)
            ->whereIn('id', $data['servicios'])
            ->get();

        if ($servicios->isEmpty()) {
            return back()->withErrors(['servicios' => 'Los servicios elegidos ya no están disponibles.']);
        }

        $fecha = Carbon::parse($data['fecha']);

        // Revalidamos disponibilidad al confirmar, por si alguien más
        // reservó ese horario mientras el cliente completaba el formulario.
        $slotsVigentes = $this->disponibilidad->slotsDisponibles($empresa, $fecha, $servicios, $data['profesional'] ?? null);

        if (!in_array($data['hora'], $slotsVigentes, true)) {
            return back()->withErrors(['hora' => 'Ese horario ya no está disponible. Elegí otro, por favor.']);
        }

        $duracionTotal = (int) $servicios->sum('duracion_minutos');
        $horaFin = Carbon::parse($data['hora'])->addMinutes($duracionTotal)->format('H:i');

        $turno = DB::transaction(function () use ($empresa, $data, $fecha, $horaFin, $servicios) {
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

            foreach ($servicios as $servicio) {
                $turno->servicios()->attach($servicio->id, [
                    'precio_al_momento' => $servicio->precio,
                    'duracion_al_momento' => $servicio->duracion_minutos,
                ]);
            }

            return $turno;
        });

        return redirect()->route('cliente.turnos.confirmado', $turno)
            ->with('status', 'Turno reservado correctamente.');
    }

    public function confirmado(Turno $turno): View
    {
        abort_if($turno->cliente_id !== Auth::guard('cliente')->id(), 403);

        $turno->load(['empresa', 'profesional', 'servicios']);

        return view('publico.reserva-confirmada', compact('turno'));
    }
}