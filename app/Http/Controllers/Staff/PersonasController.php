<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PersonasController extends Controller
{
    public function clientes(Request $request): View
    {
        $q = $request->query('q');

        $clientes = Cliente::query()
            ->when($q, fn ($query) => $query->where('email', 'like', "%{$q}%")->orWhere('nombre', 'like', "%{$q}%"))
            ->orderBy('nombre')
            ->get();

        return view('staff.personas-clientes', compact('clientes', 'q'));
    }

    public function staff(Request $request): View
    {
        $q = $request->query('q');

        $usuarios = Usuario::with('empresa')
            ->when($q, fn ($query) => $query->where('email', 'like', "%{$q}%")->orWhere('nombre', 'like', "%{$q}%"))
            ->orderBy('nombre')
            ->get();

        return view('staff.personas-staff', compact('usuarios', 'q'));
    }

    public function generarLinkCliente(Cliente $cliente)
    {
        $token = Password::broker('clientes')->createToken($cliente);
        $url = route('cliente.password.reset', ['token' => $token, 'email' => $cliente->email]);

        $mensaje = "Restablecé tu contraseña de EclesTres acá:\n{$url}";

        $link = WhatsApp::linkChat($cliente->telefono, $mensaje);

        if (!$link) {
            return back()->with('status', "Este cliente no tiene teléfono cargado. Link generado: {$url}");
        }

        return redirect()->away($link);
    }

    public function generarLinkStaff(Usuario $usuario)
    {
        $token = Password::broker('usuarios')->createToken($usuario);
        $url = route('staff.password.reset', ['token' => $token, 'email' => $usuario->email]);

        $mensaje = "Restablecé tu contraseña de EclesTres acá:\n{$url}";

        $link = WhatsApp::linkChat($usuario->telefono, $mensaje);

        if (!$link) {
            return back()->with('status', "Este usuario no tiene teléfono cargado. Link generado: {$url}");
        }

        return redirect()->away($link);
    }
}