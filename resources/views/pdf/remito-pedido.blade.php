<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #2E2B26; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .muted { color: #777; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 6px 4px; border-bottom: 1px solid #ddd; }
        th { background: #EDE7DD; }
        .total { font-weight: bold; font-size: 14px; }
        .header { border-bottom: 2px solid #6B7360; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $pedido->empresa->nombre }}</h1>
        <p class="muted">Remito de compra #{{ $pedido->id }} · {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <p><strong>Cliente:</strong> {{ $pedido->cliente->nombre }}</p>
    <p><strong>Entrega:</strong> {{ $pedido->metodo_entrega === 'retiro' ? 'Retiro en el local' : 'Envío a domicilio' }}</p>
    @if ($pedido->direccionEnvio)
        <p><strong>Dirección:</strong> {{ $pedido->direccionEnvio->calle }} {{ $pedido->direccionEnvio->altura }}, {{ $pedido->direccionEnvio->barrio }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pedido->items as $item)
                <tr>
                    <td>{{ $item->producto->nombre }}</td>
                    <td>{{ $item->cantidad }}</td>
                    <td>${{ number_format($item->precio_al_momento, 2, ',', '.') }}</td>
                    <td>${{ number_format($item->subtotal(), 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total" style="text-align: right; margin-top: 15px;">
        Total: ${{ number_format($pedido->total, 2, ',', '.') }}
    </p>

    <p class="muted" style="margin-top: 40px;">Generado por EclesTres</p>
</body>
</html>