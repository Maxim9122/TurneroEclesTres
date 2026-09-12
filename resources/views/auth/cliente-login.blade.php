<x-layouts.guest title="Ingresar - EclesTres">
    <h1 class="font-display text-xl mb-1">Ingresá a tu cuenta</h1>
    <p class="text-sm text-mate-tinta/70 mb-6">Para reservar turnos y comprar productos.</p>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('cliente.login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm mb-1">Email</label>
            <input type="email" name="email" required autofocus value="{{ old('email') }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>
        <div>
            <label class="block text-sm mb-1">Contraseña</label>
            <input type="password" name="password" required
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>
        <label class="flex items-center gap-2 text-sm text-mate-tinta/70">
            <input type="checkbox" name="recordar" class="rounded border-mate-borde">
            Mantenerme conectado
        </label>
        <p class="text-sm text-right">
            <a href="{{ route('cliente.password.request') }}" class="text-mate-salvia">¿Olvidaste tu contraseña?</a>
        </p>
        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium transition-colors">
            Ingresar
        </button>
    </form>

    <p class="text-sm text-center text-mate-tinta/70 mt-6">
        ¿No tenés cuenta? <a href="{{ route('cliente.register') }}" class="text-mate-salvia font-medium">Registrate</a>
    </p>    
</x-layouts.guest>