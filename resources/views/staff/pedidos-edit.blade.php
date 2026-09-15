<x-layouts.app title="Editar venta - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6 max-w-md mx-auto">
        <h1 class="font-display text-2xl">Editar venta #{{ $pedido->id }}</h1>
        <a href="{{ route('staff.empresa.pedidos.index') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <p class="text-sm text-mate-tinta/70 mb-6 max-w-md mx-auto">
        Cliente: <strong>{{ $pedido->cliente->nombre }}</strong>
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

    <form method="POST" action="{{ route('staff.empresa.pedidos.update', $pedido) }}"
        class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-5 max-w-md mx-auto"
        x-data="{ metodo: '{{ old('metodo_entrega', $pedido->metodo_entrega) }}' }">
        @csrf
        @method('PUT')

        <div x-data="{
                items: {{ $pedido->items->map(fn ($i) => ['producto_id' => $i->producto_id, 'nombre' => $i->producto->nombre, 'precio' => (float) $i->precio_al_momento, 'cantidad' => $i->cantidad])->values()->toJson() }},
                busqueda: '',
                resultados: [],
                indiceActivo: -1,
                buscar() {
                    this.indiceActivo = -1;
                    if (this.busqueda.length < 2) { this.resultados = []; return; }
                    fetch('{{ route('staff.empresa.productos.buscar') }}?q=' + encodeURIComponent(this.busqueda))
                        .then(r => r.json())
                        .then(data => { this.resultados = data; });
                },
                moverSeleccion(direccion) {
                    if (this.resultados.length === 0) return;
                    this.indiceActivo = (this.indiceActivo + direccion + this.resultados.length) % this.resultados.length;
                },
                confirmarSeleccion() {
                    if (this.indiceActivo >= 0 && this.resultados[this.indiceActivo]) {
                        this.agregar(this.resultados[this.indiceActivo]);
                    }
                },
                agregar(producto) {
                    const existente = this.items.find(i => i.producto_id === producto.id);
                    if (existente) {
                        existente.cantidad++;
                    } else {
                        this.items.push({ producto_id: producto.id, nombre: producto.nombre, precio: producto.precio, cantidad: 1 });
                    }
                    this.busqueda = '';
                    this.resultados = [];
                    this.indiceActivo = -1;
                },
                quitar(index) {
                    this.items.splice(index, 1);
                }
            }">
                <p class="text-sm font-medium mb-2">Productos</p>

                <div class="relative mb-3">
                    <input type="text" x-model="busqueda" @input="buscar()"
                        @keydown.down.prevent="moverSeleccion(1)"
                        @keydown.up.prevent="moverSeleccion(-1)"
                        @keydown.enter.prevent="confirmarSeleccion()"
                        @keydown.escape="resultados = []; indiceActivo = -1"
                        placeholder="Buscar producto por nombre..."
                        class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">

                    <div x-show="resultados.length > 0" x-cloak
                        class="absolute z-10 w-full bg-white border border-mate-borde rounded-md mt-1 shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="(producto, i) in resultados" :key="producto.id">
                            <button type="button" @click="agregar(producto)" @mouseenter="indiceActivo = i"
                                class="w-full text-left px-3 py-2 text-sm flex justify-between"
                                :class="indiceActivo === i ? 'bg-mate-salvia text-white' : 'hover:bg-mate-fondo'">
                                <span x-text="producto.nombre"></span>
                                <span class="text-xs" :class="indiceActivo === i ? 'text-white/80' : 'text-mate-tinta/50'"
                                    x-text="'$' + producto.precio + ' · stock: ' + producto.stock"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="space-y-2">
                    <template x-for="(item, index) in items" :key="item.producto_id">
                        <div class="flex items-center justify-between bg-white border border-mate-borde rounded-md px-3 py-2 text-sm">
                            <span class="flex-1" x-text="item.nombre"></span>
                            <span class="text-xs text-mate-tinta/50 mr-2" x-text="'$' + item.precio"></span>
                            <input type="number" min="1" x-model.number="item.cantidad"
                                :name="'productos[' + item.producto_id + ']'"
                                class="w-16 rounded-md border border-mate-borde px-2 py-1 text-sm">
                            <button type="button" @click="quitar(index)" class="ml-2 text-red-600 text-xs">Quitar</button>
                        </div>
                    </template>

                    <p x-show="items.length === 0" class="text-xs text-mate-tinta/50">Buscá y agregá al menos un producto.</p>
                </div>
            </div>

        <div>
            <label class="block text-sm mb-2">Entrega</label>
            <div class="flex gap-3">
                <label class="flex-1 flex items-center gap-2 border border-mate-borde rounded-md px-3 py-2 text-sm cursor-pointer">
                    <input type="radio" name="metodo_entrega" value="retiro" x-model="metodo">
                    Retiro
                </label>
                <label class="flex-1 flex items-center gap-2 border border-mate-borde rounded-md px-3 py-2 text-sm cursor-pointer">
                    <input type="radio" name="metodo_entrega" value="envio" x-model="metodo">
                    Envío
                </label>
            </div>
        </div>

        <div x-show="metodo === 'envio'" x-cloak class="space-y-3">
            @if ($direcciones->isNotEmpty())
                <div>
                    <label class="block text-sm mb-1">Dirección</label>
                    <select name="direccion_envio_id" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                        <option value="">Usar una dirección nueva</option>
                        @foreach ($direcciones as $direccion)
                            <option value="{{ $direccion->id }}" @selected($pedido->direccion_envio_id == $direccion->id)>
                                {{ $direccion->calle }} {{ $direccion->altura }}, {{ $direccion->barrio }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="space-y-3">
                <div>
                    <label class="block text-sm mb-1">Barrio (si es dirección nueva)</label>
                    <input type="text" name="nueva_barrio" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-sm mb-1">Calle</label>
                        <input type="text" name="nueva_calle" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Altura</label>
                        <input type="text" name="nueva_altura" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
                    </div>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm mb-1">Notas (opcional)</label>
            <textarea name="notas" rows="2" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">{{ old('notas', $pedido->notas) }}</textarea>
        </div>

        <div>
            <label class="block text-sm mb-1">Motivo de la modificación <span class="text-red-600">*</span></label>
            <textarea name="motivo" rows="2" required placeholder="Ej: El cliente pidió cambiar un producto por otro"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">{{ old('motivo') }}</textarea>
            <p class="text-xs text-mate-tinta/50 mt-1">Este motivo queda guardado en el historial de la venta.</p>
        </div>

        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            Guardar cambios
        </button>
    </form>
</x-layouts.app>