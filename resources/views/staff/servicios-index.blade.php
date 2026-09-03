<x-layouts.app title="Servicios - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Servicios</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <a href="{{ route('staff.empresa.servicios.create') }}"
        class="inline-block mb-6 bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
        + Nuevo servicio
    </a>

    <div class="space-y-3">
        @forelse ($servicios as $servicio)
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4 flex items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-sm">{{ $servicio->nombre }}</p>
                    <p class="text-xs text-mate-tinta/60">
                        {{ $servicio->duracion_minutos }} min · ${{ number_format($servicio->precio, 2, ',', '.') }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $servicio->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $servicio->activo ? 'Activo' : 'Pausado' }}
                    </span>

                    <a href="{{ route('staff.empresa.servicios.edit', $servicio) }}" class="text-xs underline text-mate-tinta/70">
                        Editar
                    </a>

                    <form method="POST" action="{{ route('staff.empresa.servicios.alternar', $servicio) }}">
                        @csrf
                        <button type="submit" class="text-xs underline text-mate-tinta/70">
                            {{ $servicio->activo ? 'Pausar' : 'Activar' }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">Todavía no cargaste ningún servicio.</p>
        @endforelse
    </div>
</x-layouts.app>