<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MiPerfilController extends Controller
{
    public function edit(): View
    {
        return view('cliente.mi-perfil');
    }

    public function update(Request $request): RedirectResponse
    {
        $cliente = Auth::guard('cliente')->user();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:clientes,email,' . $cliente->id],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $cliente->update($data);

        return back()->with('status', 'Tus datos fueron actualizados.');
    }

    public function actualizarPassword(Request $request): RedirectResponse
    {
        $cliente = Auth::guard('cliente')->user();

        $data = $request->validate([
            'password_actual' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['password_actual'], $cliente->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $cliente->update(['password' => $data['password']]);

        return back()->with('status', 'Tu contraseña fue actualizada.');
    }
}