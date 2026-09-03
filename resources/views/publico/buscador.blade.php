<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EclesTres - Encontrá tu salón</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-mate-fondo font-body">

    <header class="bg-mate-superficie border-b border-mate-borde px-4 py-4 sm:px-6 flex items-center justify-between">
        <span class="font-display text-xl">EclesTres</span>
        <div class="flex gap-4 text-sm">
            <a href="{{ route('cliente.login') }}" class="text-mate-tinta/70">Ingresar</a>
            <a href="{{ route('staff.registro-empresa') }}" class="text-mate-salvia font-medium">Sumá tu negocio</a>
        </div>
    </header>

    <section class="px-4 py-10 sm:px-6 text-center bg-mate-superficie border-b border-mate-borde">
        <h1 class="font-display text-3xl sm:text-4xl mb-2">Encontrá tu salón ideal</h1>
        <p class="text-sm text-mate-tinta/70 mb-6">Peluquerías, barberías, estéticas y uñas cerca tuyo.</p>

        <form method="GET" action="{{ route('home') }}"
            class="max-w-2xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="text" name="q" value="{{ $q }}" placeholder="Ciudad, barrio o calle..."
                class="sm:col-span-2 rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">

            <select name="rubro" class="rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                <option value="">Todos los rubros</option>
                <option value="peluqueria" @selected($rubro === 'peluqueria')>Peluquería</option>
                <option value="barberia" @selected($rubro === 'barberia')>Barbería</option>
                <option value="estetica" @selected($rubro === 'estetica')>Estética</option>
                <option value="unas" @selected($rubro === 'unas')>Uñas</option>
            </select>

            <button type="submit"
                class="sm:col-span-3 bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2 text-sm font-medium">
                Buscar
            </button>
        </form>
    </section>

    <main class="max-w-4xl mx-auto px-4 py-8 sm:px-6">
        @if ($empresas->isEmpty())
            <p class="text-sm text-mate-tinta/60 text-center py-10">
                No encontramos negocios con esos filtros. Probá con otra búsqueda.
            </p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($empresas as $empresa)
                    @php $direccion = $empresa->direcciones->first(); @endphp
                    <a href="{{ route('publico.empresa', $empresa) }}"
                        class="bg-mate-superficie border border-mate-borde rounded-lg overflow-hidden flex hover:shadow-sm transition-shadow">
                        <div class="w-20 shrink-0" style="{{ $empresa->fondoCss() }}">
                            <div class="h-full w-full bg-black/10 flex items-center justify-center p-2">
                                @if ($empresa->logo_path)
                                    <img src="{{ asset('storage/' . $empresa->logo_path) }}"
                                        class="w-10 h-10 rounded-full object-cover bg-white">
                                @endif
                            </div>
                        </div>
                        <div class="p-3 flex-1">
                            <p class="font-medium text-sm">{{ $empresa->nombre }}</p>
                            <p class="text-xs text-mate-tinta/60">{{ ucfirst($empresa->rubro) }}</p>
                            @if ($direccion)
                                <p class="text-xs text-mate-tinta/50 mt-1">
                                    {{ $direccion->calle }} {{ $direccion->altura }}, {{ $direccion->barrio }}, {{ $direccion->ciudad }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </main>

</body>
</html>