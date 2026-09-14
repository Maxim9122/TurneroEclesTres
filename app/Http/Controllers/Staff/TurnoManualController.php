<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Turno;
use App\Services\DisponibilidadService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TurnoManualController extends Controller
{
    public function __construct(private DisponibilidadService $disponibilidad)
    {
    }

    public function iniciar(Request $request): View|RedirectResponse
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $cliente = Cliente::find($request->query('cliente_id'));

        if (!$cliente) {
            return redirect()->route('staff.empresa.renovaciones.index')
                ->with('status', 'No encontramos al cliente indicado.');
        }

        $servicios = $empresa->servicios()->where('activo', true)->orderBy('nombre')->get();
        $profesionales = $empresa->profesionales()->where('activo', true)->orderBy('nombre')->get();
        $servicioPreseleccionado = (int) $request->query('servicio_id');

        return view('staff.turnos-manual-fecha', compact(
            'empresa', 'cliente', 'servicios', 'profesionales', 'servicioPreseleccionado'
        ));
    }

    public function confirmar(Request $request): RedirectResponse
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $data = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*' => ['integer', 'exists:servicios,id'],
            'profesional' => ['nullable', 'exists:profesionales,id'],
        ]);

        $servicios = Servicio::where('empresa_id', $empresa->id)
            ->where('activo', true)
            ->whereIn('id', $data['servicios'])
            ->get();

        $fecha = Carbon::parse($data['fecha']);

        $slotsVigentes = $this->disponibilidad->slotsDisponibles($empresa, $fecha, $servicios, $data['profesional'] ?? null);

        if (!in_array($data['hora'], $slotsVigentes, true)) {
            return back()->withErrors(['hora' => 'Ese horario ya no está disponible. Elegí otro, por favor.'])->withInput();
        }

        $duracionTotal = (int) $servicios->sum('duracion_minutos');
        $horaFin = Carbon::parse($data['hora'])->addMinutes($duracionTotal)->format('H:i');

        $turno = DB::transaction(function () use ($empresa, $data, $fecha, $horaFin, $servicios) {
            $turno = Turno::create([
                'empresa_id' => $empresa->id,
                'cliente_id' => $data['cliente_id'],
                'profesional_id' => $data['profesional'] ?? null,
                'creado_por_usuario_id' => Auth::guard('web')->id(),
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

        return redirect()->route('staff.empresa.turnos.index', ['fecha' => $turno->fecha->toDateString()])
            ->with('status', 'Turno agendado correctamente para ' . $turno->cliente->nombre . '.');
    }
}