<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $empresa->nombre }} - EclesTres</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

    <main class="max-w-md mx-auto px-4 py-6" x-data="{
        servicioIds: [],
        profesionalId: '',
        toggleServicio(id) {
            this.servicioIds.includes(id)
                ? this.servicioIds = this.servicioIds.filter(s => s !== id)
                : this.servicioIds.push(id);
        }
    }">
        @if ($empresa->descripcion)
            <p class="text-sm text-mate-tinta/80 mb-6">{{ $empresa->descripcion }}</p>
        @endif

        {{-- Servicios --}}
        <section class="mb-8">
            <h2 class="font-display text-lg mb-3">Servicios</h2>

            @if ($servicios->isEmpty())
                <p class="text-sm text-mate-tinta/60">Este negocio todavía no cargó servicios.</p>
            @else
                <div class="space-y-2">
                    @foreach ($servicios as $servicio)
                        <label class="flex items-center justify-between bg-mate-superficie border border-mate-borde rounded-md px-3 py-2.5 cursor-pointer"
                            :class="servicioIds.includes({{ $servicio->id }}) ? 'ring-2 ring-mate-salvia' : ''">
                            <span class="flex items-center gap-2 text-sm">
                                <input type="checkbox" class="rounded border-mate-borde"
                                    @change="toggleServicio({{ $servicio->id }})">
                                {{ $servicio->nombre }}
                                <span class="text-xs text-mate-tinta/50">({{ $servicio->duracion_minutos }} min)</span>
                            </span>
                            <span class="text-sm font-medium">${{ number_format($servicio->precio, 2, ',', '.') }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Productos --}}
        @if ($productos->isNotEmpty())
            <section class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-display text-lg">Productos</h2>
                    <a href="{{ route('publico.carrito.index', $empresa) }}" class="text-sm text-mate-salvia">Ver carrito →</a>
                </div>

                <div class="space-y-2">
                    @foreach ($productos as $producto)
                        <div class="flex items-center gap-3 bg-mate-superficie border border-mate-borde rounded-md px-3 py-2.5">
                            @if ($producto->foto_path)
                                <img src="{{ asset('storage/' . $producto->foto_path) }}" class="w-12 h-12 rounded-md object-cover">
                            @else
                                <div class="w-12 h-12 rounded-md bg-mate-borde"></div>
                            @endif

                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ $producto->nombre }}</p>
                                <p class="text-xs text-mate-tinta/60">${{ number_format($producto->precio, 2, ',', '.') }}</p>
                                @if ($producto->stock === 0)
                                    <p class="text-xs text-red-700">Sin stock</p>
                                @endif
                            </div>

                            @if ($producto->stock > 0)
                                <form method="POST" action="{{ route('publico.carrito.agregar', $empresa) }}">
                                    @csrf
                                    <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                                    <input type="hidden" name="cantidad" value="1">
                                    <button type="submit" class="text-xs bg-mate-salvia text-white rounded-md px-3 py-1.5">
                                        Agregar
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if (session('status'))
                    <p class="text-xs text-green-700 mt-2">{{ session('status') }}</p>
                @endif
            </section>
        @endif

        {{-- Profesionales --}}
        <section class="mb-8">
            <h2 class="font-display text-lg mb-3">¿Con quién te querés atender?</h2>

            <div class="grid grid-cols-3 gap-3">
                <button type="button" @click="profesionalId = ''"
                    class="flex flex-col items-center gap-1 p-2 rounded-md border"
                    :class="profesionalId === '' ? 'border-mate-salvia bg-mate-superficie' : 'border-mate-borde'">
                    <div class="w-12 h-12 rounded-full bg-mate-borde flex items-center justify-center text-xs text-mate-tinta/60">
                        Sin<br>pref.
                    </div>
                    <span class="text-xs text-center">Sin preferencia</span>
                </button>

                @foreach ($profesionales as $profesional)
                    <button type="button" @click="profesionalId = '{{ $profesional->id }}'"
                        class="flex flex-col items-center gap-1 p-2 rounded-md border"
                        :class="profesionalId === '{{ $profesional->id }}' ? 'border-mate-salvia bg-mate-superficie' : 'border-mate-borde'">
                        @if ($profesional->foto_path)
                            <img src="{{ asset('storage/' . $profesional->foto_path) }}" class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-mate-borde"></div>
                        @endif
                        <span class="text-xs text-center">{{ $profesional->nombre }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- Resumen y continuar --}}
        <div class="sticky bottom-4">
        <button type="button"
            x-show="servicioIds.length > 0"
            x-cloak
            @click="
                const params = new URLSearchParams();
                servicioIds.forEach(id => params.append('servicios[]', id));
                if (profesionalId) params.append('profesional', profesionalId);
                window.location.href = '{{ route('publico.reserva.iniciar', $empresa) }}?' + params.toString();
            "
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-3 text-sm font-medium shadow-lg">
            Continuar reserva
        </button>
    </div>

        <p class="text-xs text-mate-tinta/50 text-center mt-8">
            <a href="{{ route('home') }}" class="underline">EclesTres</a>
        </p>
    </main>
    <x-footer />
</body>
</html>