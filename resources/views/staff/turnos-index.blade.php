<x-layouts.app title="Turnos - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Turnos</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="GET" action="{{ route('staff.empresa.turnos.index') }}" class="mb-6">
        <label class="block text-sm mb-1">Fecha</label>
        <input type="date" name="fecha" value="{{ $fecha }}" onchange="this.form.submit()"
            class="rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
    </form>

    <div class="space-y-3" x-data="{ modalAbierto: false, formPendiente: null, mensajePendiente: '' }">
        @forelse ($turnos as $turno)
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div>
                        <p class="font-medium text-sm">
                            {{ substr($turno->hora_inicio, 0, 5) }} - {{ substr($turno->hora_fin, 0, 5) }}
                            · {{ $turno->cliente->nombre }}
                        </p>
                        <p class="text-xs text-mate-tinta/60 mt-0.5">
                            {{ $turno->servicios->pluck('nombre')->implode(' + ') }}
                            · ${{ number_format($turno->precioTotal(), 2, ',', '.') }}
                        </p>
                        <p class="text-xs text-mate-tinta/60">
                            Profesional:
                            <strong>{{ $turno->profesional->nombre ?? 'Sin asignar' }}</strong>
                        </p>
                    </div>

                    <span class="text-xs px-2 py-0.5 rounded-full h-fit
                        @class([
                            'bg-yellow-100 text-yellow-800' => $turno->estado === 'pendiente',
                            'bg-green-100 text-green-800' => $turno->estado === 'confirmado',
                            'bg-blue-100 text-blue-800' => $turno->estado === 'completado',
                            'bg-red-100 text-red-800' => in_array($turno->estado, ['cancelado', 'no_show']),
                        ])">
                        {{ ucfirst(str_replace('_', ' ', $turno->estado)) }}
                    </span>
                </div>

                <div class="flex flex-wrap gap-2 mt-3 text-sm">
                    @php
                        $mensajeTurno = "Hola {$turno->cliente->nombre}! Te confirmamos tu turno en {$turno->empresa->nombre} "
                            . "para el {$turno->fecha->format('d/m/Y')} a las " . substr($turno->hora_inicio, 0, 5) . "hs. "
                            . "Servicios: " . $turno->servicios->pluck('nombre')->implode(' + ') . ". ¡Te esperamos!";
                        $linkWhatsapp = \App\Support\WhatsApp::linkChat($turno->cliente->telefono, $mensajeTurno);
                    @endphp

                    @if ($linkWhatsapp)
                        <a href="{{ $linkWhatsapp }}" target="_blank" class="underline text-green-700">
                            📱 Enviar WhatsApp
                        </a>
                    @else
                        <span class="text-mate-tinta/40 text-xs">Cliente sin teléfono cargado</span>
                    @endif

                    <a href="{{ route('staff.empresa.turnos.remito', $turno) }}" target="_blank" class="underline text-mate-salvia">
                        📄 Enviar comprobante
                    </a>

                    <a href="{{ route('staff.empresa.turnos.edit', $turno) }}" class="underline text-mate-tinta/70">
                        Editar profesional/servicios
                    </a>

                    @if ($turno->estado === 'pendiente')
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="confirmado">
                            <button type="button" class="underline text-green-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Confirmar este turno?'">
                                Confirmar
                            </button>
                        </form>
                    @endif

                    @if (in_array($turno->estado, ['pendiente', 'confirmado']))
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="cancelado">
                            <button type="button" class="underline text-red-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Cancelar este turno? Esta acción no se puede deshacer.'">
                                Cancelar
                            </button>
                        </form>
                    @endif

                    @if ($turno->estado === 'confirmado')
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="completado">
                            <button type="button" class="underline text-blue-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Marcar este turno como completado?'">
                                Completado
                            </button>
                        </form>
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="no_show">
                            <button type="button" class="underline text-red-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Marcar que el cliente no asistió?'">
                                No asistió
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No hay turnos para esta fecha.</p>
        @endforelse

        {{-- Modal de confirmación --}}
        <div x-show="modalAbierto" x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50"
            style="display: none;">
            <div @click.outside="modalAbierto = false" class="bg-white rounded-lg max-w-sm w-full p-5">
                <p class="text-sm mb-5" x-text="mensajePendiente"></p>
                <div class="flex gap-2">
                    <button type="button" @click="modalAbierto = false"
                        class="flex-1 border border-mate-borde rounded-md py-2 text-sm font-medium">
                        Cancelar
                    </button>
                    <button type="button" @click="modalAbierto = false; $nextTick(() => formPendiente.submit())"
                        class="flex-1 bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2 text-sm font-medium">
                        Sí, confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>