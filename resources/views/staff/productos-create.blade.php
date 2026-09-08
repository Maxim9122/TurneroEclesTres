<x-layouts.app title="Nuevo producto - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Nuevo producto</h1>
        <a href="{{ route('staff.empresa.productos.index') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <form method="POST" action="{{ route('staff.empresa.productos.store') }}" enctype="multipart/form-data"
        class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-4 max-w-md">
        @csrf
        @include('staff.productos-form')

        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            Crear producto
        </button>
    </form>
</x-layouts.app>