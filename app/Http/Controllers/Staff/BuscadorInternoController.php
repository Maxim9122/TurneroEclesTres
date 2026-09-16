<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Turno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuscadorInternoController extends Controller
{
    private const POR_PAGINA = 10;

    public function buscar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'numero' => ['required', 'integer'],
            'tipo' => ['required', 'in:turno,pedido'],
        ]);

        $empresaId = Auth::guard('web')->user()->empresa_id;
        $numero = $data['numero'];

        if ($data['tipo'] === 'turno') {
            $turno = Turno::where('id', $numero)->where('empresa_id', $empresaId)->first();

            if ($turno) {
                return redirect()->route('staff.empresa.turnos.index', ['fecha' => $turno->fecha->toDateString()])
                    ->with('status', "Turno #{$numero} encontrado, mostrado abajo resaltado en la fecha {$turno->fecha->format('d/m/Y')}.");
            }

            return back()->with('status', "No encontramos ningún turno #{$numero} en tu empresa.");
        }

        $pedido = Pedido::where('id', $numero)->where('empresa_id', $empresaId)->first();

        if ($pedido) {
            // Calculamos en qué página cae este pedido, según el orden por fecha
            // descendente que usa el listado (sin ningún filtro de estado aplicado).
            $posicion = Pedido::where('empresa_id', $empresaId)
                ->where('created_at', '>', $pedido->created_at)
                ->count();

            $pagina = intdiv($posicion, self::POR_PAGINA) + 1;

            return redirect()->route('staff.empresa.pedidos.index', ['destacar' => $pedido->id, 'page' => $pagina])
                ->with('status', "Pedido #{$numero} encontrado, mostrado abajo resaltado.");
        }

        return back()->with('status', "No encontramos ningún pedido #{$numero} en tu empresa.");
    }
}