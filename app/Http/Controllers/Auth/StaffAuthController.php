<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StaffAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.staff-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::guard('web')->attempt($credentials, $request->boolean('recordar'))) {
            throw ValidationException::withMessages([
                'email' => 'Los datos ingresados no coinciden con ningún registro.',
            ]);
        }

        $request->session()->regenerate();

        $usuario = Auth::guard('web')->user();

        if (!$usuario->activo) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages([
                'email' => 'Tu usuario está inactivo. Contactá al administrador de tu empresa.',
            ]);
        }

        return $usuario->esSuperAdmin()
            ? redirect()->route('staff.plataforma.dashboard')
            : redirect()->route('staff.empresa.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}