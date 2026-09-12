<x-layouts.app title="Pedidos - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Pedidos</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-wrap gap-2 mb-6 text-sm">
        <a href="{{ route('staff.empresa.pedidos.index') }}"
            class="px-3 py-1.5 rounded-full border {{ !$estado ? 'bg-mate-salvia text-white border-mate-salvia' : 'border-mate-borde' }}">
            Todos
        </a>
        @foreach (['pendiente' => 'Pendientes', 'confirmado' => 'Confirmados', 'en_preparacion' => 'En preparación', 'listo' => 'Listos', 'enviado' => 'Enviados', 'entregado' => 'Entregados', 'cancelado' => 'Cancelados'] as $key => $label)
            <a href="{{ route('staff.empresa.pedidos.index', ['estado' => $key]) }}"
                class="px-3 py-1.5 rounded-full border {{ $estado === $key ? 'bg-mate-salvia text-white border-mate-salvia' : 'border-mate-borde' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-3" x-data="{ modalAbierto: false, formPendiente: null, mensajePendiente: '' }">
        @forelse ($pedidos as $pedido)
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div>
                        <p class="font-medium text-sm">{{ $pedido->cliente->nombre }} · #{{ $pedido->id }}</p>
                        <p class="text-xs text-mate-tinta/60 mt-0.5">
                            {{ $pedido->created_at->format('d/m/Y H:i') }} ·
                            {{ $pedido->metodo_entrega === 'retiro' ? 'Retiro en local' : 'Envío a domicilio' }}
                        </p>
                        @if ($pedido->direccionEnvio)
                            <p class="text-xs text-mate-tinta/60">
                                {{ $pedido->direccionEnvio->calle }} {{ $pedido->direccionEnvio->altura }}, {{ $pedido->direccionEnvio->barrio }}
                            </p>
                        @endif
                        @if ($pedido->notas)
                            <p class="text-xs text-mate-tinta/60 italic">"{{ $pedido->notas }}"</p>
                        @endif
                    </div>

                    <span class="text-xs px-2 py-0.5 rounded-full h-fit
                        @class([
                            'bg-yellow-100 text-yellow-800' => $pedido->estado === 'pendiente',
                            'bg-green-100 text-green-800' => in_array($pedido->estado, ['confirmado', 'listo', 'entregado']),
                            'bg-blue-100 text-blue-800' => in_array($pedido->estado, ['en_preparacion', 'enviado']),
                            'bg-red-100 text-red-800' => $pedido->estado === 'cancelado',
                        ])">
                        {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                    </span>
                </div>

                <div class="text-sm mt-3 space-y-1 border-t border-mate-borde pt-3">
                    @foreach ($pedido->items as $item)
                        <div class="flex justify-between text-xs">
                            <span>{{ $item->cantidad }} x {{ $item->producto->nombre }}</span>
                            <span>${{ number_format($item->subtotal(), 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between text-sm font-medium pt-1">
                        <span>Total</span>
                        <span>${{ number_format($pedido->total, 2, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mt-3 text-sm">
                    @php
                        $itemsTexto = $pedido->items->map(fn ($item) => "{$item->cantidad}x {$item->producto->nombre}")->implode(', ');
                        $mensajePedido = "Hola {$pedido->cliente->nombre}! Te confirmamos tu pedido en {$pedido->empresa->nombre}: "
                            . $itemsTexto . ". Total: \${$pedido->total}. "
                            . ($pedido->metodo_entrega === 'retiro' ? 'Podés retirarlo en el local.' : 'Coordinamos el envío a tu domicilio.');
                        $linkWhatsappPedido = \App\Support\WhatsApp::linkChat($pedido->cliente->telefono, $mensajePedido);
                    @endphp

                    @if ($linkWhatsappPedido)
                        <a href="{{ $linkWhatsappPedido }}" target="_blank" class="underline text-green-700">
                            📱 Enviar WhatsApp
                        </a>
                    @else
                        <span class="text-mate-tinta/40 text-xs">Cliente sin teléfono cargado</span>
                    @endif

                    <a href="{{ route('staff.empresa.pedidos.remito', $pedido) }}" target="_blank" class="underline text-mate-salvia">
                        📄 Enviar remito
                    </a>

                    @if ($pedido->estado === 'pendiente')
                        <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                            @csrf
                            <input type="hidden" name="estado" value="confirmado">
                            <button type="button" class="underline text-green-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Confirmar este pedido?'">
                                Confirmar
                            </button>
                        </form>
                        <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                            @csrf
                            <input type="hidden" name="estado" value="cancelado">
                            <button type="button" class="underline text-red-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Cancelar este pedido? Esta acción no se puede deshacer.'">
                                Cancelar
                            </button>
                        </form>
                    @endif

                    @if ($pedido->estado === 'confirmado')
                        <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                            @csrf
                            <input type="hidden" name="estado" value="en_preparacion">
                            <button type="button" class="underline text-blue-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Marcar este pedido como en preparación?'">
                                En preparación
                            </button>
                        </form>
                    @endif

                    @if ($pedido->estado === 'en_preparacion')
                        @if ($pedido->metodo_entrega === 'retiro')
                            <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                                @csrf
                                <input type="hidden" name="estado" value="listo">
                                <button type="button" class="underline text-green-700"
                                    @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Marcar este pedido como listo para retirar?'">
                                    Listo para retirar
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                                @csrf
                                <input type="hidden" name="estado" value="enviado">
                                <button type="button" class="underline text-blue-700"
                                    @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Marcar este pedido como enviado?'">
                                    Marcar enviado
                                </button>
                            </form>
                        @endif
                    @endif

                    @if (in_array($pedido->estado, ['listo', 'enviado']))
                        <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                            @csrf
                            <input type="hidden" name="estado" value="entregado">
                            <button type="button" class="underline text-green-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Marcar este pedido como entregado?'">
                                Marcar entregado
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No hay pedidos en este estado.</p>
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