<!DOCTYPE html>
<html lang="es" x-data="{ menuAbierto: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title>{{ $title ?? 'EclesTres' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-mate-fondo font-body">

    <header class="bg-mate-superficie border-b border-mate-borde">
        <div class="flex items-center justify-between px-4 py-3 sm:px-6">
            <a href="{{ route('home') }}" title="Ir al sitio principal">
                <x-brand size="text-xl" />
            </a>

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

    @if (request()->routeIs('staff.*') && auth('web')->check())
        @php
            $empresaActual = auth('web')->user()->empresa;
            $direccionActual = $empresaActual?->direcciones->first();
        @endphp
        @if ($empresaActual)
            <div style="{{ $empresaActual->fondoCss() }}">
                <div class="bg-black/15 px-4 py-4 sm:px-6 flex items-center gap-3">
                    @if ($empresaActual->logo_path)
                        <img src="{{ asset('storage/' . $empresaActual->logo_path) }}"
                            class="w-10 h-10 rounded-full object-cover bg-white shadow shrink-0">
                    @endif
                    <div class="min-w-0">
                        <p class="text-white font-display text-base leading-tight texto-delineado truncate">{{ $empresaActual->nombre }}</p>
                        <p class="text-white/80 text-xs truncate">
                            {{ ucfirst($empresaActual->rubro) }}
                            @if ($direccionActual && $direccionActual->ciudad)
                                · {{ $direccionActual->barrio }}{{ $direccionActual->barrio && $direccionActual->ciudad ? ', ' : '' }}{{ $direccionActual->ciudad }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <main class="px-4 py-6 sm:px-6 max-w-5xl mx-auto">
        {{ $slot }}
    </main>
    @if (request()->routeIs('staff.*') && auth('web')->check() && !auth('web')->user()->esSuperAdmin())
        <div x-data="{
                toastVisible: false,
                mensaje: '',
                audioCtx: null,
                ultimaVerificacion: localStorage.getItem('eclestres_ultima_notif') || new Date(Date.now() - 60000).toISOString(),
                iniciarAudio() {
                    if (!this.audioCtx) {
                        try {
                            this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        } catch (e) {}
                    }
                },
                sonar() {
                    if (!this.audioCtx) return;
                    try {
                        if (this.audioCtx.state === 'suspended') {
                            this.audioCtx.resume();
                        }
                        const osc = this.audioCtx.createOscillator();
                        const gain = this.audioCtx.createGain();
                        osc.connect(gain);
                        gain.connect(this.audioCtx.destination);
                        osc.frequency.value = 880;
                        gain.gain.setValueAtTime(0.15, this.audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.5);
                        osc.start();
                        osc.stop(this.audioCtx.currentTime + 0.5);
                    } catch (e) {}
                },
                verificar() {
                    fetch('{{ route('staff.empresa.notificaciones.verificar') }}?desde=' + encodeURIComponent(this.ultimaVerificacion))
                        .then(r => r.json())
                        .then(data => {
                            const total = data.turnos + data.pedidos;
                            if (total > 0) {
                                let partes = [];
                                if (data.turnos > 0) partes.push(data.turnos + (data.turnos === 1 ? ' turno nuevo' : ' turnos nuevos'));
                                if (data.pedidos > 0) partes.push(data.pedidos + (data.pedidos === 1 ? ' pedido nuevo' : ' pedidos nuevos'));
                                this.mensaje = partes.join(' y ');
                                this.toastVisible = true;
                                this.sonar();
                                setTimeout(() => { this.toastVisible = false; }, 6000);
                            }
                            this.ultimaVerificacion = data.ahora;
                            localStorage.setItem('eclestres_ultima_notif', data.ahora);
                        })
                        .catch(() => {});
                }
            }"
            x-init="setInterval(() => verificar(), 25000)"
            @click.window="iniciarAudio()"
            @keydown.window="iniciarAudio()">

            <div x-show="toastVisible" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed bottom-5 right-5 z-50 bg-mate-tinta text-white rounded-lg shadow-lg px-4 py-3 max-w-xs flex items-center gap-3"
                style="display: none;">
                <span class="text-xl">🔔</span>
                <div class="text-sm">
                    <p class="font-medium">¡Novedad!</p>
                    <p class="text-white/80" x-text="mensaje"></p>
                </div>
                <button type="button" @click="toastVisible = false" class="text-white/50 text-lg leading-none ml-1">&times;</button>
            </div>
        </div>
    @endif
    <x-footer />
</body>
</html>