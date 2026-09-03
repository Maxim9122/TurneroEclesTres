<x-layouts.app title="Horario general - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Horario general del negocio</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <p class="text-sm text-mate-tinta/70 mb-6">
        Este horario se usa automáticamente para cualquier profesional que no tenga cargado un horario propio.
    </p>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="space-y-4 mb-8">
        @foreach ($dias as $numero => $nombre)
            @php $horariosDia = $horarios->where('dia_semana', $numero); @endphp
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4">
                <p class="font-medium text-sm mb-2">{{ $nombre }}</p>

                @forelse ($horariosDia as $horario)
                    <div class="flex items-center justify-between text-sm py-1">
                        <span>{{ substr($horario->hora_inicio, 0, 5) }} — {{ substr($horario->hora_fin, 0, 5) }}</span>
                        <form method="POST" action="{{ route('staff.empresa.horario-general.destroy', $horario) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs underline text-red-700">Quitar</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-mate-tinta/50">Sin atención este día.</p>
                @endforelse
            </div>
        @endforeach
    </div>

    <div class="bg-mate-superficie border border-mate-borde rounded-lg p-5 max-w-md">
        <p class="font-medium text-sm mb-3">Agregar rango horario</p>
        <form method="POST" action="{{ route('staff.empresa.horario-general.store') }}" class="space-y-3">
            @csrf

            <div>
                <label class="block text-sm mb-2">Días</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($dias as $numero => $nombre)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="dias[]" value="{{ $numero }}" class="rounded border-mate-borde">
                            {{ $nombre }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm mb-1">Desde</label>
                    <input type="time" name="hora_inicio" required class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm mb-1">Hasta</label>
                    <input type="time" name="hora_fin" required class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                </div>
            </div>

            <p class="text-xs text-mate-tinta/50">
                Se va a cargar el mismo rango horario para todos los días que marques. Para un día con corte al mediodía, cargalo primero acá y después agregá el segundo rango marcando solo ese día.
            </p>

            <button type="submit"
                class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
                Agregar
            </button>
        </form>
    </div>
</x-layouts.app>