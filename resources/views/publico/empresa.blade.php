<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $empresa->nombre }} - EclesTres</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-mate-fondo font-body">

    <div style="{{ $empresa->fondoCss() }}">
        <div class="bg-black/15 px-5 py-12 flex flex-col items-center text-center gap-3">
            @if ($empresa->logo_path)
                <img src="{{ asset('storage/' . $empresa->logo_path) }}"
                    class="w-20 h-20 rounded-full object-cover bg-white shadow">
            @endif
            <h1 class="font-display text-2xl text-white drop-shadow">{{ $empresa->nombre }}</h1>
            <p class="text-sm text-white/90">{{ ucfirst($empresa->rubro) }}</p>
        </div>
    </div>

    <main class="max-w-md mx-auto px-4 py-6">
        @if ($empresa->descripcion)
            <p class="text-sm text-mate-tinta/80 mb-6">{{ $empresa->descripcion }}</p>
        @endif

        <div class="grid grid-cols-2 gap-3">
            <a href="#"
                class="text-center bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-3 text-sm font-medium">
                Sacar turno
            </a>
            <a href="#"
                class="text-center border border-mate-borde rounded-md py-3 text-sm font-medium">
                Ver productos
            </a>
        </div>

        <p class="text-xs text-mate-tinta/50 text-center mt-8">
            <a href="{{ route('home') }}" class="underline">EclesTres</a>
        </p>
    </main>

</body>
</html>