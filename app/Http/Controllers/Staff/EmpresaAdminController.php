<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmpresaAdminController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->query('estado');

        $empresas = Empresa::query()
            ->when($estado, fn ($query) => $query->where('estado', $estado))
            ->orderByRaw("FIELD(estado, 'pendiente', 'activa', 'suspendida', 'rechazada', 'cancelada')")
            ->latest()
            ->get();

        return view('staff.empresas-index', compact('empresas', 'estado'));
    }

    public function activar(Empresa $empresa): RedirectResponse
    {
        $empresa->update(['estado' => 'activa']);

        return back()->with('status', "Empresa \"{$empresa->nombre}\" activada.");
    }

    public function rechazar(Empresa $empresa): RedirectResponse
    {
        $empresa->update(['estado' => 'rechazada']);

        return back()->with('status', "Empresa \"{$empresa->nombre}\" rechazada.");
    }

    public function suspender(Empresa $empresa): RedirectResponse
    {
        $empresa->update(['estado' => 'suspendida']);

        return back()->with('status', "Empresa \"{$empresa->nombre}\" suspendida.");
    }

    public function reactivar(Empresa $empresa): RedirectResponse
    {
        $empresa->update(['estado' => 'activa']);

        return back()->with('status', "Empresa \"{$empresa->nombre}\" reactivada.");
    }
}