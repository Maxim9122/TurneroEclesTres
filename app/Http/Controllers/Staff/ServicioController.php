<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServicioController extends Controller
{
    public function index(): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $servicios = $empresa->servicios()->orderBy('nombre')->get();

        return view('staff.servicios-index', compact('servicios'));
    }

    public function create(): View
    {
        return view('staff.servicios-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $data = $this->validarDatos($request);

        $empresa->servicios()->create($data);

        return redirect()->route('staff.empresa.servicios.index')
            ->with('status', 'Servicio creado correctamente.');
    }

    public function edit(Servicio $servicio): View
    {
        $this->autorizar($servicio);

        return view('staff.servicios-edit', compact('servicio'));
    }

    public function update(Request $request, Servicio $servicio): RedirectResponse
    {
        $this->autorizar($servicio);

        $data = $this->validarDatos($request);

        $servicio->update($data);

        return redirect()->route('staff.empresa.servicios.index')
            ->with('status', 'Servicio actualizado correctamente.');
    }

    public function alternarEstado(Servicio $servicio): RedirectResponse
    {
        $this->autorizar($servicio);

        $servicio->update(['activo' => !$servicio->activo]);

        $mensaje = $servicio->activo ? 'Servicio activado.' : 'Servicio pausado.';

        return back()->with('status', $mensaje);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'duracion_minutos' => ['required', 'integer', 'min:5', 'max:600'],
            'precio' => ['required', 'numeric', 'min:0'],
        ]);
    }

    /**
     * Un admin solo puede tocar servicios de SU PROPIA empresa.
     */
    private function autorizar(Servicio $servicio): void
    {
        abort_if($servicio->empresa_id !== Auth::guard('web')->user()->empresa_id, 403);
    }
}