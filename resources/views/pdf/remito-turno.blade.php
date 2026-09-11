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
        <h1>{{ $turno->empresa->nombre }}</h1>
        <p class="muted">Comprobante de turno #{{ $turno->id }} · {{ $turno->fecha->format('d/m/Y') }} {{ substr($turno->hora_inicio, 0, 5) }}hs</p>
    </div>

    <p><strong>Cliente:</strong> {{ $turno->cliente->nombre }}</p>
    <p><strong>Profesional:</strong> {{ $turno->profesional->nombre ?? 'No asignado' }}</p>

    <table>
        <thead>
            <tr>
                <th>Servicio</th>
                <th>Duración</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($turno->servicios as $servicio)
                <tr>
                    <td>{{ $servicio->nombre }}</td>
                    <td>{{ $servicio->pivot->duracion_al_momento }} min</td>
                    <td>${{ number_format($servicio->pivot->precio_al_momento, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total" style="text-align: right; margin-top: 15px;">
        Total: ${{ number_format($turno->precioTotal(), 2, ',', '.') }}
    </p>

    <p class="muted" style="margin-top: 40px;">Generado por EclesTres</p>
</body>
</html>