<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OperadorController extends Controller
{
    public function index(): View
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $operadores = $empresa->usuarios()
            ->where('rol', 'operador')
            ->orderBy('nombre')
            ->get();

        return view('staff.operadores-index', compact('operadores'));
    }

    public function create(): View
    {
        return view('staff.operadores-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $empresa = Auth::guard('web')->user()->empresa;

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $empresa->usuarios()->create([
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'rol' => 'operador',
            'activo' => true,
        ]);

        return redirect()->route('staff.empresa.operadores.index')
            ->with('status', 'Operador creado correctamente.');
    }

    public function alternarEstado(Usuario $usuario): RedirectResponse
    {
        $empresaActual = Auth::guard('web')->user()->empresa_id;

        // Un admin solo puede tocar operadores de SU PROPIA empresa,
        // nunca de otra (aunque adivine el ID en la URL).
        abort_if($usuario->empresa_id !== $empresaActual || $usuario->rol !== 'operador', 403);

        $usuario->update(['activo' => !$usuario->activo]);

        $mensaje = $usuario->activo ? 'Operador activado.' : 'Operador desactivado.';

        return back()->with('status', $mensaje);
    }
}