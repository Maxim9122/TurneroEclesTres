<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Profesional;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfesionalController extends Controller
{
    public function index(): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $profesionales = $empresa->profesionales()->orderBy('nombre')->get();

        return view('staff.profesionales-index', compact('profesionales'));
    }

    public function create(): View
    {
        return view('staff.profesionales-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $data = $this->validarDatos($request);

        $profesional = $empresa->profesionales()->create([
            'nombre' => $data['nombre'],
            'porcentaje_comision' => $data['porcentaje_comision'],
            'activo' => true,
        ]);

        if ($request->hasFile('foto')) {
            $this->guardarFoto($profesional, $request);
        }

        return redirect()->route('staff.empresa.profesionales.index')
            ->with('status', 'Profesional creado correctamente.');
    }

    public function edit(Profesional $profesional): View
    {
        $this->autorizarAdmin($profesional);

        return view('staff.profesionales-edit', compact('profesional'));
    }

    public function update(Request $request, Profesional $profesional): RedirectResponse
    {
        $this->autorizarAdmin($profesional);

        $data = $this->validarDatos($request);

        $profesional->update([
            'nombre' => $data['nombre'],
            'porcentaje_comision' => $data['porcentaje_comision'],
        ]);

        if ($request->hasFile('foto')) {
            $this->guardarFoto($profesional, $request);
        }

        return redirect()->route('staff.empresa.profesionales.index')
            ->with('status', 'Profesional actualizado correctamente.');
    }

    public function alternarEstado(Profesional $profesional): RedirectResponse
    {
        $this->autorizarAdmin($profesional);

        $profesional->update(['activo' => !$profesional->activo]);

        $mensaje = $profesional->activo ? 'Profesional activado.' : 'Profesional dado de baja.';

        return back()->with('status', $mensaje);
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'porcentaje_comision' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    private function guardarFoto(Profesional $profesional, Request $request): void
    {
        $carpeta = "profesionales/{$profesional->id}";

        foreach (Storage::disk('public')->files($carpeta) as $existente) {
            if (str_starts_with(basename($existente), 'foto.')) {
                Storage::disk('public')->delete($existente);
            }
        }

        $extension = $request->file('foto')->getClientOriginalExtension();
        $profesional->foto_path = $request->file('foto')->storeAs($carpeta, "foto.{$extension}", 'public');
        $profesional->save();
    }

    private function autorizarAdmin(Profesional $profesional): void
    {
        abort_if($profesional->empresa_id !== Auth::guard('web')->user()->empresa_id, 403);
    }
}