<x-layouts.app title="Próximas renovaciones - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Próximas renovaciones</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <p class="text-sm text-mate-tinta/70 mb-6">
        Clientes que deberían volver a hacerse un servicio en los próximos 5 días.
    </p>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <div x-data="{ modalAbierto: false, formPendiente: null, datosPendiente: {} }">

        <div class="space-y-2 mb-10">
            @forelse ($proximas as $item)
                @php
                    $mensaje = "Hola {$item->cliente_nombre}! Te escribimos de {$item->empresa_nombre} (EclesTres) para recordarte que ya se acerca "
                        . "el momento de renovar tu {$item->servicio_nombre} (aprox. " . \Carbon\Carbon::parse($item->fecha_renovacion)->format('d/m/Y') . "). "
                        . "¿Querés que te reservemos un turno?";
                    $link = \App\Support\WhatsApp::linkChat($item->cliente_telefono, $mensaje);
                @endphp
                <div class="bg-mate-superficie border border-mate-borde rounded-md p-3 flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <p class="text-sm font-medium">{{ $item->cliente_nombre }}</p>
                        <p class="text-xs text-mate-tinta/60">
                            {{ $item->servicio_nombre }} · renovar el {{ \Carbon\Carbon::parse($item->fecha_renovacion)->format('d/m/Y') }}
                            ({{ \Carbon\Carbon::parse($item->fecha_renovacion)->diffForHumans() }})
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('staff.empresa.turnos.manual.iniciar', ['cliente_id' => $item->cliente_id, 'servicio_id' => $item->servicio_id]) }}"
                            class="text-xs bg-mate-salvia text-white rounded-md px-3 py-1.5">
                            📅 Agendar turno
                        </a>

                        @if ($link)
                            <a href="{{ $link }}" target="_blank" class="text-xs bg-green-600 text-white rounded-md px-3 py-1.5">
                                📱 Avisar por WhatsApp
                            </a>
                        @else
                            <span class="text-xs text-mate-tinta/40">Sin teléfono</span>
                        @endif

                        <form method="POST" action="{{ route('staff.empresa.renovaciones.resolver', $item->turno_servicio_id) }}">
                            @csrf
                            <button type="button" class="text-xs border border-mate-borde rounded-md px-3 py-1.5"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); datosPendiente = {
                                    cliente: '{{ addslashes($item->cliente_nombre) }}',
                                    servicio: '{{ addslashes($item->servicio_nombre) }}',
                                    fecha: '{{ \Carbon\Carbon::parse($item->fecha_renovacion)->format('d/m/Y') }}'
                                }">
                                ✓ Marcar resuelto
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-mate-tinta/60">No hay renovaciones próximas en los próximos 5 días.</p>
            @endforelse
        </div>

        <h2 class="font-display text-lg mb-3">Vencidas (últimas 30)</h2>
        <div class="space-y-2">
            @forelse ($vencidas as $item)
                <div class="bg-mate-superficie border border-mate-borde rounded-md p-3 flex items-center justify-between gap-3 flex-wrap opacity-90">
                    <div>
                        <p class="text-sm font-medium">{{ $item->cliente_nombre }}</p>
                        <p class="text-xs text-red-700">
                            {{ $item->servicio_nombre }} · venció el {{ \Carbon\Carbon::parse($item->fecha_renovacion)->format('d/m/Y') }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('staff.empresa.renovaciones.resolver', $item->turno_servicio_id) }}">
                        @csrf
                        <button type="button" class="text-xs border border-mate-borde rounded-md px-3 py-1.5"
                            @click="modalAbierto = true; formPendiente = $el.closest('form'); datosPendiente = {
                                cliente: '{{ addslashes($item->cliente_nombre) }}',
                                servicio: '{{ addslashes($item->servicio_nombre) }}',
                                fecha: '{{ \Carbon\Carbon::parse($item->fecha_renovacion)->format('d/m/Y') }}'
                            }">
                            ✓ Marcar resuelto
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-mate-tinta/60">No hay renovaciones vencidas.</p>
            @endforelse
        </div>

        {{-- Modal de confirmación --}}
        <div x-show="modalAbierto" x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50"
            style="display: none;">
            <div @click.outside="modalAbierto = false" class="bg-white rounded-lg max-w-sm w-full p-5">
                <p class="text-sm font-medium mb-3">¿Marcar esta renovación como resuelta?</p>
                <div class="text-sm bg-mate-fondo rounded-md p-3 mb-5 space-y-1">
                    <p><strong>Cliente:</strong> <span x-text="datosPendiente.cliente"></span></p>
                    <p><strong>Servicio:</strong> <span x-text="datosPendiente.servicio"></span></p>
                    <p><strong>Fecha de renovación:</strong> <span x-text="datosPendiente.fecha"></span></p>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="modalAbierto = false"
                        class="flex-1 border border-mate-borde rounded-md py-2 text-sm font-medium">
                        Cancelar
                    </button>
                    <button type="button" @click="modalAbierto = false; $nextTick(() => formPendiente.submit())"
                        class="flex-1 bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2 text-sm font-medium">
                        Sí, marcar resuelto
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>