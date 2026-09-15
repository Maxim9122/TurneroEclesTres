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
    @php
        $rubroServicio = \Illuminate\Support\Facades\Auth::guard('web')->user()->empresa->rubro;
        $placeholderNombre = match ($rubroServicio) {
            'peluqueria', 'barberia' => 'Ej: Degradé, Barba, Corte clásico, Mechas...',
            'estetica', 'unas' => 'Ej: Manicura, Pedicura, Limpieza facial...',
            'salud' => 'Ej: Consulta general, Control, Limpieza dental...',
            'especialista' => 'Ej: Sesión de masajes, Evaluación kinesiológica...',
            default => 'Ej: Nombre del servicio que ofrecés...',
        };
    @endphp
    <input type="text" name="nombre" required value="{{ old('nombre', $servicio->nombre ?? '') }}"
        placeholder="{{ $placeholderNombre }}"
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

<div>
    <label class="block text-sm mb-1">Días para renovar (opcional)</label>
    <input type="number" name="dias_renovacion" min="1"
        value="{{ old('dias_renovacion', $servicio->dias_renovacion ?? '') }}"
        placeholder="Ej: 20 para una tintura"
        class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
    <p class="text-xs text-mate-tinta/50 mt-1">
        Si lo cargás, el sistema va a avisar cuándo el cliente debería volver a hacerse este servicio.
    </p>
</div>

<div>
    <label class="block text-sm mb-1">Foto principal (opcional)</label>
    <input type="file" name="foto" accept="image/*"
        class="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-mate-salvia file:text-white text-mate-tinta/70">
    @isset($servicio)
        @if ($servicio->foto_path)
            <img src="{{ asset('storage/' . $servicio->foto_path) }}" class="w-16 h-16 rounded-md object-cover mt-2">
        @endif
    @endisset
</div>

<div>
    <label class="block text-sm mb-1">Fotos adicionales (opcional, hasta 2 más)</label>
    <input type="file" name="imagenes_adicionales[]" accept="image/*" multiple
        class="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-mate-salvia file:text-white text-mate-tinta/70">
    <p class="text-xs text-mate-tinta/50 mt-1">El servicio puede tener hasta 3 fotos en total.</p>

    @isset($servicio)
        @if ($servicio->imagenes->isNotEmpty())
            <div class="flex gap-2 mt-2">
                @foreach ($servicio->imagenes as $imagen)
                    <div class="relative">
                        <img src="{{ asset('storage/' . $imagen->path) }}" class="w-16 h-16 rounded-md object-cover">
                        <button type="submit" form="eliminar-imagen-serv-{{ $imagen->id }}"
                            class="absolute -top-1.5 -right-1.5 bg-red-600 text-white text-xs w-5 h-5 rounded-full">×</button>
                    </div>
                @endforeach
            </div>
        @endif
    @endisset
</div>

@php
    $rubroActual = \Illuminate\Support\Facades\Auth::guard('web')->user()->empresa->rubro;
    $ejemploCombo = match ($rubroActual) {
        'peluqueria', 'barberia' => 'Si un cliente quiere "Corte + Barba", va a poder elegir ambos servicios por separado al reservar',
        'estetica', 'unas' => 'Si un cliente quiere combinar servicios (ej: "Manicura + Pedicura"), va a poder elegirlos por separado al reservar',
        'salud' => 'Si un cliente necesita varias prácticas en la misma consulta, va a poder elegirlas por separado al reservar',
        default => 'Si un cliente quiere combinar varios de tus servicios, va a poder elegirlos por separado al reservar',
    };
@endphp
<p class="text-xs text-mate-tinta/50">
    {{ $ejemploCombo }} — no hace falta cargarlos como combo.
</p>