<x-layouts.app title="Empresas - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Empresas registradas</h1>
        <a href="{{ route('staff.plataforma.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-wrap gap-2 mb-6 text-sm">
        <a href="{{ route('staff.plataforma.empresas.index') }}"
            class="px-3 py-1.5 rounded-full border {{ !$estado ? 'bg-mate-salvia text-white border-mate-salvia' : 'border-mate-borde' }}">
            Todas
        </a>
        @foreach (['pendiente' => 'Pendientes', 'activa' => 'Activas', 'suspendida' => 'Suspendidas', 'rechazada' => 'Rechazadas'] as $key => $label)
            <a href="{{ route('staff.plataforma.empresas.index', ['estado' => $key]) }}"
                class="px-3 py-1.5 rounded-full border {{ $estado === $key ? 'bg-mate-salvia text-white border-mate-salvia' : 'border-mate-borde' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($empresas as $empresa)
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="font-medium">{{ $empresa->nombre }}</p>
                    <p class="text-xs text-mate-tinta/60">
                        {{ ucfirst($empresa->rubro) }} ·
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs
                            @class([
                                'bg-yellow-100 text-yellow-800' => $empresa->estado === 'pendiente',
                                'bg-green-100 text-green-800' => $empresa->estado === 'activa',
                                'bg-red-100 text-red-800' => $empresa->estado === 'suspendida',
                                'bg-gray-100 text-gray-700' => in_array($empresa->estado, ['rechazada', 'cancelada']),
                            ])">
                            {{ ucfirst($empresa->estado) }}
                        </span>
                    </p>
                </div>

                <div class="flex gap-2 text-sm">
                    @if ($empresa->estado === 'pendiente')
                        <form method="POST" action="{{ route('staff.plataforma.empresas.activar', $empresa) }}">
                            @csrf
                            <button class="px-3 py-1.5 rounded-md bg-mate-salvia text-white">Activar</button>
                        </form>
                        <form method="POST" action="{{ route('staff.plataforma.empresas.rechazar', $empresa) }}">
                            @csrf
                            <button class="px-3 py-1.5 rounded-md border border-mate-borde">Rechazar</button>
                        </form>
                    @elseif ($empresa->estado === 'activa')
                        <form method="POST" action="{{ route('staff.plataforma.empresas.suspender', $empresa) }}">
                            @csrf
                            <button class="px-3 py-1.5 rounded-md border border-red-300 text-red-700">Suspender</button>
                        </form>
                    @elseif ($empresa->estado === 'suspendida')
                        <form method="POST" action="{{ route('staff.plataforma.empresas.reactivar', $empresa) }}">
                            @csrf
                            <button class="px-3 py-1.5 rounded-md bg-mate-salvia text-white">Reactivar</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No hay empresas en este estado.</p>
        @endforelse
    </div>
</x-layouts.app>