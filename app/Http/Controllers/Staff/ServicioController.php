<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\Imagenes;
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

        $servicios = $empresa->servicios()->with(['imagenes', 'adicionales'])->orderBy('nombre')->get();

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

        $this->guardarAdicionales($servicio, $request->input('adicionales', []));

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

        $servicio->load(['imagenes', 'adicionales']);

        return view('staff.servicios-edit', compact('servicio'));
    }

    public function update(Request $request, Servicio $servicio): RedirectResponse
    {
        $this->autorizar($servicio);

        $data = $this->validarDatos($request);

        $servicio->update($data);

        $this->guardarAdicionales($servicio, $request->input('adicionales', []));

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
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'imagenes_adicionales.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'adicionales' => ['nullable', 'array', 'max:20'],
            'adicionales.*.id' => ['nullable', 'integer'],
            'adicionales.*.nombre' => ['required', 'string', 'max:100'],
            'adicionales.*.precio' => ['required', 'numeric', 'min:0'],
            'adicionales.*.duracion_minutos' => ['nullable', 'integer', 'min:0', 'max:240'],
        ], [], [
            'adicionales.*.nombre' => 'nombre del adicional',
            'adicionales.*.precio' => 'precio del adicional',
            'adicionales.*.duracion_minutos' => 'minutos extra del adicional',
        ]);
    }

    /**
     * Sincroniza la lista de adicionales del formulario: actualiza los existentes, crea los nuevos y
     * borra los que se quitaron. Los turnos ya reservados guardan su propia copia, así que no cambian.
     */
    private function guardarAdicionales(Servicio $servicio, array $filas): void
    {
        $conservar = [];

        foreach ($filas as $fila) {
            $datos = [
                'nombre' => trim($fila['nombre']),
                'precio' => $fila['precio'],
                'duracion_minutos' => (int) ($fila['duracion_minutos'] ?? 0),
                'activo' => true,
            ];

            // Solo se actualiza un adicional que sea de ESTE servicio; un id ajeno se trata como nuevo.
            $existente = !empty($fila['id']) ? $servicio->adicionales()->find($fila['id']) : null;

            if ($existente) {
                $existente->update($datos);
                $conservar[] = $existente->id;
            } else {
                $conservar[] = $servicio->adicionales()->create($datos)->id;
            }
        }

        $servicio->adicionales()->whereNotIn('id', $conservar)->delete();
    }

    private function guardarFotoPrincipal(Servicio $servicio, Request $request): void
    {
        $servicio->foto_path = Imagenes::guardar($request->file('foto'), "servicios/{$servicio->id}", 'foto', Imagenes::FOTO);
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
            $ruta = Imagenes::guardar($archivo, $carpeta, uniqid('img_'), Imagenes::FOTO, reemplazar: false);

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