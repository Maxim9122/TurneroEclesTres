<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TurnoController extends Controller
{
    public function index(Request $request): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $fecha = $request->query('fecha', now()->toDateString());

        $turnos = $empresa->turnos()
            ->with(['cliente', 'profesional', 'servicios'])
            ->whereDate('fecha', $fecha)
            ->orderBy('hora_inicio')
            ->get();

        return view('staff.turnos-index', compact('turnos', 'fecha'));
    }

    public function edit(Turno $turno): View
    {
        $this->autorizar($turno);

        $empresa = $turno->empresa;
        $profesionales = $empresa->profesionales()->where('activo', true)->orderBy('nombre')->get();
        $servicios = $empresa->servicios()->where('activo', true)->orderBy('nombre')->get();
        $serviciosSeleccionados = $turno->servicios->pluck('id')->all();

        return view('staff.turnos-edit', compact('turno', 'profesionales', 'servicios', 'serviciosSeleccionados'));
    }

    public function update(Request $request, Turno $turno): RedirectResponse
    {
        $this->autorizar($turno);

        $data = $request->validate([
            'profesional_id' => ['nullable', 'exists:profesionales,id'],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*' => ['integer', 'exists:servicios,id'],
        ]);

        try {
            DB::transaction(function () use ($turno, $data) {
                $idsActuales = $turno->servicios->pluck('id')->all();
                $idsNuevos = $data['servicios'];

                $paraQuitar = array_diff($idsActuales, $idsNuevos);
                $paraAgregar = array_diff($idsNuevos, $idsActuales);

                if ($paraQuitar) {
                    $turno->servicios()->detach($paraQuitar);
                }

                foreach ($paraAgregar as $servicioId) {
                    $servicio = Servicio::find($servicioId);
                    $turno->servicios()->attach($servicioId, [
                        'precio_al_momento' => $servicio->precio,
                        'duracion_al_momento' => $servicio->duracion_minutos,
                    ]);
                }

                $turno->refresh();

                $duracionTotal = $turno->duracionTotalMinutos();
                $horaFin = Carbon::parse($turno->hora_inicio)->addMinutes($duracionTotal)->format('H:i');

                if ($this->haySolapamiento($turno, $data['profesional_id'] ?? null, $turno->hora_inicio, $horaFin)) {
                    throw ValidationException::withMessages([
                        'profesional_id' => 'Ese profesional ya tiene otro turno en ese horario.',
                    ]);
                }

                $turno->update([
                    'profesional_id' => $data['profesional_id'] ?? null,
                    'hora_fin' => $horaFin,
                ]);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('staff.empresa.turnos.index', ['fecha' => $turno->fecha->toDateString()])
            ->with('status', 'Turno actualizado correctamente.');
    }

    public function cambiarEstado(Request $request, Turno $turno): RedirectResponse
    {
        $this->autorizar($turno);

        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,confirmado,cancelado,completado,no_show'],
        ]);

        $turno->update(['estado' => $data['estado']]);

        return back()->with('status', 'Estado del turno actualizado.');
    }

    private function haySolapamiento(Turno $turno, ?int $profesionalId, string $horaInicio, string $horaFin): bool
    {
        if (!$profesionalId) {
            return false;
        }

        return Turno::where('profesional_id', $profesionalId)
            ->where('fecha', $turno->fecha->toDateString())
            ->where('id', '!=', $turno->id)
            ->whereIn('estado', ['pendiente', 'confirmado'])
            ->where(function ($q) use ($horaInicio, $horaFin) {
                $q->where('hora_inicio', '<', $horaFin)
                    ->where('hora_fin', '>', $horaInicio);
            })
            ->exists();
    }

    private function autorizar(Turno $turno): void
    {
        abort_if($turno->empresa_id !== Auth::guard('web')->user()->empresa_id, 403);
    }
}