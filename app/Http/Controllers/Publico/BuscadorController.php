<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuscadorController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->query('q');
        $rubro = $request->query('rubro');

        $empresas = Empresa::query()
            ->where('estado', 'activa')
            ->with('direcciones')
            ->when($q, function ($query) use ($q) {
                $query->whereHas('direcciones', function ($sub) use ($q) {
                    $sub->where('ciudad', 'like', "%{$q}%")
                        ->orWhere('barrio', 'like', "%{$q}%")
                        ->orWhere('calle', 'like', "%{$q}%");
                });
            })
            ->when($rubro, fn ($query) => $query->where('rubro', $rubro))
            ->orderBy('nombre')
            ->get();

        return view('publico.buscador', compact('empresas', 'q', 'rubro'));
    }
}