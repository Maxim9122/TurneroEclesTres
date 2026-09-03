<!DOCTYPE html>
<html lang="es" x-data="{ menuAbierto: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'EclesTres' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-mate-fondo font-body">

    <header class="bg-mate-superficie border-b border-mate-borde">
        <div class="flex items-center justify-between px-4 py-3 sm:px-6">
            <span class="font-display text-xl text-mate-tinta">EclesTres</span>

            <button @click="menuAbierto = !menuAbierto" class="sm:hidden p-2" aria-label="Abrir menú">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-mate-tinta" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <nav class="hidden sm:flex items-center gap-6 text-sm">
                {{ $nav ?? '' }}
                <form method="POST" action="{{ $logoutRoute ?? '' }}">
                    @csrf
                    <button type="submit" class="text-mate-tinta/70 hover:text-mate-tinta">Cerrar sesión</button>
                </form>
            </nav>
        </div>

        <nav x-show="menuAbierto" x-cloak class="sm:hidden flex flex-col gap-1 px-4 pb-4 text-sm border-t border-mate-borde pt-3">
            {{ $nav ?? '' }}
            <form method="POST" action="{{ $logoutRoute ?? '' }}">
                @csrf
                <button type="submit" class="text-mate-tinta/70 hover:text-mate-tinta py-2">Cerrar sesión</button>
            </form>
        </nav>
    </header>

    <main class="px-4 py-6 sm:px-6 max-w-5xl mx-auto">
        {{ $slot }}
    </main>

</body>
</html>