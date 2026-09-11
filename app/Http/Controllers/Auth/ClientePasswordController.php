<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use App\Models\Cliente;
use App\Support\WhatsApp;

class ClientePasswordController extends Controller
{
    public function showLinkRequest(): View
    {
        return view('auth.cliente-forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $cliente = Cliente::where('email', $request->email)->first();

        if (!$cliente) {
            return back()->withErrors(['email' => 'No encontramos ningún usuario con ese email.'])->withInput();
        }

        $mensaje = "Hola! Necesito recuperar mi contraseña de mi cuenta de cliente en EclesTres. Mi email registrado es: {$cliente->email}";

        return redirect()->away(
            WhatsApp::linkChat('3841670079', $mensaje)
        );
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.cliente-reset-password', [
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

        $status = Password::broker('clientes')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($cliente, $password) {
                $cliente->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('cliente.login')->with('status', 'Tu contraseña fue actualizada. Ya podés ingresar.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}