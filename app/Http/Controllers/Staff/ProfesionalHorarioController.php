<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Profesional;
use App\Models\ProfesionalHorario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfesionalHorarioController extends Controller
{
    private array $dias = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo',
    ];

    public function edit(Profesional $profesional): View
    {
        $this->autorizar($profesional);

        $horarios = $profesional->horarios()->orderBy('dia_semana')->orderBy('hora_inicio')->get();

        return view('staff.profesional-horarios', [
            'profesional' => $profesional,
            'horarios' => $horarios,
            'dias' => $this->dias,
        ]);
    }

    public function store(Request $request, Profesional $profesional): RedirectResponse
    {
        $this->autorizar($profesional);

        $data = $request->validate([
            'dias' => ['required', 'array', 'min:1'],
            'dias.*' => ['integer', 'between:0,6'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ]);

        $creados = [];
        $conflictos = [];

        foreach ($data['dias'] as $dia) {
            $solapa = $profesional->horarios()
                ->where('dia_semana', $dia)
                ->where(function ($q) use ($data) {
                    $q->where('hora_inicio', '<', $data['hora_fin'])
                        ->where('hora_fin', '>', $data['hora_inicio']);
                })
                ->exists();

            if ($solapa) {
                $conflictos[] = $this->dias[$dia];
                continue;
            }

            $profesional->horarios()->create([
                'dia_semana' => $dia,
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
            ]);

            $creados[] = $this->dias[$dia];
        }

        $mensaje = $creados
            ? 'Horario agregado para: ' . implode(', ', $creados) . '.'
            : 'No se agregó ningún horario.';

        if ($conflictos) {
            $mensaje .= ' Se omitió por superposición: ' . implode(', ', $conflictos) . '.';
        }

        return back()->with('status', $mensaje);
    }

    public function destroy(Profesional $profesional, ProfesionalHorario $horario): RedirectResponse
    {
        $this->autorizar($profesional);

        abort_if($horario->profesional_id !== $profesional->id, 403);

        $horario->delete();

        return back()->with('status', 'Horario eliminado.');
    }

    /**
     * El admin puede gestionar horarios de cualquier profesional de su empresa.
     * El operador solo puede gestionar los suyos propios (si está vinculado).
     */
    private function autorizar(Profesional $profesional): void
    {
        $usuario = Auth::guard('web')->user();

        if ($usuario->esAdmin() && $profesional->empresa_id === $usuario->empresa_id) {
            return;
        }

        if ($usuario->esOperador() && $profesional->usuario_id === $usuario->id) {
            return;
        }

        abort(403);
    }
}