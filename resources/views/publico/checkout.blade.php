<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar compra - {{ $empresa->nombre }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-mate-fondo font-body flex flex-col">

<header class="bg-mate-superficie border-b border-mate-borde px-4 py-4">
    <x-empresa-topbar :empresa="$empresa" :back-route="route('publico.carrito.index', $empresa)" back-label="Finalizando compra" />
</header>

<main class="flex-1 max-w-md mx-auto px-4 py-6 w-full" x-data="{ metodo: 'retiro', direccionExistente: '{{ $direcciones->first()->id ?? '' }}' }">
    <h1 class="font-display text-xl mb-6">Finalizar compra</h1>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-mate-superficie border border-mate-borde rounded-md p-4 mb-6 text-sm space-y-1">
        @foreach ($productos as $producto)
            <div class="flex justify-between">
                <span>{{ $items[$producto->id] }} x {{ $producto->nombre }}</span>
                <span>${{ number_format($producto->precio * $items[$producto->id], 2, ',', '.') }}</span>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('publico.pedido.confirmar', $empresa) }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm mb-2">¿Cómo lo recibís?</label>
            <div class="flex gap-3">
                <label class="flex-1 flex items-center gap-2 border border-mate-borde rounded-md px-3 py-2 text-sm cursor-pointer"
                    :class="metodo === 'retiro' ? 'ring-2 ring-mate-salvia' : ''">
                    <input type="radio" name="metodo_entrega" value="retiro" x-model="metodo">
                    Retiro en el local
                </label>
                <label class="flex-1 flex items-center gap-2 border border-mate-borde rounded-md px-3 py-2 text-sm cursor-pointer"
                    :class="metodo === 'envio' ? 'ring-2 ring-mate-salvia' : ''">
                    <input type="radio" name="metodo_entrega" value="envio" x-model="metodo">
                    Envío a domicilio
                </label>
            </div>
        </div>

        <div x-show="metodo === 'envio'" x-cloak class="space-y-3">
            @if ($direcciones->isNotEmpty())
                <div>
                    <label class="block text-sm mb-1">Dirección</label>
                    <select name="direccion_envio_id" x-model="direccionExistente"
                        class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                        @foreach ($direcciones as $direccion)
                            <option value="{{ $direccion->id }}">
                                {{ $direccion->calle }} {{ $direccion->altura }}, {{ $direccion->barrio }}
                            </option>
                        @endforeach
                        <option value="">Usar una dirección nueva</option>
                    </select>
                </div>
            @endif

            <div x-show="!direccionExistente || {{ $direcciones->isEmpty() ? 'true' : 'false' }}" x-cloak class="space-y-3">
                <div>
                    <label class="block text-sm mb-1">Barrio</label>
                    <input type="text" name="nueva_barrio" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-sm mb-1">Calle</label>
                        <input type="text" name="nueva_calle" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Altura</label>
                        <input type="text" name="nueva_altura" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                    </div>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm mb-1">Notas para el negocio (opcional)</label>
            <textarea name="notas" rows="2" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm"></textarea>
        </div>

        <p class="text-xs text-mate-tinta/50">
            El pago se coordina directamente con el negocio al momento de retirar o recibir tu pedido.
        </p>

        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-3 text-sm font-medium">
            Confirmar pedido
        </button>
    </form>
</main>

<x-footer />
</body>
</html>