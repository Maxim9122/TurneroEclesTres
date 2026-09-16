<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Turno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class NotificacionController extends Controller
{
    public function verificar(Request $request)
    {
        $empresaId = Auth::guard('web')->user()->empresa_id;

        if (!$empresaId) {
            return response()->json(['turnos' => 0, 'pedidos' => 0, 'ahora' => now()->toIso8601String()]);
        }

        $desde = $request->query('desde');
        $desde = $desde ? Carbon::parse($desde) : now()->subMinutes(1);

        $turnosNuevos = Turno::where('empresa_id', $empresaId)
            ->where('created_at', '>', $desde)
            ->count();

        $pedidosNuevos = Pedido::where('empresa_id', $empresaId)
            ->where('created_at', '>', $desde)
            ->count();

        return response()->json([
            'turnos' => $turnosNuevos,
            'pedidos' => $pedidosNuevos,
            'ahora' => now()->toIso8601String(),
        ]);
    }
}