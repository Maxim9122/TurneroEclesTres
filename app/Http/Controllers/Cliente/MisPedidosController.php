<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MisPedidosController extends Controller
{
    public function index(): View
    {
        $pedidos = Pedido::with(['empresa', 'items.producto', 'direccionEnvio'])
            ->where('cliente_id', Auth::guard('cliente')->id())
            ->orderByDesc('created_at')
            ->get();

        return view('cliente.mis-pedidos', compact('pedidos'));
    }
}