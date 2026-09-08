<x-layouts.app title="Comisiones de profesionales - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Comisiones de profesionales</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <form method="GET" action="{{ route('staff.empresa.reportes.comisiones') }}" class="flex flex-wrap items-end gap-3 mb-8">
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

    @if ($filas->isEmpty())
        <p class="text-sm text-mate-tinta/60">No hay turnos completados en este rango de fechas.</p>
    @else
        <div class="space-y-3 mb-6">
            @foreach ($filas as $fila)
                <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4" x-data="{ verDetalle: false }">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <p class="font-medium text-sm">{{ $fila['profesional']->nombre }}</p>
                        <button type="button" @click="verDetalle = true" class="text-xs underline text-mate-salvia">
                            {{ $fila['cantidad_turnos'] }} turnos completados — Ver detalle
                        </button>
                    </div>
                    <div class="grid grid-cols-3 gap-3 mt-3 text-sm">
                        <div>
                            <p class="text-xs text-mate-tinta/50">Recaudado</p>
                            <p class="font-medium">${{ number_format($fila['recaudado'], 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-mate-tinta/50">Comisión ({{ $fila['porcentaje'] }}%)</p>
                            <p class="font-medium text-mate-salvia">${{ number_format($fila['comision'], 2, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-mate-tinta/50">Para el negocio</p>
                            <p class="font-medium">${{ number_format($fila['para_el_negocio'], 2, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Modal de detalle --}}
                    <div x-show="verDetalle" x-cloak
                        class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50"
                        style="display: none;">
                        <div @click.outside="verDetalle = false"
                            class="bg-white rounded-lg max-w-md w-full max-h-[80vh] overflow-y-auto p-5">
                            <div class="flex items-center justify-between mb-4">
                                <p class="font-medium text-sm">Turnos de {{ $fila['profesional']->nombre }}</p>
                                <button type="button" @click="verDetalle = false" class="text-mate-tinta/50 text-xl leading-none">&times;</button>
                            </div>

                            <div class="space-y-2">
                                @foreach ($fila['turnos'] as $turno)
                                    <div class="border border-mate-borde rounded-md p-3 text-sm">
                                        <div class="flex justify-between">
                                            <span class="font-medium">{{ $turno->fecha->format('d/m/Y') }} · {{ substr($turno->hora_inicio, 0, 5) }}hs</span>
                                            <span>${{ number_format($turno->precioTotal(), 2, ',', '.') }}</span>
                                        </div>
                                        <p class="text-xs text-mate-tinta/60 mt-1">
                                            {{ $turno->cliente->nombre ?? 'Cliente' }} ·
                                            {{ $turno->servicios->pluck('nombre')->implode(' + ') }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-mate-tinta text-white rounded-lg p-4">
            <p class="text-sm font-medium mb-2">Totales del período</p>
            <div class="grid grid-cols-3 gap-3 text-sm">
                <div>
                    <p class="text-xs text-white/60">Recaudado</p>
                    <p class="font-medium">${{ number_format($totales['recaudado'], 2, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-white/60">Total comisiones</p>
                    <p class="font-medium">${{ number_format($totales['comision'], 2, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-white/60">Para el negocio</p>
                    <p class="font-medium">${{ number_format($totales['para_el_negocio'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
    @endif
</x-layouts.app>