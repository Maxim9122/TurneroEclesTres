<x-layouts.app title="Editar operador - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Editar operador</h1>
        <a href="{{ route('staff.empresa.operadores.index') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('staff.empresa.operadores.update', $usuario) }}"
        class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-4 max-w-md">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm mb-1">Nombre</label>
            <input type="text" name="nombre" required value="{{ old('nombre', $usuario->nombre) }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Email</label>
            <input type="email" name="email" required value="{{ old('email', $usuario->email) }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Teléfono (WhatsApp)</label>
            <input type="tel" name="telefono" value="{{ old('telefono', $usuario->telefono) }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            Guardar cambios
        </button>
    </form>
</x-layouts.app>