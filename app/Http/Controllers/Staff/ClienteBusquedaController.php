<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteBusquedaController extends Controller
{
    public function buscar(Request $request)
    {
        $q = $request->query('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $clientes = Cliente::where('nombre', 'like', "%{$q}%")
            ->orWhere('telefono', 'like', "%{$q}%")
            ->orderBy('nombre')
            ->limit(10)
            ->get(['id', 'nombre', 'telefono']);

        return response()->json($clientes);
    }
}