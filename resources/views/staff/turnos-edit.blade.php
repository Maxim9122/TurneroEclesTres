<x-layouts.app title="Editar turno - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Editar turno</h1>
        <a href="{{ route('staff.empresa.turnos.index', ['fecha' => $turno->fecha->toDateString()]) }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <p class="text-sm text-mate-tinta/70 mb-6">
        {{ $turno->cliente->nombre }} · {{ $turno->fecha->format('d/m/Y') }} · {{ substr($turno->hora_inicio, 0, 5) }}hs
    </p>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('staff.empresa.turnos.update', $turno) }}"
        class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-5 max-w-md">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm mb-1">Profesional</label>
            <select name="profesional_id" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                <option value="">Sin asignar</option>
                @foreach ($profesionales as $profesional)
                    <option value="{{ $profesional->id }}" @selected(old('profesional_id', $turno->profesional_id) == $profesional->id)>
                        {{ $profesional->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm mb-2">Servicios</label>
            <x-servicios-selector :servicios="$servicios"
                :seleccionados="old('servicios', $serviciosSeleccionados)"
                :adicionales-seleccionados="old('adicionales', $adicionalesSeleccionados)"
                :editar-precios="true"
                :precios="old('precios_adicionales', $preciosAdicionales)" />
            <p class="text-xs text-mate-tinta/50 mt-2">
                El horario de fin se recalcula automáticamente según los servicios y adicionales que dejes marcados.
                Lo que ya tenía el turno mantiene el precio con el que se reservó.
            </p>
            <p class="text-xs text-mate-tinta/50 mt-1">
                El precio de los adicionales es aproximado: una vez acordado con el cliente según la complejidad,
                cargá el <strong>precio final</strong> y se actualizan el total, el comprobante y la comisión.
            </p>
        </div>

        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            Guardar cambios
        </button>
    </form>
</x-layouts.app>