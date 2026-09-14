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
    <label class="block text-sm mb-1">Nombre del producto</label>
    <input type="text" name="nombre" required value="{{ old('nombre', $producto->nombre ?? '') }}"
        class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
</div>

<div>
    <label class="block text-sm mb-1">Categoría</label>
    <select name="categoria_id" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
        <option value="">Sin categoría</option>
        @foreach (\Illuminate\Support\Facades\Auth::guard('web')->user()->empresa->categoriasProductos()->orderBy('nombre')->get() as $cat)
            <option value="{{ $cat->id }}" @selected(old('categoria_id', $producto->categoria_id ?? null) == $cat->id)>
                {{ $cat->nombre }}
            </option>
        @endforeach
    </select>
    <p class="text-xs text-mate-tinta/50 mt-1">
        <a href="{{ route('staff.empresa.categorias.index') }}" class="underline">Gestionar categorías</a>
    </p>
</div>

<div>
    <label class="block text-sm mb-1">Descripción (opcional)</label>
    <textarea name="descripcion" rows="3"
        class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm mb-1">Precio</label>
        <input type="number" name="precio" min="0" step="0.01" required
            value="{{ old('precio', $producto->precio ?? '') }}"
            class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
    </div>
    <div>
        <label class="block text-sm mb-1">Stock disponible</label>
        <input type="number" name="stock" min="0" required
            value="{{ old('stock', $producto->stock ?? 0) }}"
            class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
    </div>
</div>

<div>
    <label class="block text-sm mb-1">Foto (opcional)</label>
    <input type="file" name="foto" accept="image/*"
        class="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-mate-salvia file:text-white text-mate-tinta/70">
    @isset($producto)
        @if ($producto->foto_path)
            <img src="{{ asset('storage/' . $producto->foto_path) }}" class="w-20 h-20 rounded-md object-cover mt-2">
        @endif
    @endisset
</div>

<div>
    <label class="block text-sm mb-1">Fotos adicionales (opcional, hasta 2 más)</label>
    <input type="file" name="imagenes_adicionales[]" accept="image/*" multiple
        class="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-mate-salvia file:text-white text-mate-tinta/70">
    <p class="text-xs text-mate-tinta/50 mt-1">El producto puede tener hasta 3 fotos en total.</p>

    @isset($producto)
        @if ($producto->imagenes->isNotEmpty())
            <div class="flex gap-2 mt-2">
                @foreach ($producto->imagenes as $imagen)
                    <div class="relative">
                        <img src="{{ asset('storage/' . $imagen->path) }}" class="w-16 h-16 rounded-md object-cover">
                        <button type="submit" form="eliminar-imagen-{{ $imagen->id }}"
                            class="absolute -top-1.5 -right-1.5 bg-red-600 text-white text-xs w-5 h-5 rounded-full">×</button>
                    </div>
                @endforeach
            </div>
        @endif
    @endisset
</div>