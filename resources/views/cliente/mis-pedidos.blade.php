<x-layouts.app title="Mis pedidos - EclesTres" :logout-route="route('cliente.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Mis pedidos</h1>
        <a href="{{ route('cliente.home') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>
    <form method="GET" class="flex flex-wrap items-end gap-3 mb-6">
        <div>
            <label class="block text-xs mb-1">Desde</label>
            <input type="date" name="desde" value="{{ $desde }}" class="rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ $hasta }}" class="rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
        </div>
        <button type="submit" class="bg-mate-salvia text-white rounded-md px-4 py-2 text-sm font-medium">Filtrar</button>
        @if ($desde || $hasta)
            <a href="{{ route('cliente.pedidos.index') }}" class="text-sm text-mate-tinta/60 underline">Limpiar</a>
        @endif
    </form>
    <div class="space-y-3">
        @forelse ($pedidos as $pedido)
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div>
                        <p class="font-medium text-sm">{{ $pedido->empresa->nombre }}</p>
                        <p class="text-xs text-mate-tinta/60">
                            {{ $pedido->created_at->format('d/m/Y H:i') }} ·
                            {{ $pedido->metodo_entrega === 'retiro' ? 'Retiro en local' : 'Envío a domicilio' }}
                        </p>
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

                <div class="text-xs mt-3 space-y-1 border-t border-mate-borde pt-3">
                    @foreach ($pedido->items as $item)
                        <div class="flex justify-between">
                            <span>{{ $item->cantidad }} x {{ $item->producto->nombre }}</span>
                            <span>${{ number_format($item->subtotal(), 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between text-sm font-medium pt-1">
                        <span>Total</span>
                        <span>${{ number_format($pedido->total, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">Todavía no hiciste ningún pedido.</p>
        @endforelse
    </div>
    <div class="mt-4">
        {{ $pedidos->onEachSide(1)->links() }}
    </div>
</x-layouts.app>