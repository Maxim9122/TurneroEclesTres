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
    public function index(): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $productos = $empresa->productos()->orderBy('nombre')->get();

        return view('staff.productos-index', compact('productos'));
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
            $this->guardarFoto($producto, $request);
        }

        return redirect()->route('staff.empresa.productos.index')
            ->with('status', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto): View
    {
        $this->autorizar($producto);

        return view('staff.productos-edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $this->autorizar($producto);

        $data = $this->validarDatos($request);

        $producto->update($data);

        if ($request->hasFile('foto')) {
            $this->guardarFoto($producto, $request);
        }

        return redirect()->route('staff.empresa.productos.index')
            ->with('status', 'Producto actualizado correctamente.');
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
        ]);
    }

    private function guardarFoto(Producto $producto, Request $request): void
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

    private function autorizar(Producto $producto): void
    {
        abort_if($producto->empresa_id !== Auth::guard('web')->user()->empresa_id, 403);
    }
}