<x-layouts.app title="Nuevo operador - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Nuevo operador</h1>
        <a href="{{ route('staff.empresa.operadores.index') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('staff.empresa.operadores.store') }}"
        class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-4 max-w-md">
        @csrf

        <div>
            <label class="block text-sm mb-1">Nombre</label>
            <input type="text" name="nombre" required value="{{ old('nombre') }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Email</label>
            <input type="email" name="email" required value="{{ old('email') }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Contraseña</label>
            <input type="password" name="password" required
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Repetir contraseña</label>
            <input type="password" name="password_confirmation" required
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <p class="text-xs text-mate-tinta/50">
            Vas a tener que compartirle este email y contraseña al operador para que pueda ingresar.
        </p>

        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            Crear operador
        </button>
    </form>
</x-layouts.app>