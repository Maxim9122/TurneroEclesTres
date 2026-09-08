<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido confirmado - {{ $pedido->empresa->nombre }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-mate-fondo font-body flex flex-col items-center justify-center px-4">

<div class="max-w-sm w-full bg-mate-superficie border border-mate-borde rounded-lg p-6 text-center">
    <div class="text-4xl mb-3">✅</div>
    <h1 class="font-display text-xl mb-2">¡Pedido realizado!</h1>
    <p class="text-sm text-mate-tinta/70 mb-4">{{ $pedido->empresa->nombre }}</p>

    <div class="text-left text-sm space-y-1 bg-white border border-mate-borde rounded-md p-4 mb-4">
        @foreach ($pedido->items as $item)
            <div class="flex justify-between">
                <span>{{ $item->cantidad }} x {{ $item->producto->nombre }}</span>
                <span>${{ number_format($item->subtotal(), 2, ',', '.') }}</span>
            </div>
        @endforeach
        <hr class="border-mate-borde my-2">
        <div class="flex justify-between font-medium">
            <span>Total</span>
            <span>${{ number_format($pedido->total, 2, ',', '.') }}</span>
        </div>
    </div>

    <div class="text-left text-sm bg-white border border-mate-borde rounded-md p-4 mb-6">
        <p><strong>Entrega:</strong> {{ $pedido->metodo_entrega === 'retiro' ? 'Retiro en el local' : 'Envío a domicilio' }}</p>
        @if ($pedido->direccionEnvio)
            <p><strong>Dirección:</strong> {{ $pedido->direccionEnvio->calle }} {{ $pedido->direccionEnvio->altura }}, {{ $pedido->direccionEnvio->barrio }}</p>
        @endif
    </div>

    <a href="{{ route('cliente.home') }}" class="text-sm text-mate-salvia font-medium">Volver a mi cuenta</a>
</div>

<x-footer />
</body>
</html>