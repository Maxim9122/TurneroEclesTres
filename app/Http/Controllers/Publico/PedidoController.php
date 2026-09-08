<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\CarritoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function __construct(private CarritoService $carrito)
    {
    }

    public function iniciar(Empresa $empresa): View|RedirectResponse
    {
        if (!$empresa->estaActiva()) {
            abort(404);
        }

        if (!Auth::guard('cliente')->check()) {
            return redirect()->guest(route('cliente.login'));
        }

        $items = $this->carrito->items($empresa->id);

        if (empty($items)) {
            return redirect()->route('publico.empresa', $empresa)->with('status', 'Tu carrito está vacío.');
        }

        $productos = Producto::where('empresa_id', $empresa->id)->whereIn('id', array_keys($items))->get();
        $direcciones = Auth::guard('cliente')->user()->direcciones;

        return view('publico.checkout', compact('empresa', 'productos', 'items', 'direcciones'));
    }

    public function confirmar(Request $request, Empresa $empresa): RedirectResponse
    {
        if (!Auth::guard('cliente')->check()) {
            return redirect()->guest(route('cliente.login'));
        }

        $data = $request->validate([
            'metodo_entrega' => ['required', 'in:retiro,envio'],
            'direccion_envio_id' => ['nullable', 'exists:direcciones,id'],
            'nueva_barrio' => ['nullable', 'string', 'max:120'],
            'nueva_calle' => ['nullable', 'string', 'max:150'],
            'nueva_altura' => ['nullable', 'string', 'max:20'],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        $items = $this->carrito->items($empresa->id);

        if (empty($items)) {
            return redirect()->route('publico.empresa', $empresa)->with('status', 'Tu carrito está vacío.');
        }

        if ($data['metodo_entrega'] === 'envio' && empty($data['direccion_envio_id']) && empty($data['nueva_calle'])) {
            return back()->withErrors(['direccion_envio_id' => 'Elegí una dirección o cargá una nueva para el envío.'])->withInput();
        }

        try {
            $pedido = DB::transaction(function () use ($empresa, $data, $items) {
                $productos = Producto::where('empresa_id', $empresa->id)
                    ->whereIn('id', array_keys($items))
                    ->lockForUpdate()
                    ->get();

                $total = 0;

                foreach ($productos as $producto) {
                    $cantidad = $items[$producto->id];

                    if (!$producto->hayStock($cantidad)) {
                        throw ValidationException::withMessages([
                            'stock' => "No hay stock suficiente de \"{$producto->nombre}\". Quedan {$producto->stock} unidades.",
                        ]);
                    }

                    $total += $producto->precio * $cantidad;
                }

                $direccionId = $data['direccion_envio_id'] ?? null;

                if ($data['metodo_entrega'] === 'envio' && !$direccionId) {
                    $direccion = Auth::guard('cliente')->user()->direcciones()->create([
                        'barrio' => $data['nueva_barrio'] ?? null,
                        'calle' => $data['nueva_calle'] ?? null,
                        'altura' => $data['nueva_altura'] ?? null,
                    ]);
                    $direccionId = $direccion->id;
                }

                $pedido = Pedido::create([
                    'empresa_id' => $empresa->id,
                    'cliente_id' => Auth::guard('cliente')->id(),
                    'metodo_entrega' => $data['metodo_entrega'],
                    'direccion_envio_id' => $data['metodo_entrega'] === 'envio' ? $direccionId : null,
                    'estado' => 'pendiente',
                    'total' => $total,
                    'notas' => $data['notas'] ?? null,
                ]);

                foreach ($productos as $producto) {
                    $cantidad = $items[$producto->id];

                    $pedido->items()->create([
                        'producto_id' => $producto->id,
                        'cantidad' => $cantidad,
                        'precio_al_momento' => $producto->precio,
                    ]);

                    $producto->decrement('stock', $cantidad);
                }

                return $pedido;
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $this->carrito->vaciar($empresa->id);

        return redirect()->route('cliente.pedidos.confirmado', $pedido)
            ->with('status', 'Pedido realizado correctamente.');
    }

    public function confirmado(Pedido $pedido): View
    {
        abort_if($pedido->cliente_id !== Auth::guard('cliente')->id(), 403);

        $pedido->load(['empresa', 'items.producto', 'direccionEnvio']);

        return view('publico.pedido-confirmado', compact('pedido'));
    }
}