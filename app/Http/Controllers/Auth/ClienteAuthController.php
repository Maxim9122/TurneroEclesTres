<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Rules\Recaptcha;

class ClienteAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.cliente-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::guard('cliente')->attempt($credentials, $request->boolean('recordar'))) {
            throw ValidationException::withMessages([
                'email' => 'Los datos ingresados no coinciden con ningún registro.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('cliente.home'));
    }

    public function showRegister()
    {
        return view('auth.cliente-register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:clientes,email'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'g-recaptcha-response' => [new Recaptcha()],
        ]);

        $cliente = Cliente::create([
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'telefono' => $data['telefono'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        Auth::guard('cliente')->login($cliente, true);

        return redirect()->route('cliente.home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('cliente')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('cliente.login');
    }
}