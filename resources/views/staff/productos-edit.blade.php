<x-layouts.app title="Editar producto - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6 max-w-md mx-auto">
        <h1 class="font-display text-2xl">Editar producto</h1>
        <a href="{{ route('staff.empresa.productos.index') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <form method="POST" action="{{ route('staff.empresa.productos.update', $producto) }}" enctype="multipart/form-data"
        class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-4 max-w-md mx-auto">
        @csrf
        @method('PUT')
        @include('staff.productos-form')

        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            Guardar cambios
        </button>
    </form>

    {{-- Formularios ocultos para eliminar imágenes (fuera del form principal, HTML no permite forms anidados) --}}
    @if ($producto->imagenes->isNotEmpty())
        @foreach ($producto->imagenes as $imagen)
            <form id="eliminar-imagen-{{ $imagen->id }}" method="POST"
                action="{{ route('staff.empresa.productos.imagenes.eliminar', $imagen) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endif
</x-layouts.app>