<x-layouts.app title="Turnos - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Turnos</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route('staff.empresa.turnos.index', ['fecha' => $semana['semana_anterior']]) }}"
                class="text-sm text-mate-salvia">&larr; Semana anterior</a>
            <a href="{{ route('staff.empresa.turnos.index', ['fecha' => $semana['semana_siguiente']]) }}"
                class="text-sm text-mate-salvia">Semana siguiente &rarr;</a>
        </div>

        <div class="grid grid-cols-7 gap-1.5">
            @foreach ($semana['dias'] as $dia)
                <a href="{{ route('staff.empresa.turnos.index', ['fecha' => $dia['fecha']]) }}"
                    class="relative flex flex-col items-center rounded-md border py-2 text-xs
                        {{ $dia['fecha'] === $fecha ? 'bg-mate-salvia text-white border-mate-salvia' : 'bg-mate-superficie border-mate-borde' }}
                        {{ $dia['fecha'] === now()->toDateString() && $dia['fecha'] !== $fecha ? 'ring-1 ring-mate-arcilla' : '' }}">
                    <span class="uppercase">{{ $dia['nombre_corto'] }}</span>
                    <span class="font-medium text-sm">{{ $dia['numero'] }}</span>

                    @if ($dia['pendientes'] > 0)
                        <span class="absolute -top-1.5 -right-1.5 bg-mate-arcilla text-white text-[10px] font-medium rounded-full w-4 h-4 flex items-center justify-center">
                            {{ $dia['pendientes'] }}
                        </span>
                    @endif

                    @if ($dia['total'] > 0)
                        <span class="absolute -bottom-1.5 -right-1.5 text-[10px] font-semibold text-mate-arcilla bg-white rounded-full w-4 h-4 flex items-center justify-center border border-mate-arcilla">
                            {{ $dia['total'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('staff.empresa.turnos.index') }}" class="mt-3">
            <label class="block text-sm mb-1">O elegí otra fecha</label>
            <input type="date" name="fecha" value="{{ $fecha }}" onchange="this.form.submit()"
                class="rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
        </form>
    </div>

    <div class="space-y-3" x-data="{
        modalAbierto: false, formPendiente: null, mensajePendiente: '',
        modalRapidoAbierto: false,
        rapido: {
            clienteId: '', clienteNombre: '', esNuevo: false,
            busqueda: '', resultados: [], servicioIds: [],
            profesional: '', enviando: false, error: '',
            indiceActivo: -1,
            buscarCliente() {
                this.indiceActivo = -1;
                if (this.busqueda.length < 2) { this.resultados = []; return; }
                fetch('{{ route('staff.empresa.clientes.buscar') }}?q=' + encodeURIComponent(this.busqueda))
                    .then(r => r.json())
                    .then(data => { this.resultados = data; });
            },
            moverSeleccion(direccion) {
                if (this.resultados.length === 0) return;
                this.indiceActivo = (this.indiceActivo + direccion + this.resultados.length) % this.resultados.length;
            },
            confirmarSeleccion() {
                if (this.indiceActivo >= 0 && this.resultados[this.indiceActivo]) {
                    this.elegirCliente(this.resultados[this.indiceActivo]);
                }
            },
            elegirCliente(cliente) {
                this.clienteId = cliente.id;
                this.clienteNombre = cliente.nombre + (cliente.telefono ? ' (' + cliente.telefono + ')' : '');
                this.busqueda = ''; this.resultados = []; this.esNuevo = false; this.indiceActivo = -1;
            },
            marcarNuevo() {
                this.clienteId = ''; this.clienteNombre = ''; this.esNuevo = true; this.resultados = [];
            },
            toggleServicio(id) {
                this.servicioIds.includes(id)
                    ? this.servicioIds = this.servicioIds.filter(s => s !== id)
                    : this.servicioIds.push(id);
            },
            reset() {
                this.clienteId = ''; this.clienteNombre = ''; this.esNuevo = false;
                this.busqueda = ''; this.resultados = []; this.servicioIds = [];
                this.profesional = ''; this.error = '';
            },
            enviar(event) {
                this.enviando = true;
                this.error = '';
                const form = event.target;
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData,
                })
                .then(async (r) => {
                    const data = await r.json();
                    if (!r.ok) throw new Error(data.error || 'Ocurrió un error.');
                    return data;
                })
                .then(() => {
                    this.enviando = false;
                    this.modalRapidoAbierto = false;
                    this.reset();
                    window.location.reload();
                })
                .catch((err) => {
                    this.enviando = false;
                    this.error = err.message;
                });
            }
        }
    }">
        <button type="button" @click="modalRapidoAbierto = true"
            class="inline-block mb-6 bg-mate-arcilla hover:opacity-90 text-white rounded-md px-4 py-2.5 text-sm font-medium">
            + Orden de llegada (cliente sin turno)
        </button>

        @forelse ($turnos as $turno)
            <div class="bg-mate-superficie border rounded-lg p-4
                @if ($destacar && (int) $destacar === $turno->id)
                    border-mate-salvia ring-2 ring-mate-salvia
                @else
                    border-mate-borde
                @endif
            ">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div>
                        <p class="font-medium text-sm">
                            #{{ $turno->id }} · {{ substr($turno->hora_inicio, 0, 5) }} - {{ substr($turno->hora_fin, 0, 5) }}
                            · {{ $turno->cliente->nombre }}
                        </p>
                        <p class="text-xs text-mate-tinta/60 mt-0.5">
                            {{ $turno->servicios->pluck('nombre')->implode(' + ') }}
                            · ${{ number_format($turno->precioTotal(), 2, ',', '.') }}
                        </p>
                        <p class="text-xs text-mate-tinta/60">
                            Profesional:
                            <strong>{{ $turno->profesional->nombre ?? 'Sin asignar' }}</strong>
                        </p>
                    </div>

                    <span class="text-xs px-2 py-0.5 rounded-full h-fit
                        @class([
                            'bg-yellow-100 text-yellow-800' => $turno->estado === 'pendiente',
                            'bg-green-100 text-green-800' => $turno->estado === 'confirmado',
                            'bg-blue-100 text-blue-800' => $turno->estado === 'completado',
                            'bg-red-100 text-red-800' => in_array($turno->estado, ['cancelado', 'no_show']),
                        ])">
                        {{ ucfirst(str_replace('_', ' ', $turno->estado)) }}
                    </span>
                </div>

                <div class="flex flex-wrap gap-2 mt-3 text-sm">
                    @php
                        $mensajeTurno = "Hola {$turno->cliente->nombre}! Te confirmamos tu turno en {$turno->empresa->nombre} (EclesTres) "
                            . "para el {$turno->fecha->format('d/m/Y')} a las " . substr($turno->hora_inicio, 0, 5) . "hs. "
                            . "Servicios: " . $turno->servicios->pluck('nombre')->implode(' + ') . ". ¡Te esperamos!";
                        $linkWhatsapp = \App\Support\WhatsApp::linkChat($turno->cliente->telefono, $mensajeTurno);
                    @endphp

                    @if ($linkWhatsapp)
                        <a href="{{ $linkWhatsapp }}" target="_blank" class="underline text-green-700">
                            📱 Enviar WhatsApp
                        </a>
                    @else
                        <span class="text-mate-tinta/40 text-xs">Cliente sin teléfono cargado</span>
                    @endif

                    @unless (in_array($turno->estado, ['cancelado', 'no_show']))
                        <a href="{{ route('staff.empresa.turnos.remito', $turno) }}" target="_blank" class="underline text-mate-salvia">
                            📄 Enviar comprobante
                        </a>

                        <a href="{{ route('staff.empresa.turnos.edit', $turno) }}" class="underline text-mate-tinta/70">
                            Editar profesional/servicios
                        </a>
                    @endunless

                    @if ($turno->estado === 'pendiente')
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="confirmado">
                            <button type="button" class="underline text-green-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Confirmar este turno?'">
                                Confirmar
                            </button>
                        </form>
                    @endif

                    @if (in_array($turno->estado, ['pendiente', 'confirmado']))
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="cancelado">
                            <button type="button" class="underline text-red-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Cancelar este turno? Esta acción no se puede deshacer.'">
                                Cancelar
                            </button>
                        </form>
                    @endif

                    @if ($turno->estado === 'confirmado')
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="completado">
                            <button type="button" class="underline text-blue-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Marcar este turno como completado?'">
                                Completado
                            </button>
                        </form>
                        <form method="POST" action="{{ route('staff.empresa.turnos.estado', $turno) }}">
                            @csrf
                            <input type="hidden" name="estado" value="no_show">
                            <button type="button" class="underline text-red-700"
                                @click="modalAbierto = true; formPendiente = $el.closest('form'); mensajePendiente = '¿Marcar que el cliente no asistió?'">
                                No asistió
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No hay turnos para esta fecha.</p>
        @endforelse

        {{-- Modal de confirmación de cambio de estado --}}
        <div x-show="modalAbierto" x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50"
            style="display: none;">
            <div @click.outside="modalAbierto = false" class="bg-white rounded-lg max-w-sm w-full p-5">
                <p class="text-sm mb-5" x-text="mensajePendiente"></p>
                <div class="flex gap-2">
                    <button type="button" @click="modalAbierto = false"
                        class="flex-1 border border-mate-borde rounded-md py-2 text-sm font-medium">
                        Cancelar
                    </button>
                    <button type="button" @click="modalAbierto = false; $nextTick(() => formPendiente.submit())"
                        class="flex-1 bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2 text-sm font-medium">
                        Sí, confirmar
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal de orden de llegada --}}
        <div x-show="modalRapidoAbierto" x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50"
            style="display: none;">
            <div @click.outside="modalRapidoAbierto = false" class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto p-5">
                <div class="flex items-center justify-between mb-4">
                    <p class="font-medium text-sm">Orden de llegada</p>
                    <button type="button" @click="modalRapidoAbierto = false" class="text-mate-tinta/50 text-xl leading-none">&times;</button>
                </div>

                <p class="text-xs text-mate-tinta/60 mb-4">Para clientes que llegan sin turno y se atienden en el momento.</p>

                <div x-show="rapido.error" x-cloak class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3" x-text="rapido.error"></div>

                <form @submit.prevent="rapido.enviar($event)" action="{{ route('staff.empresa.turnos.rapido.confirmar') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm mb-1">Cliente</label>

                        <template x-if="rapido.clienteId && !rapido.esNuevo">
                            <div class="flex items-center justify-between bg-mate-fondo border border-mate-borde rounded-md px-3 py-2 text-sm">
                                <span x-text="rapido.clienteNombre"></span>
                                <button type="button" @click="rapido.clienteId = ''; rapido.clienteNombre = ''" class="text-xs text-red-600">Cambiar</button>
                            </div>
                        </template>

                        <template x-if="!rapido.clienteId && !rapido.esNuevo">
                            <div class="relative">
                                <input type="text" x-model="rapido.busqueda" @input="rapido.buscarCliente()"
                                    @keydown.down.prevent="rapido.moverSeleccion(1)"
                                    @keydown.up.prevent="rapido.moverSeleccion(-1)"
                                    @keydown.enter.prevent="rapido.confirmarSeleccion()"
                                    @keydown.escape="rapido.resultados = []; rapido.indiceActivo = -1"
                                    placeholder="Buscar por nombre o teléfono..."
                                    class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">

                                <div x-show="rapido.resultados.length > 0" x-cloak
                                    class="absolute z-10 w-full bg-white border border-mate-borde rounded-md mt-1 shadow-lg max-h-32 overflow-y-auto">
                                    <template x-for="(cliente, i) in rapido.resultados" :key="cliente.id">
                                        <button type="button" @click="rapido.elegirCliente(cliente)" @mouseenter="rapido.indiceActivo = i"
                                            class="w-full text-left px-3 py-2 text-sm flex justify-between"
                                            :class="rapido.indiceActivo === i ? 'bg-mate-salvia text-white' : 'hover:bg-mate-fondo'">
                                            <span x-text="cliente.nombre"></span>
                                            <span class="text-xs" :class="rapido.indiceActivo === i ? 'text-white/80' : 'text-mate-tinta/50'"
                                                x-text="cliente.telefono"></span>
                                        </button>
                                    </template>
                                </div>

                                <button type="button" @click="rapido.marcarNuevo()" class="text-xs text-mate-salvia mt-1.5 underline">
                                    + Es un cliente nuevo
                                </button>
                            </div>
                        </template>

                        <template x-if="rapido.esNuevo">
                            <div class="space-y-2">
                                <input type="text" name="cliente_nombre_nuevo" placeholder="Nombre del cliente"
                                    class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                                <input type="tel" name="cliente_telefono_nuevo" placeholder="Teléfono (opcional)"
                                    class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                                <button type="button" @click="rapido.esNuevo = false" class="text-xs text-mate-tinta/60 underline">
                                    Buscar cliente existente en su lugar
                                </button>
                            </div>
                        </template>

                        <input type="hidden" name="cliente_id" x-bind:value="rapido.clienteId">
                    </div>

                    <div>
                        <p class="text-sm font-medium mb-2">Servicios</p>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            @foreach (Auth::guard('web')->user()->empresa->servicios()->where('activo', true)->orderBy('nombre')->get() as $servicio)
                                <label class="flex items-center justify-between bg-white border border-mate-borde rounded-md px-3 py-2 text-sm cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <input type="checkbox" name="servicios[]" value="{{ $servicio->id }}"
                                            @change="rapido.toggleServicio({{ $servicio->id }})" class="rounded border-mate-borde">
                                        {{ $servicio->nombre }}
                                    </span>
                                    <span>${{ number_format($servicio->precio, 2, ',', '.') }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Profesional (opcional)</label>
                        <select name="profesional" x-model="rapido.profesional" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                            <option value="">Sin preferencia</option>
                            @foreach (Auth::guard('web')->user()->empresa->profesionales()->where('activo', true)->orderBy('nombre')->get() as $profesional)
                                <option value="{{ $profesional->id }}">{{ $profesional->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" :disabled="rapido.enviando"
                        class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro disabled:opacity-60 text-white rounded-md py-2.5 text-sm font-medium">
                        <span x-show="!rapido.enviando">Registrar llegada</span>
                        <span x-show="rapido.enviando" x-cloak>Registrando...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>