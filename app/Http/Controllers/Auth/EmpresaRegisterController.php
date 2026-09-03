<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmpresaRegisterController extends Controller
{
    public function showRegister()
    {
        return view('auth.empresa-register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'empresa_nombre' => ['required', 'string', 'max:255'],
            'rubro' => ['required', 'in:peluqueria,barberia,estetica,unas'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'admin_nombre' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $usuario = DB::transaction(function () use ($data) {
            $empresa = Empresa::create([
                'nombre' => $data['empresa_nombre'],
                'rubro' => $data['rubro'],
                'telefono' => $data['telefono'] ?? null,
                'estado' => 'pendiente',
            ]);

            return Usuario::create([
                'empresa_id' => $empresa->id,
                'nombre' => $data['admin_nombre'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['password']),
                'rol' => 'admin',
                'activo' => true,
            ]);
        });

        Auth::guard('web')->login($usuario);

        return redirect()->route('staff.empresa.dashboard');
    }
}