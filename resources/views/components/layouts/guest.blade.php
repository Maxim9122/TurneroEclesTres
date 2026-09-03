<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'EclesTres' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <span class="font-display text-3xl text-mate-tinta">EclesTres</span>
        </div>

        <div class="bg-mate-superficie border border-mate-borde rounded-lg p-6 sm:p-8 shadow-sm">
            {{ $slot }}
        </div>
    </div>
</body>
</html>