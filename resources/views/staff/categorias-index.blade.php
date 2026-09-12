<x-layouts.app title="Categorías de productos - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Categorías de productos</h1>
        <a href="{{ route('staff.empresa.productos.index') }}" class="text-sm text-mate-salvia">Volver a productos</a>
    </div>

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

    <form method="POST" action="{{ route('staff.empresa.categorias.store') }}" class="flex gap-2 mb-6 max-w-md">
        @csrf
        <input type="text" name="nombre" required placeholder="Nueva categoría, ej: Shampoos"
            class="flex-1 rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
        <button type="submit" class="bg-mate-salvia text-white rounded-md px-4 py-2 text-sm font-medium">
            Agregar
        </button>
    </form>

    <div class="space-y-2 max-w-md">
        @forelse ($categorias as $categoria)
            <div class="bg-mate-superficie border border-mate-borde rounded-md p-3" x-data="{ editando: false }">
                <div class="flex items-center justify-between gap-2" x-show="!editando">
                    <span class="text-sm">{{ $categoria->nombre }}
                        <span class="text-xs text-mate-tinta/50">({{ $categoria->productos_count }} productos)</span>
                    </span>
                    <div class="flex gap-2 text-xs">
                        <button type="button" @click="editando = true" class="underline text-mate-tinta/70">Editar</button>
                        <form method="POST" action="{{ route('staff.empresa.categorias.destroy', $categoria) }}"
                            onsubmit="return confirm('¿Eliminar esta categoría? Los productos quedarán sin categoría.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="underline text-red-700">Eliminar</button>
                        </form>
                    </div>
                </div>

                <form x-show="editando" x-cloak method="POST" action="{{ route('staff.empresa.categorias.update', $categoria) }}" class="flex gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="nombre" value="{{ $categoria->nombre }}" required
                        class="flex-1 rounded-md border border-mate-borde bg-white px-2 py-1 text-sm">
                    <button type="submit" class="text-xs bg-mate-salvia text-white rounded-md px-3 py-1.5">Guardar</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">Todavía no cargaste categorías.</p>
        @endforelse
    </div>
</x-layouts.app>