<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - {{ $empresa->nombre }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-mate-fondo font-body flex flex-col">

<header class="bg-mate-superficie border-b border-mate-borde px-4 py-4">
    <x-empresa-topbar :empresa="$empresa" :back-route="route('publico.empresa', $empresa)" back-label="Tu carrito" />
</header>

<main class="flex-1 max-w-md mx-auto px-4 py-6 w-full">
    <h1 class="font-display text-xl mb-6">Tu carrito</h1>

    @if ($lineas->isEmpty())
        <p class="text-sm text-mate-tinta/60">Tu carrito está vacío.</p>
    @else
        <div class="space-y-3 mb-6">
            @foreach ($lineas as $linea)
                <div class="bg-mate-superficie border border-mate-borde rounded-md p-3 flex items-center gap-3">
                    @if ($linea['producto']->foto_path)
                        <img src="{{ asset('storage/' . $linea['producto']->foto_path) }}" class="w-12 h-12 rounded-md object-cover">
                    @else
                        <div class="w-12 h-12 rounded-md bg-mate-borde"></div>
                    @endif

                    <div class="flex-1">
                        <p class="text-sm font-medium">{{ $linea['producto']->nombre }}</p>
                        <p class="text-xs text-mate-tinta/60">
                            ${{ number_format($linea['producto']->precio, 2, ',', '.') }} c/u
                        </p>
                    </div>

                    <form method="POST" action="{{ route('publico.carrito.actualizar', [$empresa, $linea['producto']]) }}" class="flex items-center gap-1">
                        @csrf
                        <input type="number" name="cantidad" value="{{ $linea['cantidad'] }}" min="0" max="{{ $linea['producto']->stock }}"
                            onchange="this.form.submit()"
                            class="w-14 text-sm rounded-md border border-mate-borde px-2 py-1">
                    </form>

                    <form method="POST" action="{{ route('publico.carrito.quitar', [$empresa, $linea['producto']]) }}">
                        @csrf
                        <button type="submit" class="text-xs text-red-700 underline">Quitar</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between text-sm font-medium mb-6">
            <span>Total</span>
            <span>${{ number_format($total, 2, ',', '.') }}</span>
        </div>

        <a href="{{ route('publico.pedido.iniciar', $empresa) }}"
            class="block text-center bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-3 text-sm font-medium">
            Continuar compra
        </a>
    @endif
</main>

<x-footer />
</body>
</html>