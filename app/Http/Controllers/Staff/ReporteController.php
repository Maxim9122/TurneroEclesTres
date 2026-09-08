<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function comisiones(Request $request): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $desde = $request->query('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->query('hasta', now()->toDateString());

        $profesionales = $empresa->profesionales()
            ->with(['turnos' => function ($q) use ($desde, $hasta) {
                $q->where('estado', 'completado')
                    ->whereDate('fecha', '>=', $desde)
                    ->whereDate('fecha', '<=', $hasta)
                    ->with(['servicios', 'cliente'])
                    ->orderBy('fecha')
                    ->orderBy('hora_inicio');
            }])
            ->orderBy('nombre')
            ->get();

        $filas = $profesionales->map(function ($profesional) {
        $recaudado = $profesional->turnos->sum(fn ($turno) => $turno->precioTotal());
        $comision = $recaudado * ($profesional->porcentaje_comision / 100);

        return [
            'profesional' => $profesional,
            'turnos' => $profesional->turnos,
            'cantidad_turnos' => $profesional->turnos->count(),
            'recaudado' => $recaudado,
            'porcentaje' => $profesional->porcentaje_comision,
            'comision' => $comision,
            'para_el_negocio' => $recaudado - $comision,
        ];
    })->filter(fn ($fila) => $fila['cantidad_turnos'] > 0)->values();

        $totales = [
            'recaudado' => $filas->sum('recaudado'),
            'comision' => $filas->sum('comision'),
            'para_el_negocio' => $filas->sum('para_el_negocio'),
        ];

        return view('staff.reporte-comisiones', compact('filas', 'totales', 'desde', 'hasta'));
    }

    public function pedidos(Request $request): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $desde = $request->query('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->query('hasta', now()->toDateString());

        $pedidos = $empresa->pedidos()
            ->whereDate('created_at', '>=', $desde)
            ->whereDate('created_at', '<=', $hasta)
            ->get();

        $porEstado = $pedidos->groupBy('estado')->map->count();

        $entregados = $pedidos->where('estado', 'entregado');
        $cancelados = $pedidos->where('estado', 'cancelado');

        $resumen = [
            'cantidad_total' => $pedidos->count(),
            'total_facturado' => $entregados->sum('total'),
            'cantidad_entregados' => $entregados->count(),
            'cantidad_cancelados' => $cancelados->count(),
            'por_estado' => $porEstado,
            'por_metodo_entrega' => $pedidos->groupBy('metodo_entrega')->map->count(),
        ];

        return view('staff.reporte-pedidos', compact('pedidos', 'resumen', 'desde', 'hasta'));
    }
}