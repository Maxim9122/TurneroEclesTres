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
        $empresa = Auth::guard('web')->user()->empresa;

        // Operadores de esta empresa que todavía no están vinculados a ningún profesional
        $operadoresDisponibles = $empresa->usuarios()
            ->where('rol', 'operador')
            ->whereDoesntHave('profesional')
            ->orderBy('nombre')
            ->get();

        return view('staff.profesionales-create', compact('operadoresDisponibles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $data = $this->validarDatos($request, $empresa->id);

        $profesional = $empresa->profesionales()->create([
            'nombre' => $data['nombre'],
            'usuario_id' => $data['usuario_id'] ?? null,
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

        $empresa = $profesional->empresa;

        $operadoresDisponibles = $empresa->usuarios()
            ->where('rol', 'operador')
            ->where(function ($q) use ($profesional) {
                $q->whereDoesntHave('profesional')
                    ->orWhereHas('profesional', fn ($sub) => $sub->where('profesionales.id', $profesional->id));
            })
            ->orderBy('nombre')
            ->get();

        return view('staff.profesionales-edit', compact('profesional', 'operadoresDisponibles'));
    }

    public function update(Request $request, Profesional $profesional): RedirectResponse
    {
        $this->autorizarAdmin($profesional);

        $data = $this->validarDatos($request, $profesional->empresa_id, $profesional->id);

        $profesional->update([
            'nombre' => $data['nombre'],
            'usuario_id' => $data['usuario_id'] ?? null,
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

    private function validarDatos(Request $request, int $empresaId, ?int $profesionalIdActual = null): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'usuario_id' => ['nullable', 'exists:usuarios,id'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'porcentaje_comision' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        // Si eligió un operador, confirmamos que sea de la misma empresa
        // y que no esté ya vinculado a OTRO profesional.
        if (!empty($data['usuario_id'])) {
            $usuario = \App\Models\Usuario::where('id', $data['usuario_id'])
                ->where('empresa_id', $empresaId)
                ->where('rol', 'operador')
                ->first();

            abort_if(!$usuario, 422, 'El operador seleccionado no es válido.');

            $yaVinculado = \App\Models\Profesional::where('usuario_id', $usuario->id)
                ->when($profesionalIdActual, fn ($q) => $q->where('id', '!=', $profesionalIdActual))
                ->exists();

            abort_if($yaVinculado, 422, 'Ese operador ya está vinculado a otro profesional.');
        }

        return $data;
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