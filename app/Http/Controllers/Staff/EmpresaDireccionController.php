<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmpresaDireccionController extends Controller
{
    public function edit(): View
    {
        $empresa = Auth::guard('web')->user()->empresa;
        $direccion = $empresa->direccionPrincipal();

        return view('staff.empresa-direccion', compact('empresa', 'direccion'));
    }

    public function update(Request $request): RedirectResponse
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $data = $request->validate([
            'ciudad' => ['required', 'string', 'max:120'],
            'barrio' => ['required', 'string', 'max:120'],
            'calle' => ['required', 'string', 'max:150'],
            'altura' => ['nullable', 'string', 'max:20'],
        ]);

        // Como una empresa tiene una única dirección "principal", actualizamos
        // la existente si ya había una, o creamos la primera.
        $empresa->direcciones()->updateOrCreate([], $data);

        return back()->with('status', 'Dirección guardada correctamente.');
    }
}