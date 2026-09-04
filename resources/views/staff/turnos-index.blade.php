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

    <div class="space-y-3">
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
                    <a href="{{ route('staff.empresa.turnos.edit', $turno) }}" class="underline text-mate-tinta/70">
                        Editar profesional/servicios
                    </a>

                    @if ($turno->estado === 'pendiente')
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="confirmado">
                            <button class="underline text-green-700">Confirmar</button>
                        </form>
                    @endif

                    @if (in_array($turno->estado, ['pendiente', 'confirmado']))
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="cancelado">
                            <button class="underline text-red-700">Cancelar</button>
                        </form>
                    @endif

                    @if ($turno->estado === 'confirmado')
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="completado">
                            <button class="underline text-blue-700">Completado</button>
                        </form>
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="no_show">
                            <button class="underline text-red-700">No asistió</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No hay turnos para esta fecha.</p>
        @endforelse
    </div>
</x-layouts.app>