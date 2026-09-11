<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar turno - {{ $empresa->nombre }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-mate-fondo font-body">

<header class="bg-mate-superficie border-b border-mate-borde px-4 py-4">
    <x-empresa-topbar :empresa="$empresa" :back-route="route('publico.empresa', $empresa)" back-label="Reservando turno" />
</header>

<main class="max-w-md mx-auto px-4 py-6"
    x-data="{
        fecha: '',
        hora: '',
        slots: [],
        cargando: false,
        error: '',
        buscarHorarios() {
            if (!this.fecha) return;
            this.cargando = true;
            this.hora = '';
            this.error = '';
            const params = new URLSearchParams();
            params.append('fecha', this.fecha);
            @foreach ($servicios as $servicio)
                params.append('servicios[]', '{{ $servicio->id }}');
            @endforeach
            @if ($profesional)
                params.append('profesional', '{{ $profesional->id }}');
            @endif

            fetch('{{ route('publico.reserva.horarios', $empresa) }}?' + params.toString())
                .then(r => r.json())
                .then(data => { this.slots = data.slots; this.cargando = false; })
                .catch(() => { this.error = 'No pudimos cargar los horarios. Probá de nuevo.'; this.cargando = false; });
        }
    }">

    <h1 class="font-display text-xl mb-1">Elegí día y horario</h1>
    <p class="text-sm text-mate-tinta/70 mb-6">
        {{ $servicios->pluck('nombre')->implode(' + ') }}
        ({{ $servicios->sum('duracion_minutos') }} min · ${{ number_format($servicios->sum('precio'), 2, ',', '.') }})
        @if ($profesional)
            <br>Con {{ $profesional->nombre }}
        @else
            <br>Sin preferencia de profesional
        @endif
    </p>

    <div class="mb-6">
        <label class="block text-sm mb-1">Fecha</label>
        <input type="date" x-model="fecha" @change="buscarHorarios()" min="{{ now()->toDateString() }}"
            class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
    </div>

    <div x-show="cargando" class="text-sm text-mate-tinta/60 mb-4">Buscando horarios disponibles...</div>
    <div x-show="error" x-text="error" x-cloak class="text-sm text-red-700 mb-4"></div>

    <div x-show="fecha && !cargando && slots.length === 0 && !error" x-cloak class="text-sm text-mate-tinta/60 mb-4">
        No hay horarios disponibles ese día. Probá con otra fecha.
    </div>

    <div class="grid grid-cols-3 gap-2 mb-6" x-show="slots.length > 0">
        <template x-for="slot in slots" :key="slot">
            <button type="button" @click="hora = slot"
                class="text-sm py-2 rounded-md border"
                :class="hora === slot ? 'bg-mate-salvia text-white border-mate-salvia' : 'border-mate-borde'"
                x-text="slot"></button>
        </template>
    </div>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('publico.reserva.confirmar', $empresa) }}" x-show="hora" x-cloak>
        @csrf
        <input type="hidden" name="fecha" x-bind:value="fecha">
        <input type="hidden" name="hora" x-bind:value="hora">
        @foreach ($servicios as $servicio)
            <input type="hidden" name="servicios[]" value="{{ $servicio->id }}">
        @endforeach
        @if ($profesional)
            <input type="hidden" name="profesional" value="{{ $profesional->id }}">
        @endif

        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-3 text-sm font-medium">
            Confirmar turno <span x-text="hora"></span>
        </button>
    </form>
</main>
<x-footer />
</body>
</html>