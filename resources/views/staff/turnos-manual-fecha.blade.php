<x-layouts.app title="Agendar turno - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6 max-w-md mx-auto">
        <h1 class="font-display text-2xl">Agendar turno</h1>
        <a href="{{ route('staff.empresa.renovaciones.index') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <p class="text-sm text-mate-tinta/70 mb-6 max-w-md mx-auto">
        Turno para: <strong>{{ $cliente->nombre }}</strong>
        @if ($cliente->telefono) ({{ $cliente->telefono }}) @endif
    </p>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3 max-w-md mx-auto">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-mate-superficie border border-mate-borde rounded-lg p-5 max-w-md mx-auto"
        x-data="{
            servicioIds: [{{ $servicioPreseleccionado ?: '' }}],
            profesionalId: '',
            fecha: '',
            hora: '',
            slots: [],
            cargando: false,
            error: '',
            toggleServicio(id) {
                this.servicioIds.includes(id)
                    ? this.servicioIds = this.servicioIds.filter(s => s !== id)
                    : this.servicioIds.push(id);
                this.buscarHorarios();
            },
            buscarHorarios() {
                if (!this.fecha || this.servicioIds.length === 0) { this.slots = []; return; }
                this.cargando = true;
                this.hora = '';
                this.error = '';
                const params = new URLSearchParams();
                params.append('fecha', this.fecha);
                this.servicioIds.forEach(id => params.append('servicios[]', id));
                if (this.profesionalId) params.append('profesional', this.profesionalId);

                fetch('{{ route('publico.reserva.horarios', $empresa) }}?' + params.toString())
                    .then(r => r.json())
                    .then(data => { this.slots = data.slots; this.cargando = false; })
                    .catch(() => { this.error = 'No pudimos cargar los horarios. Probá de nuevo.'; this.cargando = false; });
            }
        }">

        <p class="text-sm font-medium mb-2">Servicios</p>
        <div class="space-y-2 mb-4">
            @foreach ($servicios as $servicio)
                <label class="flex items-center justify-between bg-white border border-mate-borde rounded-md px-3 py-2 text-sm cursor-pointer">
                    <span class="flex items-center gap-2">
                        <input type="checkbox" value="{{ $servicio->id }}"
                            @checked($servicio->id === $servicioPreseleccionado)
                            @change="toggleServicio({{ $servicio->id }})"
                            class="rounded border-mate-borde">
                        {{ $servicio->nombre }}
                        <span class="text-xs text-mate-tinta/50">({{ $servicio->duracion_minutos }} min)</span>
                    </span>
                    <span>${{ number_format($servicio->precio, 2, ',', '.') }}</span>
                </label>
            @endforeach
        </div>

        <p class="text-sm font-medium mb-2">Profesional (opcional)</p>
        <select x-model="profesionalId" @change="buscarHorarios()"
            class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm mb-4">
            <option value="">Sin preferencia</option>
            @foreach ($profesionales as $profesional)
                <option value="{{ $profesional->id }}">{{ $profesional->nombre }}</option>
            @endforeach
        </select>

        <label class="block text-sm mb-1">Fecha</label>
        <input type="date" x-model="fecha" @change="buscarHorarios()" min="{{ now()->toDateString() }}"
            class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm mb-4">

        <div x-show="cargando" class="text-sm text-mate-tinta/60 mb-4">Buscando horarios disponibles...</div>
        <div x-show="error" x-text="error" x-cloak class="text-sm text-red-700 mb-4"></div>
        <div x-show="fecha && !cargando && slots.length === 0 && !error && servicioIds.length > 0" x-cloak class="text-sm text-mate-tinta/60 mb-4">
            No hay horarios disponibles ese día. Probá con otra fecha.
        </div>

        <div class="grid grid-cols-3 gap-2 mb-6" x-show="slots.length > 0">
            <template x-for="slot in slots" :key="slot">
                <button type="button" @click="hora = slot"
                    class="text-sm py-2 rounded-md border"
                    :class="hora === slot ? 'bg-mate-salvia text-white border-mate-salvia' : 'border-mate-borde'"
                    x-text="slot"></button>
            </template>
        </div>

        <form method="POST" action="{{ route('staff.empresa.turnos.manual.confirmar') }}" x-show="hora" x-cloak>
            @csrf
            <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
            <input type="hidden" name="fecha" x-bind:value="fecha">
            <input type="hidden" name="hora" x-bind:value="hora">
            <template x-for="id in servicioIds" :key="id">
                <input type="hidden" name="servicios[]" x-bind:value="id">
            </template>
            <input type="hidden" name="profesional" x-bind:value="profesionalId">

            <button type="submit"
                class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
                Agendar turno <span x-text="hora"></span>
            </button>
        </form>
    </div>
</x-layouts.app>