<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turno confirmado - {{ $turno->empresa->nombre }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-mate-fondo font-body flex items-center justify-center px-4">
<x-empresa-topbar :empresa="$turno->empresa" back-label="¡Turno confirmado!" />
<div class="max-w-sm w-full bg-mate-superficie border border-mate-borde rounded-lg p-6 text-center">
    <div class="text-4xl mb-3">✅</div>
    <h1 class="font-display text-xl mb-2">¡Turno confirmado!</h1>
    <p class="text-sm text-mate-tinta/70 mb-4">{{ $turno->empresa->nombre }}</p>

    <div class="text-left text-sm space-y-1 bg-white border border-mate-borde rounded-md p-4 mb-6">
        <p><strong>Fecha:</strong> {{ $turno->fecha->format('d/m/Y') }}</p>
        <p><strong>Hora:</strong> {{ substr($turno->hora_inicio, 0, 5) }} a {{ substr($turno->hora_fin, 0, 5) }}</p>
        <p><strong>Servicios:</strong> {{ $turno->servicios->pluck('nombre')->implode(' + ') }}</p>
        <p><strong>Profesional:</strong> {{ $turno->profesional->nombre ?? 'Sin preferencia (a asignar)' }}</p>
        <p><strong>Total:</strong> ${{ number_format($turno->precioTotal(), 2, ',', '.') }}</p>
    </div>

    <a href="{{ route('cliente.home') }}" class="text-sm text-mate-salvia font-medium">Volver a mi cuenta</a>
</div>
<x-footer />
</body>
</html>