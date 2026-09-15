<x-layouts.app title="Orden de llegada - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6 max-w-md mx-auto">
        <h1 class="font-display text-2xl">Orden de llegada</h1>
        <a href="{{ route('staff.empresa.turnos.index') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <p class="text-sm text-mate-tinta/70 mb-6 max-w-md mx-auto">
        Para clientes que llegan sin turno y se atienden en el momento.
    </p>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3 max-w-md mx-auto">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('staff.empresa.turnos.rapido.confirmar') }}"
        class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-5 max-w-md mx-auto"
        x-data="{
            clienteId: '',
            clienteNombre: '',
            esNuevo: false,
            busqueda: '',
            resultados: [],
            servicioIds: [],
            buscarCliente() {
                if (this.busqueda.length < 2) { this.resultados = []; return; }
                fetch('{{ route('staff.empresa.clientes.buscar') }}?q=' + encodeURIComponent(this.busqueda))
                    .then(r => r.json())
                    .then(data => { this.resultados = data; });
            },
            elegirCliente(cliente) {
                this.clienteId = cliente.id;
                this.clienteNombre = cliente.nombre + (cliente.telefono ? ' (' + cliente.telefono + ')' : '');
                this.busqueda = '';
                this.resultados = [];
                this.esNuevo = false;
            },
            marcarNuevo() {
                this.clienteId = '';
                this.clienteNombre = '';
                this.esNuevo = true;
                this.resultados = [];
            },
            toggleServicio(id) {
                this.servicioIds.includes(id)
                    ? this.servicioIds = this.servicioIds.filter(s => s !== id)
                    : this.servicioIds.push(id);
            }
        }">
        @csrf

        <div>
            <label class="block text-sm mb-1">Cliente</label>

            <template x-if="clienteId && !esNuevo">
                <div class="flex items-center justify-between bg-white border border-mate-borde rounded-md px-3 py-2 text-sm">
                    <span x-text="clienteNombre"></span>
                    <button type="button" @click="clienteId = ''; clienteNombre = ''" class="text-xs text-red-600">Cambiar</button>
                </div>
            </template>

            <template x-if="!clienteId && !esNuevo">
                <div class="relative">
                    <input type="text" x-model="busqueda" @input="buscarCliente()"
                        placeholder="Buscar por nombre o teléfono..."
                        class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">

                    <div x-show="resultados.length > 0" x-cloak
                        class="absolute z-10 w-full bg-white border border-mate-borde rounded-md mt-1 shadow-lg max-h-40 overflow-y-auto">
                        <template x-for="cliente in resultados" :key="cliente.id">
                            <button type="button" @click="elegirCliente(cliente)"
                                class="w-full text-left px-3 py-2 text-sm hover:bg-mate-fondo flex justify-between">
                                <span x-text="cliente.nombre"></span>
                                <span class="text-xs text-mate-tinta/50" x-text="cliente.telefono"></span>
                            </button>
                        </template>
                    </div>

                    <button type="button" @click="marcarNuevo()" class="text-xs text-mate-salvia mt-1.5 underline">
                        + Es un cliente nuevo
                    </button>
                </div>
            </template>

            <template x-if="esNuevo">
                <div class="space-y-2">
                    <input type="text" name="cliente_nombre_nuevo" placeholder="Nombre del cliente" required
                        class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                    <input type="tel" name="cliente_telefono_nuevo" placeholder="Teléfono (opcional)"
                        class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                    <button type="button" @click="esNuevo = false" class="text-xs text-mate-tinta/60 underline">
                        Buscar cliente existente en su lugar
                    </button>
                </div>
            </template>

            <input type="hidden" name="cliente_id" x-bind:value="clienteId">
        </div>

        <div>
            <p class="text-sm font-medium mb-2">Servicios</p>
            <div class="space-y-2">
                @foreach ($servicios as $servicio)
                    <label class="flex items-center justify-between bg-white border border-mate-borde rounded-md px-3 py-2 text-sm cursor-pointer">
                        <span class="flex items-center gap-2">
                            <input type="checkbox" name="servicios[]" value="{{ $servicio->id }}"
                                @change="toggleServicio({{ $servicio->id }})" class="rounded border-mate-borde">
                            {{ $servicio->nombre }}
                            <span class="text-xs text-mate-tinta/50">({{ $servicio->duracion_minutos }} min)</span>
                        </span>
                        <span>${{ number_format($servicio->precio, 2, ',', '.') }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-sm mb-1">Profesional (opcional)</label>
            <select name="profesional" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                <option value="">Sin preferencia</option>
                @foreach ($profesionales as $profesional)
                    <option value="{{ $profesional->id }}">{{ $profesional->nombre }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            Registrar llegada
        </button>
    </form>
</x-layouts.app>