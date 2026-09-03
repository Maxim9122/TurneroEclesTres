<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EmpresaBrandingController extends Controller
{
    public function edit(): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        return view('staff.empresa-branding', compact('empresa'));
    }

    public function update(Request $request): RedirectResponse
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $data = $request->validate([
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'imagen_fondo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'color_fondo' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'quitar_imagen_fondo' => ['nullable', 'boolean'],
        ]);

        $carpeta = "empresas/{$empresa->id}";

        // Logo: siempre se guarda como "logo.{ext}", pisando el anterior.
        if ($request->hasFile('logo')) {
            $this->reemplazarArchivo($empresa, 'logo_path', $request->file('logo'), $carpeta, 'logo');
        }

        // Imagen de fondo: siempre se guarda como "fondo.{ext}", pisando la anterior.
        if ($request->hasFile('imagen_fondo')) {
            $this->reemplazarArchivo($empresa, 'imagen_fondo_path', $request->file('imagen_fondo'), $carpeta, 'fondo');
        }

        if ($request->boolean('quitar_imagen_fondo') && $empresa->imagen_fondo_path) {
            Storage::disk('public')->delete($empresa->imagen_fondo_path);
            $empresa->imagen_fondo_path = null;
        }

        if (!empty($data['color_fondo'])) {
            $empresa->color_fondo = $data['color_fondo'];
        }

        $empresa->save();

        return back()->with('status', 'Los cambios se guardaron correctamente.');
    }

    /**
     * Guarda el archivo con un nombre fijo (logo/fondo), pisando cualquier
     * versión anterior -sea cual sea su extensión- para no acumular archivos.
     */
    private function reemplazarArchivo($empresa, string $campo, $archivo, string $carpeta, string $nombreBase): void
    {
        // Borra cualquier archivo previo con ese nombre base, sin importar la extensión
        // (por si antes era .png y ahora suben un .webp, por ejemplo).
        foreach (Storage::disk('public')->files($carpeta) as $existente) {
            if (str_starts_with(basename($existente), $nombreBase . '.')) {
                Storage::disk('public')->delete($existente);
            }
        }

        $extension = $archivo->getClientOriginalExtension();
        $rutaNueva = $archivo->storeAs($carpeta, "{$nombreBase}.{$extension}", 'public');

        $empresa->{$campo} = $rutaNueva;
    }
}