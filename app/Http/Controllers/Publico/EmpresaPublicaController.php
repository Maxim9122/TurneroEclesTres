<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\View\View;

class EmpresaPublicaController extends Controller
{
    public function show(Empresa $empresa): View
    {
        if (!$empresa->estaActiva()) {
            abort(404);
        }

        $servicios = $empresa->servicios()->where('activo', true)->orderBy('nombre')->get();
        $profesionales = $empresa->profesionales()->where('activo', true)->orderBy('nombre')->get();
        $productos = $empresa->productos()->where('activo', true)->orderBy('nombre')->get();

        return view('publico.empresa', compact('empresa', 'servicios', 'profesionales', 'productos'));
    }
}