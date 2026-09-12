<x-layouts.guest title="Crear cuenta - EclesTres">
    <h1 class="font-display text-xl mb-1">Creá tu cuenta</h1>
    <p class="text-sm text-mate-tinta/70 mb-6">Reservá turnos y comprá productos en tus salones favoritos.</p>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('cliente.register') }}" class="space-y-4">
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
            <label class="block text-sm mb-1">Teléfono (opcional)</label>
            <input type="tel" name="telefono" value="{{ old('telefono') }}"
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
        <x-recaptcha />
        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium transition-colors">
            Crear cuenta
        </button>
    </form>

    <p class="text-sm text-center text-mate-tinta/70 mt-6">
        ¿Ya tenés cuenta? <a href="{{ route('cliente.login') }}" class="text-mate-salvia font-medium">Ingresá acá</a>
    </p>
</x-layouts.guest>