<x-layouts.app title="Clientes - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Clientes</h1>
        <a href="{{ route('staff.plataforma.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="GET" class="mb-6">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre o email..."
            class="w-full max-w-sm rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
    </form>

    <div class="space-y-2">
        @forelse ($clientes as $cliente)
            <div class="bg-mate-superficie border border-mate-borde rounded-md px-4 py-3 flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <p class="text-sm font-medium">{{ $cliente->nombre }}</p>
                    <p class="text-xs text-mate-tinta/60">{{ $cliente->email }} · {{ $cliente->telefono ?? 'sin teléfono' }}</p>
                </div>
                <a href="{{ route('staff.plataforma.clientes.recuperar', $cliente) }}" target="_blank"
                    class="text-xs bg-mate-salvia text-white rounded-md px-3 py-1.5">
                    Generar link
                </a>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No se encontraron clientes.</p>
        @endforelse
    </div>
</x-layouts.app>