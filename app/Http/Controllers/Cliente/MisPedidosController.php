<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MisPedidosController extends Controller
{
    public function index(Request $request): View
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $pedidos = Pedido::with(['empresa', 'items.producto', 'direccionEnvio'])
            ->where('cliente_id', Auth::guard('cliente')->id())
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('cliente.mis-pedidos', compact('pedidos', 'desde', 'hasta'));
    }
}