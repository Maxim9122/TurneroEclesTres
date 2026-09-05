<x-layouts.app title="Mis turnos - EclesTres" :logout-route="route('cliente.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Mis turnos</h1>
        <a href="{{ route('cliente.home') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <h2 class="font-display text-lg mb-3">Próximos</h2>
    <div class="space-y-3 mb-8">
        @forelse ($proximos as $turno)
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div>
                        <p class="font-medium text-sm">{{ $turno->empresa->nombre }}</p>
                        <p class="text-xs text-mate-tinta/60 mt-0.5">
                            {{ $turno->fecha->format('d/m/Y') }} · {{ substr($turno->hora_inicio, 0, 5) }}hs
                        </p>
                        <p class="text-xs text-mate-tinta/60">
                            {{ $turno->servicios->pluck('nombre')->implode(' + ') }}
                            · ${{ number_format($turno->precioTotal(), 2, ',', '.') }}
                        </p>
                        <p class="text-xs text-mate-tinta/60">
                            Con {{ $turno->profesional->nombre ?? 'profesional a asignar' }}
                        </p>
                    </div>

                    <span class="text-xs px-2 py-0.5 rounded-full h-fit
                        {{ $turno->estado === 'confirmado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ ucfirst($turno->estado) }}
                    </span>
                </div>

                <form method="POST" action="{{ route('cliente.turnos.cancelar', $turno) }}" class="mt-3"
                    onsubmit="return confirm('¿Seguro que querés cancelar este turno?');">
                    @csrf
                    <button type="submit" class="text-xs underline text-red-700">Cancelar turno</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No tenés turnos próximos.</p>
        @endforelse
    </div>

    <h2 class="font-display text-lg mb-3">Historial</h2>
    <div class="space-y-3">
        @forelse ($historial as $turno)
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4 opacity-75">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div>
                        <p class="font-medium text-sm">{{ $turno->empresa->nombre }}</p>
                        <p class="text-xs text-mate-tinta/60 mt-0.5">
                            {{ $turno->fecha->format('d/m/Y') }} · {{ substr($turno->hora_inicio, 0, 5) }}hs
                        </p>
                        <p class="text-xs text-mate-tinta/60">
                            {{ $turno->servicios->pluck('nombre')->implode(' + ') }}
                        </p>
                    </div>

                    <span class="text-xs px-2 py-0.5 rounded-full h-fit
                        @class([
                            'bg-blue-100 text-blue-800' => $turno->estado === 'completado',
                            'bg-red-100 text-red-800' => in_array($turno->estado, ['cancelado', 'no_show']),
                            'bg-gray-100 text-gray-600' => in_array($turno->estado, ['pendiente', 'confirmado']),
                        ])">
                        {{ ucfirst(str_replace('_', ' ', $turno->estado)) }}
                    </span>
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">Todavía no tenés turnos en tu historial.</p>
        @endforelse
    </div>
</x-layouts.app>