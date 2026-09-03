<x-layouts.app title="Operadores - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Operadores</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <a href="{{ route('staff.empresa.operadores.create') }}"
        class="inline-block mb-6 bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
        + Nuevo operador
    </a>

    <div class="space-y-3">
        @forelse ($operadores as $operador)
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4 flex items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-sm">{{ $operador->nombre }}</p>
                    <p class="text-xs text-mate-tinta/60">{{ $operador->email }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $operador->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $operador->activo ? 'Activo' : 'Inactivo' }}
                    </span>

                    <form method="POST" action="{{ route('staff.empresa.operadores.alternar', $operador) }}">
                        @csrf
                        <button type="submit" class="text-xs underline text-mate-tinta/70">
                            {{ $operador->activo ? 'Desactivar' : 'Activar' }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">Todavía no cargaste ningún operador.</p>
        @endforelse
    </div>
</x-layouts.app>