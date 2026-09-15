<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RenovacionController extends Controller
{
    public function index(): View
    {
        $empresaId = Auth::guard('web')->user()->empresa_id;
        $hoy = now()->toDateString();
        $limite = now()->addDays(5)->toDateString();

        $base = DB::table('turno_servicios')
            ->join('turnos', 'turnos.id', '=', 'turno_servicios.turno_id')
            ->join('servicios', 'servicios.id', '=', 'turno_servicios.servicio_id')
            ->join('clientes', 'clientes.id', '=', 'turnos.cliente_id')
            ->join('empresas', 'empresas.id', '=', 'turnos.empresa_id')
            ->where('turnos.empresa_id', $empresaId)
            ->whereNotNull('turno_servicios.fecha_renovacion')
            ->whereNull('turno_servicios.renovacion_resuelta_at')
            ->select(
                'turno_servicios.id as turno_servicio_id',
                'turno_servicios.fecha_renovacion',
                'servicios.id as servicio_id',
                'servicios.nombre as servicio_nombre',
                'clientes.nombre as cliente_nombre',
                'clientes.telefono as cliente_telefono',
                'clientes.id as cliente_id',
                'empresas.nombre as empresa_nombre',
                'turnos.fecha as fecha_turno_original'
            );

        $proximas = (clone $base)
            ->whereBetween('turno_servicios.fecha_renovacion', [$hoy, $limite])
            ->orderBy('turno_servicios.fecha_renovacion')
            ->get();

        $vencidas = (clone $base)
            ->where('turno_servicios.fecha_renovacion', '<', $hoy)
            ->orderByDesc('turno_servicios.fecha_renovacion')
            ->limit(30)
            ->get();

        return view('staff.renovaciones-index', compact('proximas', 'vencidas'));
    }

    public function marcarResuelta(int $turnoServicioId): RedirectResponse
    {
        $empresaId = Auth::guard('web')->user()->empresa_id;

        // Confirmamos que ese turno_servicio pertenezca a la empresa del staff logueado
        $pertenece = DB::table('turno_servicios')
            ->join('turnos', 'turnos.id', '=', 'turno_servicios.turno_id')
            ->where('turno_servicios.id', $turnoServicioId)
            ->where('turnos.empresa_id', $empresaId)
            ->exists();

        abort_unless($pertenece, 403);

        DB::table('turno_servicios')
            ->where('id', $turnoServicioId)
            ->update(['renovacion_resuelta_at' => now()]);

        return back()->with('status', 'Renovación marcada como resuelta.');
    }
}