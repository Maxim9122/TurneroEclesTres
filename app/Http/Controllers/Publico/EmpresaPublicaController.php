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

        return view('publico.empresa', compact('empresa'));
    }
}