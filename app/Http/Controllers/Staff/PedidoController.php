<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function index(Request $request): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $estado = $request->query('estado');

        $pedidos = $empresa->pedidos()
            ->with(['cliente', 'items.producto', 'direccionEnvio'])
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->orderByDesc('created_at')
            ->get();

        return view('staff.pedidos-index', compact('pedidos', 'estado'));
    }

    public function cambiarEstado(Request $request, Pedido $pedido): RedirectResponse
    {
        abort_if($pedido->empresa_id !== Auth::guard('web')->user()->empresa_id, 403);

        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,confirmado,en_preparacion,listo,enviado,entregado,cancelado'],
        ]);

        $pedido->update(['estado' => $data['estado']]);

        return back()->with('status', 'Estado del pedido actualizado.');
    }
}