<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\PushSubscription;
use App\Models\Turno;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Envía notificaciones push (PWA) a los dispositivos del staff de una empresa.
 *
 * Nunca lanza excepciones: si algo falla se registra en el log y el flujo que lo
 * llamó (reserva, compra) sigue igual. Se invoca con defer() para que corra
 * después de enviar la respuesta al cliente.
 */
class PushService
{
    public function configurado(): bool
    {
        return filled(config('services.webpush.public_key')) && filled(config('services.webpush.private_key'));
    }

    public function nuevoTurno(Turno $turno): void
    {
        $turno->loadMissing(['cliente', 'servicios']);

        $cuerpo = collect([
            $turno->cliente?->nombre,
            $turno->servicios->pluck('nombre')->join(', '),
            $turno->fecha->format('d/m') . ' ' . substr((string) $turno->hora_inicio, 0, 5) . ' hs',
        ])->filter()->join(' · ');

        $this->enviarAEmpresa($turno->empresa_id, [
            'title' => '📅 Nuevo turno #' . $turno->id,
            'body' => $cuerpo,
            'tag' => 'turno-' . $turno->id,
            'url' => route('staff.empresa.turnos.index', [
                'fecha' => $turno->fecha->toDateString(),
                'destacar' => $turno->id,
            ], false),
        ]);
    }

    public function nuevoPedido(Pedido $pedido): void
    {
        $pedido->loadMissing('cliente');

        $cuerpo = collect([
            $pedido->cliente?->nombre,
            '$' . number_format((float) $pedido->total, 0, ',', '.'),
            $pedido->metodo_entrega === 'envio' ? 'Envío' : 'Retiro',
        ])->filter()->join(' · ');

        $this->enviarAEmpresa($pedido->empresa_id, [
            'title' => '🛒 Nuevo pedido #' . $pedido->id,
            'body' => $cuerpo,
            'tag' => 'pedido-' . $pedido->id,
            'url' => route('staff.empresa.pedidos.index', ['destacar' => $pedido->id], false),
        ]);
    }

    /**
     * Aviso de prueba a los dispositivos de un solo usuario. Devuelve cuántos se enviaron bien.
     */
    public function prueba(int $usuarioId): int
    {
        $suscripciones = PushSubscription::where('usuario_id', $usuarioId)->get();

        return $this->enviar($suscripciones, [
            'title' => '🔔 Avisos activados',
            'body' => 'Así vas a ver los turnos y pedidos nuevos en este dispositivo.',
            'tag' => 'prueba',
            'url' => route('staff.empresa.dashboard', [], false),
        ]);
    }

    private function enviarAEmpresa(int $empresaId, array $payload): void
    {
        $suscripciones = PushSubscription::whereHas('usuario', fn ($q) => $q
            ->where('empresa_id', $empresaId)
            ->where('activo', true)
            ->whereIn('rol', ['admin', 'operador'])
        )->get();

        $this->enviar($suscripciones, $payload);
    }

    private function enviar(Collection $suscripciones, array $payload): int
    {
        if ($suscripciones->isEmpty() || !$this->configurado()) {
            return 0;
        }

        $enviados = 0;

        try {
            $webPush = new WebPush(
                ['VAPID' => [
                    'subject' => config('services.webpush.subject'),
                    'publicKey' => config('services.webpush.public_key'),
                    'privateKey' => config('services.webpush.private_key'),
                ]],
                ['TTL' => 3600, 'urgency' => 'high'],
                new Client(['timeout' => 5, 'connect_timeout' => 3]),
            );

            $json = json_encode($payload + ['icon' => '/icons/icon-192.png'], JSON_UNESCAPED_UNICODE);

            foreach ($suscripciones as $s) {
                $webPush->queueNotification(new Subscription($s->endpoint, $s->public_key, $s->auth_token, $s->content_encoding), $json);
            }

            foreach ($webPush->flush() as $reporte) {
                if ($reporte->isSuccess()) {
                    $enviados++;
                } elseif ($reporte->isSubscriptionExpired()) {
                    // El dispositivo ya no existe o revocó el permiso: se limpia solo.
                    PushSubscription::where('endpoint_hash', PushSubscription::hashEndpoint($reporte->getEndpoint()))->delete();
                } else {
                    Log::warning('Push no enviado: ' . $reporte->getReason());
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error enviando push: ' . $e->getMessage());
        }

        return $enviados;
    }
}
