<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\ServicioImagen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ServicioController extends Controller
{
    public function index(): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $servicios = $empresa->servicios()->with('imagenes')->orderBy('nombre')->get();

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

        $servicio = $empresa->servicios()->create($data);

        if ($request->hasFile('foto')) {
            $this->guardarFotoPrincipal($servicio, $request);
        }

        $this->guardarImagenesAdicionales($servicio, $request);

        return redirect()->route('staff.empresa.servicios.index')
            ->with('status', 'Servicio creado correctamente.');
    }

    public function edit(Servicio $servicio): View
    {
        $this->autorizar($servicio);

        $servicio->load('imagenes');

        return view('staff.servicios-edit', compact('servicio'));
    }

    public function update(Request $request, Servicio $servicio): RedirectResponse
    {
        $this->autorizar($servicio);

        $data = $this->validarDatos($request);

        $servicio->update($data);

        if ($request->hasFile('foto')) {
            $this->guardarFotoPrincipal($servicio, $request);
        }

        $this->guardarImagenesAdicionales($servicio, $request);

        return redirect()->route('staff.empresa.servicios.index')
            ->with('status', 'Servicio actualizado correctamente.');
    }

    public function eliminarImagen(ServicioImagen $imagen): RedirectResponse
    {
        $this->autorizar($imagen->servicio);

        Storage::disk('public')->delete($imagen->path);
        $imagen->delete();

        return back()->with('status', 'Imagen eliminada.');
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
            'dias_renovacion' => ['nullable', 'integer', 'min:1', 'max:365'],
            'precio' => ['required', 'numeric', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'imagenes_adicionales.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    private function guardarFotoPrincipal(Servicio $servicio, Request $request): void
    {
        $carpeta = "servicios/{$servicio->id}";

        foreach (Storage::disk('public')->files($carpeta) as $existente) {
            if (str_starts_with(basename($existente), 'foto.')) {
                Storage::disk('public')->delete($existente);
            }
        }

        $extension = $request->file('foto')->getClientOriginalExtension();
        $servicio->foto_path = $request->file('foto')->storeAs($carpeta, "foto.{$extension}", 'public');
        $servicio->save();
    }

    private function guardarImagenesAdicionales(Servicio $servicio, Request $request): void
    {
        if (!$request->hasFile('imagenes_adicionales')) {
            return;
        }

        $carpeta = "servicios/{$servicio->id}";
        $yaExistentes = $servicio->imagenes()->count() + ($servicio->foto_path ? 1 : 0);
        $espacioDisponible = max(0, 3 - $yaExistentes);

        foreach (array_slice($request->file('imagenes_adicionales'), 0, $espacioDisponible) as $archivo) {
            $nombre = uniqid('img_') . '.' . $archivo->getClientOriginalExtension();
            $ruta = $archivo->storeAs($carpeta, $nombre, 'public');

            $servicio->imagenes()->create([
                'path' => $ruta,
                'orden' => $servicio->imagenes()->count(),
            ]);
        }
    }

    private function autorizar(Servicio $servicio): void
    {
        abort_if($servicio->empresa_id !== Auth::guard('web')->user()->empresa_id, 403);
    }
}