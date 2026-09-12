<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\View\View;

class EmpresaPublicaController extends Controller
{
    public function show(Empresa $empresa, \Illuminate\Http\Request $request): View
    {
        if (!$empresa->estaActiva()) {
            abort(404);
        }

        $servicios = $empresa->servicios()->where('activo', true)->orderBy('nombre')->get();
        $profesionales = $empresa->profesionales()->where('activo', true)->orderBy('nombre')->get();

        $categoriaId = $request->query('categoria');
        $categorias = $empresa->categoriasProductos()
            ->whereHas('productos', fn ($q) => $q->where('activo', true))
            ->orderBy('nombre')
            ->get();

        $productos = $empresa->productos()
            ->where('activo', true)
            ->when($categoriaId, fn ($q) => $q->where('categoria_id', $categoriaId))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('publico.empresa', compact('empresa', 'servicios', 'profesionales', 'productos', 'categorias', 'categoriaId'));
    }
}