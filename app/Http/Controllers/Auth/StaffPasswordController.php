<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Models\Usuario;
use App\Support\WhatsApp;

class StaffPasswordController extends Controller
{
    public function showLinkRequest(): View
    {
        return view('auth.staff-forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario) {
            return back()->withErrors(['email' => 'No encontramos ningún usuario con ese email.'])->withInput();
        }

        $mensaje = "Hola! Necesito recuperar mi contraseña del panel de EclesTres. Mi email registrado es: {$usuario->email}";

        return redirect()->away(
            WhatsApp::linkChat('3841670079', $mensaje)
        );
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.staff-reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker('usuarios')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($usuario, $password) {
                $usuario->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('staff.login')->with('status', 'Tu contraseña fue actualizada. Ya podés ingresar.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}