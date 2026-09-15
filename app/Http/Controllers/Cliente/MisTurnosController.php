<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MisTurnosController extends Controller
{
    public function index(Request $request): View
    {
        $clienteId = Auth::guard('cliente')->id();
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $proximos = Turno::with(['empresa', 'profesional', 'servicios'])
            ->where('cliente_id', $clienteId)
            ->whereIn('estado', ['pendiente', 'confirmado'])
            ->where(function ($q) {
                $q->where('fecha', '>', now()->toDateString())
                    ->orWhere(function ($q2) {
                        $q2->where('fecha', now()->toDateString())
                            ->where('hora_inicio', '>=', now()->format('H:i'));
                    });
            })
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        $historial = Turno::with(['empresa', 'profesional', 'servicios'])
            ->where('cliente_id', $clienteId)
            ->where(function ($q) {
                $q->whereIn('estado', ['completado', 'cancelado', 'no_show'])
                    ->orWhere(function ($q2) {
                        $q2->whereIn('estado', ['pendiente', 'confirmado'])
                            ->where('fecha', '<', now()->toDateString());
                    });
            })
            ->when($desde, fn ($q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha', '<=', $hasta))
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->paginate(10)
            ->withQueryString();

        return view('cliente.mis-turnos', compact('proximos', 'historial', 'desde', 'hasta'));
    }

    public function cancelar(Turno $turno): RedirectResponse
    {
        abort_if($turno->cliente_id !== Auth::guard('cliente')->id(), 403);

        if (!in_array($turno->estado, ['pendiente', 'confirmado'])) {
            return back()->with('status', 'Este turno ya no se puede cancelar.');
        }

        $turno->update(['estado' => 'cancelado']);

        return back()->with('status', 'Turno cancelado correctamente.');
    }
}