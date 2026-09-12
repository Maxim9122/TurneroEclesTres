<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\WhatsApp;
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
            ->where('turnos.empresa_id', $empresaId)
            ->whereNotNull('turno_servicios.fecha_renovacion')
            ->select(
                'turno_servicios.fecha_renovacion',
                'servicios.nombre as servicio_nombre',
                'clientes.nombre as cliente_nombre',
                'clientes.telefono as cliente_telefono',
                'clientes.id as cliente_id',
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
}