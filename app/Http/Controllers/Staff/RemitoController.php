<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Turno;
use App\Support\WhatsApp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class RemitoController extends Controller
{
    /**
     * Genera un link firmado y temporal al PDF, y redirige a WhatsApp con ese link.
     */
    public function enviarPedido(Pedido $pedido)
    {
        abort_if($pedido->empresa_id !== Auth::guard('web')->user()->empresa_id, 403);

        $url = URL::temporarySignedRoute('remitos.pedido', now()->addDays(30), ['pedido' => $pedido->id]);

        $mensaje = "Acá tenés tu remito de compra en {$pedido->empresa->nombre}:\n{$url}";

        $link = WhatsApp::linkChat($pedido->cliente->telefono, $mensaje);

        if (!$link) {
            return back()->with('status', "El cliente no tiene teléfono cargado. Link del remito: {$url}");
        }

        return redirect()->away($link);
    }

    public function enviarTurno(Turno $turno)
    {
        abort_if($turno->empresa_id !== Auth::guard('web')->user()->empresa_id, 403);

        $url = URL::temporarySignedRoute('remitos.turno', now()->addDays(30), ['turno' => $turno->id]);

        $mensaje = "Acá tenés tu comprobante de turno en {$turno->empresa->nombre}:\n{$url}";

        $link = WhatsApp::linkChat($turno->cliente->telefono, $mensaje);

        if (!$link) {
            return back()->with('status', "El cliente no tiene teléfono cargado. Link del comprobante: {$url}");
        }

        return redirect()->away($link);
    }

    /**
     * Muestra/descarga el PDF en sí. Accesible sin login porque el link viene firmado
     * (nadie puede adivinar la URL de otro pedido/turno sin la firma correcta).
     */
    public function verPedido(Pedido $pedido)
    {
        $pedido->load(['empresa', 'cliente', 'items.producto', 'direccionEnvio']);

        $pdf = Pdf::loadView('pdf.remito-pedido', compact('pedido'));

        return $pdf->stream("remito-pedido-{$pedido->id}.pdf");
    }

    public function verTurno(Turno $turno)
    {
        $turno->load(['empresa', 'cliente', 'profesional', 'servicios']);

        $pdf = Pdf::loadView('pdf.remito-turno', compact('turno'));

        return $pdf->stream("comprobante-turno-{$turno->id}.pdf");
    }
}