<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Services\CarritoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmpresaPublicaController extends Controller
{
    public function __construct(private CarritoService $carrito)
    {
    }

    public function show(Empresa $empresa, Request $request): View
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

        $cantidadCarrito = $this->carrito->cantidadTotal($empresa->id);

        return view('publico.empresa', compact(
            'empresa', 'servicios', 'profesionales', 'productos', 'categorias', 'categoriaId', 'cantidadCarrito'
        ));
    }
}