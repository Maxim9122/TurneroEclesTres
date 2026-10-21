<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\Imagenes;
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

        // Logo y fondo: se guardan optimizados (WebP, achicados) y reemplazan la versión anterior.
        if ($request->hasFile('logo')) {
            $empresa->logo_path = Imagenes::guardar($request->file('logo'), $carpeta, 'logo', Imagenes::LOGO);
        }

        if ($request->hasFile('imagen_fondo')) {
            $empresa->imagen_fondo_path = Imagenes::guardar($request->file('imagen_fondo'), $carpeta, 'fondo', Imagenes::FONDO);
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
}