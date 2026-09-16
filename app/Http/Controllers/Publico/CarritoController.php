<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Producto;
use App\Services\CarritoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarritoController extends Controller
{
    public function __construct(private CarritoService $carrito)
    {
    }

    public function index(Empresa $empresa): View
    {
        if (!$empresa->estaActiva()) {
            abort(404);
        }

        $items = $this->carrito->items($empresa->id);

        $productos = Producto::where('empresa_id', $empresa->id)
            ->whereIn('id', array_keys($items))
            ->get()
            ->keyBy('id');

        $lineas = collect($items)->map(function ($cantidad, $productoId) use ($productos) {
            $producto = $productos->get($productoId);

            if (!$producto) {
                return null;
            }

            return [
                'producto' => $producto,
                'cantidad' => $cantidad,
                'subtotal' => $producto->precio * $cantidad,
            ];
        })->filter()->values();

        $total = $lineas->sum('subtotal');
        $cantidadCarrito = $this->carrito->cantidadTotal($empresa->id);

        return view('publico.carrito', compact('empresa', 'lineas', 'total', 'cantidadCarrito'));
    }

    public function agregar(Request $request, Empresa $empresa): RedirectResponse
    {
        $data = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'categoria' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $producto = Producto::where('empresa_id', $empresa->id)
            ->where('activo', true)
            ->findOrFail($data['producto_id']);

        $paramsVolver = array_filter([
            'categoria' => $data['categoria'] ?? null,
            'page' => $data['page'] ?? null,
        ]);

        if (!$producto->hayStock($data['cantidad'])) {
            return redirect()->route('publico.empresa', array_merge(['empresa' => $empresa], $paramsVolver))
                ->with('status', 'No hay stock suficiente de ese producto.');
        }

        $this->carrito->agregar($empresa->id, $producto->id, $data['cantidad']);

        return redirect()->route('publico.empresa', array_merge(['empresa' => $empresa], $paramsVolver))
            ->with('status', 'Producto agregado al carrito.');
    }

    public function actualizar(Request $request, Empresa $empresa, Producto $producto): RedirectResponse
    {
        $data = $request->validate(['cantidad' => ['required', 'integer', 'min:0']]);

        $this->carrito->actualizar($empresa->id, $producto->id, $data['cantidad']);

        return back();
    }

    public function quitar(Empresa $empresa, Producto $producto): RedirectResponse
    {
        $this->carrito->quitar($empresa->id, $producto->id);

        return back();
    }
}