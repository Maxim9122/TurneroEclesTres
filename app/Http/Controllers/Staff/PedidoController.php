<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function index(Request $request): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $estado = $request->query('estado');
        $destacar = $request->query('destacar');

        $pedidos = $empresa->pedidos()
            ->with(['cliente', 'items.producto', 'direccionEnvio', 'historiales'])
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->orderByDesc('created_at')
            ->get();

        return view('staff.pedidos-index', compact('pedidos', 'estado', 'destacar'));
    }

    public function edit(Pedido $pedido): View
    {
        $this->autorizar($pedido);

        $empresa = $pedido->empresa;
        $productos = $empresa->productos()->orderBy('nombre')->get();
        $cantidadesActuales = $pedido->items->pluck('cantidad', 'producto_id');
        $direcciones = $pedido->cliente->direcciones;

        return view('staff.pedidos-edit', compact('pedido', 'productos', 'cantidadesActuales', 'direcciones'));
    }

    public function update(Request $request, Pedido $pedido): RedirectResponse
    {
        $this->autorizar($pedido);

        $data = $request->validate([
            'productos' => ['nullable', 'array'],
            'productos.*' => ['integer', 'min:0'],
            'metodo_entrega' => ['required', 'in:retiro,envio'],
            'direccion_envio_id' => ['nullable', 'exists:direcciones,id'],
            'nueva_barrio' => ['nullable', 'string', 'max:120'],
            'nueva_calle' => ['nullable', 'string', 'max:150'],
            'nueva_altura' => ['nullable', 'string', 'max:20'],
            'notas' => ['nullable', 'string', 'max:500'],
            'motivo' => ['required', 'string', 'max:500'],
        ]);

        $cantidadesNuevas = collect($data['productos'] ?? [])->filter(fn ($cant) => $cant > 0);

        if ($cantidadesNuevas->isEmpty()) {
            return back()->withErrors(['productos' => 'La venta debe tener al menos un producto.'])->withInput();
        }

        try {
            DB::transaction(function () use ($pedido, $data, $cantidadesNuevas) {
                // 1. Guardar la "foto" de cómo estaba el pedido antes de este cambio.
                $itemsAnterior = $pedido->items->map(fn ($item) => [
                    'producto_id' => $item->producto_id,
                    'nombre' => $item->producto->nombre,
                    'cantidad' => $item->cantidad,
                    'precio_al_momento' => $item->precio_al_momento,
                ])->all();

                $pedido->historiales()->create([
                    'usuario_id' => Auth::guard('web')->id(),
                    'motivo' => $data['motivo'],
                    'items_anterior' => $itemsAnterior,
                    'total_anterior' => $pedido->total,
                    'metodo_entrega_anterior' => $pedido->metodo_entrega,
                    'direccion_envio_id_anterior' => $pedido->direccion_envio_id,
                ]);

                // 2. Devolver al stock las cantidades que tenía el pedido ANTES del cambio.
                foreach ($pedido->items as $item) {
                    $item->producto->increment('stock', $item->cantidad);
                }

                // 3. Validar que haya stock suficiente para las cantidades NUEVAS
                //    (el stock ya incluye lo devuelto en el paso anterior).
                $productos = Producto::where('empresa_id', $pedido->empresa_id)
                    ->whereIn('id', $cantidadesNuevas->keys())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $total = 0;

                foreach ($cantidadesNuevas as $productoId => $cantidad) {
                    $producto = $productos->get($productoId);

                    if (!$producto || $producto->stock < $cantidad) {
                        throw ValidationException::withMessages([
                            'productos' => "No hay stock suficiente de \"{$producto?->nombre}\". Disponible: {$producto?->stock}.",
                        ]);
                    }

                    $total += $producto->precio * $cantidad;
                }

                // 4. Reemplazar los items del pedido y descontar el stock nuevo.
                $pedido->items()->delete();

                foreach ($cantidadesNuevas as $productoId => $cantidad) {
                    $producto = $productos->get($productoId);

                    $pedido->items()->create([
                        'producto_id' => $productoId,
                        'cantidad' => $cantidad,
                        'precio_al_momento' => $producto->precio,
                    ]);

                    $producto->decrement('stock', $cantidad);
                }

                // 5. Dirección de envío nueva, si corresponde.
                $direccionId = $data['direccion_envio_id'] ?? null;

                if ($data['metodo_entrega'] === 'envio' && !$direccionId && !empty($data['nueva_calle'])) {
                    $direccion = $pedido->cliente->direcciones()->create([
                        'barrio' => $data['nueva_barrio'] ?? null,
                        'calle' => $data['nueva_calle'] ?? null,
                        'altura' => $data['nueva_altura'] ?? null,
                    ]);
                    $direccionId = $direccion->id;
                }

                // 6. Actualizar el pedido.
                $pedido->update([
                    'metodo_entrega' => $data['metodo_entrega'],
                    'direccion_envio_id' => $data['metodo_entrega'] === 'envio' ? $direccionId : null,
                    'notas' => $data['notas'] ?? $pedido->notas,
                    'total' => $total,
                ]);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('staff.empresa.pedidos.index')
            ->with('status', "Venta #{$pedido->id} modificada correctamente.");
    }

    public function cambiarEstado(Request $request, Pedido $pedido): RedirectResponse
    {
        $this->autorizar($pedido);

        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,confirmado,en_preparacion,listo,enviado,entregado,cancelado'],
        ]);

        $pedido->update(['estado' => $data['estado']]);

        return back()->with('status', 'Estado del pedido actualizado.');
    }

    private function autorizar(Pedido $pedido): void
    {
        abort_if($pedido->empresa_id !== Auth::guard('web')->user()->empresa_id, 403);
    }
}