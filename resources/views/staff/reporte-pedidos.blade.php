<x-layouts.app title="Reporte de pedidos - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Reporte de pedidos</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <form method="GET" action="{{ route('staff.empresa.reportes.pedidos') }}" class="flex flex-wrap items-end gap-3 mb-8">
        <div>
            <label class="block text-sm mb-1">Desde</label>
            <input type="date" name="desde" value="{{ $desde }}" class="rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ $hasta }}" class="rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
        </div>
        <button type="submit" class="bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2 text-sm font-medium">
            Filtrar
        </button>
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
        <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4">
            <p class="text-xs text-mate-tinta/50">Pedidos totales</p>
            <p class="font-medium text-lg">{{ $resumen['cantidad_total'] }}</p>
        </div>
        <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4">
            <p class="text-xs text-mate-tinta/50">Facturado (entregados)</p>
            <p class="font-medium text-lg">${{ number_format($resumen['total_facturado'], 2, ',', '.') }}</p>
        </div>
        <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4">
            <p class="text-xs text-mate-tinta/50">Entregados</p>
            <p class="font-medium text-lg text-green-700">{{ $resumen['cantidad_entregados'] }}</p>
        </div>
        <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4">
            <p class="text-xs text-mate-tinta/50">Cancelados</p>
            <p class="font-medium text-lg text-red-700">{{ $resumen['cantidad_cancelados'] }}</p>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-6 mb-8">
        <div>
            <p class="text-sm font-medium mb-2">Por estado</p>
            <div class="space-y-1 text-sm">
                @forelse ($resumen['por_estado'] as $estado => $cantidad)
                    <div class="flex justify-between border-b border-mate-borde py-1">
                        <span>{{ ucfirst(str_replace('_', ' ', $estado)) }}</span>
                        <span>{{ $cantidad }}</span>
                    </div>
                @empty
                    <p class="text-xs text-mate-tinta/50">Sin datos.</p>
                @endforelse
            </div>
        </div>

        <div>
            <p class="text-sm font-medium mb-2">Por método de entrega</p>
            <div class="space-y-1 text-sm">
                @forelse ($resumen['por_metodo_entrega'] as $metodo => $cantidad)
                    <div class="flex justify-between border-b border-mate-borde py-1">
                        <span>{{ $metodo === 'retiro' ? 'Retiro en local' : 'Envío a domicilio' }}</span>
                        <span>{{ $cantidad }}</span>
                    </div>
                @empty
                    <p class="text-xs text-mate-tinta/50">Sin datos.</p>
                @endforelse
            </div>
        </div>
    </div>

    <p class="text-sm font-medium mb-3">Detalle de pedidos</p>
    <div class="space-y-2">
        @forelse ($pedidos as $pedido)
            <div class="bg-mate-superficie border border-mate-borde rounded-md px-4 py-2.5 flex items-center justify-between text-sm flex-wrap gap-2">
                <span>#{{ $pedido->id }} · {{ $pedido->created_at->format('d/m/Y H:i') }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full
                    @class([
                        'bg-yellow-100 text-yellow-800' => $pedido->estado === 'pendiente',
                        'bg-green-100 text-green-800' => in_array($pedido->estado, ['confirmado', 'listo', 'entregado']),
                        'bg-blue-100 text-blue-800' => in_array($pedido->estado, ['en_preparacion', 'enviado']),
                        'bg-red-100 text-red-800' => $pedido->estado === 'cancelado',
                    ])">
                    {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                </span>
                <span class="font-medium">${{ number_format($pedido->total, 2, ',', '.') }}</span>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No hay pedidos en este rango de fechas.</p>
        @endforelse
    </div>
</x-layouts.app>