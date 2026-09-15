<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TurnoRapidoController extends Controller
{
    public function iniciar(): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $servicios = $empresa->servicios()->where('activo', true)->orderBy('nombre')->get();
        $profesionales = $empresa->profesionales()->where('activo', true)->orderBy('nombre')->get();

        return view('staff.turno-rapido', compact('servicios', 'profesionales'));
    }

    public function confirmar(Request $request)
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $data = $request->validate([
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'cliente_nombre_nuevo' => ['required_without:cliente_id', 'nullable', 'string', 'max:255'],
            'cliente_telefono_nuevo' => ['nullable', 'string', 'max:30'],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*' => ['integer', 'exists:servicios,id'],
            'profesional' => ['nullable', 'exists:profesionales,id'],
            'fecha_listado' => ['nullable', 'date'],
        ]);

        $servicios = Servicio::where('empresa_id', $empresa->id)
            ->where('activo', true)
            ->whereIn('id', $data['servicios'])
            ->get();

        try {
            $turno = DB::transaction(function () use ($empresa, $data, $servicios) {
                $ahora = Carbon::now();
                $duracionTotal = (int) $servicios->sum('duracion_minutos');
                $horaInicio = $ahora->format('H:i');
                $horaFin = $ahora->copy()->addMinutes($duracionTotal)->format('H:i');

                if (!empty($data['profesional'])) {
                    $solapa = Turno::where('profesional_id', $data['profesional'])
                        ->where('fecha', $ahora->toDateString())
                        ->whereIn('estado', ['pendiente', 'confirmado'])
                        ->lockForUpdate()
                        ->where(function ($q) use ($horaInicio, $horaFin) {
                            $q->where('hora_inicio', '<', $horaFin)
                                ->where('hora_fin', '>', $horaInicio);
                        })
                        ->exists();

                    if ($solapa) {
                        throw ValidationException::withMessages([
                            'profesional' => 'Ese profesional ya tiene otro turno asignado en este horario. Elegí otro profesional o dejalo "sin preferencia".',
                        ]);
                    }
                }

                $cliente = $data['cliente_id']
                    ? Cliente::find($data['cliente_id'])
                    : $this->crearClienteRapido($data['cliente_nombre_nuevo'], $data['cliente_telefono_nuevo'] ?? null);

                $turno = Turno::create([
                    'empresa_id' => $empresa->id,
                    'cliente_id' => $cliente->id,
                    'profesional_id' => $data['profesional'] ?? null,
                    'creado_por_usuario_id' => Auth::guard('web')->id(),
                    'fecha' => $ahora->toDateString(),
                    'hora_inicio' => $horaInicio,
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
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'mensaje' => 'Turno registrado para ' . $turno->cliente->nombre . '.']);
        }

        return redirect()->route('staff.empresa.turnos.index', ['fecha' => $turno->fecha->toDateString()])
            ->with('status', 'Turno registrado para ' . $turno->cliente->nombre . ' (orden de llegada).');
    }

    private function crearClienteRapido(string $nombre, ?string $telefono): Cliente
    {
        $emailPlaceholder = 'walkin-' . Str::random(10) . '@sin-email.eclestres.local';

        return Cliente::create([
            'nombre' => $nombre,
            'telefono' => $telefono,
            'email' => $emailPlaceholder,
            'password' => Hash::make(Str::random(20)),
        ]);
    }
}