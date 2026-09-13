<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $categoriaId = $request->query('categoria');

        $productos = $empresa->productos()
            ->with('imagenes')
            ->when($categoriaId, fn ($q) => $q->where('categoria_id', $categoriaId))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        $categorias = $empresa->categoriasProductos()->orderBy('nombre')->get();

        return view('staff.productos-index', compact('productos', 'categorias', 'categoriaId'));
    }

    public function create(): View
    {
        return view('staff.productos-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $data = $this->validarDatos($request);

        $producto = $empresa->productos()->create($data);

        if ($request->hasFile('foto')) {
            $this->guardarFotoPrincipal($producto, $request);
        }

        $this->guardarImagenesAdicionales($producto, $request);

        return redirect()->route('staff.empresa.productos.index')
            ->with('status', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto): View
    {
        $this->autorizar($producto);

        $producto->load('imagenes');

        return view('staff.productos-edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $this->autorizar($producto);

        $data = $this->validarDatos($request);

        $producto->update($data);

        if ($request->hasFile('foto')) {
            $this->guardarFotoPrincipal($producto, $request);
        }

        $this->guardarImagenesAdicionales($producto, $request);

        return redirect()->route('staff.empresa.productos.index')
            ->with('status', 'Producto actualizado correctamente.');
    }

    public function eliminarImagen(\App\Models\ProductoImagen $imagen): RedirectResponse
    {
        $this->autorizar($imagen->producto);

        Storage::disk('public')->delete($imagen->path);
        $imagen->delete();

        return back()->with('status', 'Imagen eliminada.');
    }

    public function alternarEstado(Producto $producto): RedirectResponse
    {
        $this->autorizar($producto);

        $producto->update(['activo' => !$producto->activo]);

        $mensaje = $producto->activo ? 'Producto activado.' : 'Producto pausado.';

        return back()->with('status', $mensaje);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'imagenes_adicionales.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'categoria_id' => ['nullable', 'exists:categorias_productos,id'],
        ]);
    }

    private function guardarFotoPrincipal(Producto $producto, Request $request): void
    {
        $carpeta = "productos/{$producto->id}";

        foreach (Storage::disk('public')->files($carpeta) as $existente) {
            if (str_starts_with(basename($existente), 'foto.')) {
                Storage::disk('public')->delete($existente);
            }
        }

        $extension = $request->file('foto')->getClientOriginalExtension();
        $producto->foto_path = $request->file('foto')->storeAs($carpeta, "foto.{$extension}", 'public');
        $producto->save();
    }

    /**
     * Agrega imágenes adicionales, respetando el máximo de 3 imágenes totales
     * (principal + adicionales) por producto.
     */
    private function guardarImagenesAdicionales(Producto $producto, Request $request): void
    {
        if (!$request->hasFile('imagenes_adicionales')) {
            return;
        }

        $carpeta = "productos/{$producto->id}";
        $yaExistentes = $producto->imagenes()->count() + ($producto->foto_path ? 1 : 0);
        $espacioDisponible = max(0, 3 - $yaExistentes);

        foreach (array_slice($request->file('imagenes_adicionales'), 0, $espacioDisponible) as $index => $archivo) {
            $nombre = uniqid('img_') . '.' . $archivo->getClientOriginalExtension();
            $ruta = $archivo->storeAs($carpeta, $nombre, 'public');

            $producto->imagenes()->create([
                'path' => $ruta,
                'orden' => $producto->imagenes()->count(),
            ]);
        }
    }

    private function autorizar(Producto $producto): void
    {
        abort_if($producto->empresa_id !== Auth::guard('web')->user()->empresa_id, 403);
    }
}