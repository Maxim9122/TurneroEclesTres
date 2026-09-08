<x-layouts.app title="Productos - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Productos</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <a href="{{ route('staff.empresa.productos.create') }}"
        class="inline-block mb-6 bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
        + Nuevo producto
    </a>

    <div class="space-y-3">
        @forelse ($productos as $producto)
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    @if ($producto->foto_path)
                        <img src="{{ asset('storage/' . $producto->foto_path) }}" class="w-12 h-12 rounded-md object-cover">
                    @else
                        <div class="w-12 h-12 rounded-md bg-mate-borde"></div>
                    @endif
                    <div>
                        <p class="font-medium text-sm">{{ $producto->nombre }}</p>
                        <p class="text-xs text-mate-tinta/60">
                            ${{ number_format($producto->precio, 2, ',', '.') }} ·
                            Stock: {{ $producto->stock }}
                            @if ($producto->stock === 0)
                                <span class="text-red-700">(sin stock)</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-sm">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $producto->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $producto->activo ? 'Activo' : 'Pausado' }}
                    </span>

                    <a href="{{ route('staff.empresa.productos.edit', $producto) }}" class="underline text-mate-tinta/70">
                        Editar
                    </a>

                    <form method="POST" action="{{ route('staff.empresa.productos.alternar', $producto) }}">
                        @csrf
                        <button type="submit" class="underline text-mate-tinta/70">
                            {{ $producto->activo ? 'Pausar' : 'Activar' }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">Todavía no cargaste ningún producto.</p>
        @endforelse
    </div>
</x-layouts.app>