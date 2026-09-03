@if ($errors->any())
    <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <label class="block text-sm mb-1">Nombre del servicio</label>
    <input type="text" name="nombre" required value="{{ old('nombre', $servicio->nombre ?? '') }}"
        placeholder="Ej: Degradé, Barba, Corte clásico, Mechas..."
        class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm mb-1">Duración (minutos)</label>
        <input type="number" name="duracion_minutos" min="5" step="5" required
            value="{{ old('duracion_minutos', $servicio->duracion_minutos ?? 30) }}"
            class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
    </div>
    <div>
        <label class="block text-sm mb-1">Precio</label>
        <input type="number" name="precio" min="0" step="0.01" required
            value="{{ old('precio', $servicio->precio ?? '') }}"
            class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
    </div>
</div>

<p class="text-xs text-mate-tinta/50">
    Si un cliente quiere "Degradé + Barba", va a poder elegir ambos servicios por separado al reservar — no hace falta cargarlos como combo.
</p>