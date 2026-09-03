<x-layouts.app title="Dirección - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Dirección de tu negocio</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <p class="text-sm text-mate-tinta/70 mb-6">
        Esto es lo que ven los clientes cuando te buscan por zona.
    </p>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('staff.empresa.direccion.update') }}"
        class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-4 max-w-md">
        @csrf
        <div>
            <label class="block text-sm mb-1">Ciudad</label>
            <input type="text" name="ciudad" required value="{{ old('ciudad', $direccion?->ciudad) }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Barrio</label>
            <input type="text" name="barrio" required value="{{ old('barrio', $direccion?->barrio) }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Calle</label>
            <input type="text" name="calle" required value="{{ old('calle', $direccion?->calle) }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Altura (opcional)</label>
            <input type="text" name="altura" value="{{ old('altura', $direccion?->altura) }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            Guardar dirección
        </button>
    </form>
</x-layouts.app>