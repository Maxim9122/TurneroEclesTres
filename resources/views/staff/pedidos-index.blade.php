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

    <div class="space-y-3">
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
                    @if ($pedido->estado === 'pendiente')
                        <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                            @csrf
                            <input type="hidden" name="estado" value="confirmado">
                            <button class="underline text-green-700">Confirmar</button>
                        </form>
                        <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                            @csrf
                            <input type="hidden" name="estado" value="cancelado">
                            <button class="underline text-red-700">Cancelar</button>
                        </form>
                    @endif

                    @if ($pedido->estado === 'confirmado')
                        <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                            @csrf
                            <input type="hidden" name="estado" value="en_preparacion">
                            <button class="underline text-blue-700">En preparación</button>
                        </form>
                    @endif

                    @if ($pedido->estado === 'en_preparacion')
                        @if ($pedido->metodo_entrega === 'retiro')
                            <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                                @csrf
                                <input type="hidden" name="estado" value="listo">
                                <button class="underline text-green-700">Listo para retirar</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                                @csrf
                                <input type="hidden" name="estado" value="enviado">
                                <button class="underline text-blue-700">Marcar enviado</button>
                            </form>
                        @endif
                    @endif

                    @if (in_array($pedido->estado, ['listo', 'enviado']))
                        <form method="POST" action="{{ route('staff.empresa.pedidos.estado', $pedido) }}">
                            @csrf
                            <input type="hidden" name="estado" value="entregado">
                            <button class="underline text-green-700">Marcar entregado</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No hay pedidos en este estado.</p>
        @endforelse
    </div>
</x-layouts.app>