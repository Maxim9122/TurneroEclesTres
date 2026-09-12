<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoriaProductoController extends Controller
{
    public function index(): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $categorias = $empresa->categoriasProductos()
            ->withCount('productos')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('staff.categorias-index', compact('categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        $empresa->categoriasProductos()->create($data);

        return back()->with('status', 'Categoría creada.');
    }

    public function update(Request $request, CategoriaProducto $categoria): RedirectResponse
    {
        $this->autorizar($categoria);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        $categoria->update($data);

        return back()->with('status', 'Categoría actualizada.');
    }

    public function destroy(CategoriaProducto $categoria): RedirectResponse
    {
        $this->autorizar($categoria);

        // Los productos de esta categoría quedan sin categoría (no se borran).
        $categoria->delete();

        return back()->with('status', 'Categoría eliminada. Sus productos quedaron sin categoría.');
    }

    private function autorizar(CategoriaProducto $categoria): void
    {
        abort_if($categoria->empresa_id !== Auth::guard('web')->user()->empresa_id, 403);
    }
}