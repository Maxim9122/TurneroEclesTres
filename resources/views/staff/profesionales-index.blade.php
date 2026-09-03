<x-layouts.app title="Profesionales - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Profesionales</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <a href="{{ route('staff.empresa.profesionales.create') }}"
        class="inline-block mb-6 bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
        + Nuevo profesional
    </a>

    <div class="space-y-3">
        @forelse ($profesionales as $profesional)
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    @if ($profesional->foto_path)
                        <img src="{{ asset('storage/' . $profesional->foto_path) }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 rounded-full bg-mate-borde"></div>
                    @endif
                    <div>
                        <p class="font-medium text-sm">{{ $profesional->nombre }}</p>
                        <p class="text-xs text-mate-tinta/60">
                            {{ $profesional->usuario ? 'Vinculado a ' . $profesional->usuario->nombre : 'Sin usuario del sistema' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-sm">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $profesional->activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $profesional->activo ? 'Activo' : 'Baja' }}
                    </span>

                    <a href="{{ route('staff.empresa.profesionales.horarios.edit', $profesional) }}" class="underline text-mate-tinta/70">
                        Horarios
                    </a>
                    <a href="{{ route('staff.empresa.profesionales.edit', $profesional) }}" class="underline text-mate-tinta/70">
                        Editar
                    </a>
                    <form method="POST" action="{{ route('staff.empresa.profesionales.alternar', $profesional) }}">
                        @csrf
                        <button type="submit" class="underline text-mate-tinta/70">
                            {{ $profesional->activo ? 'Dar de baja' : 'Reactivar' }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">Todavía no cargaste ningún profesional.</p>
        @endforelse
    </div>
</x-layouts.app>