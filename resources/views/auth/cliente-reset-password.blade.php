<x-layouts.guest title="Nueva contraseña - EclesTres">
    <h1 class="font-display text-xl mb-1">Elegí una nueva contraseña</h1>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('cliente.password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label class="block text-sm mb-1">Email</label>
            <input type="email" name="email" required value="{{ old('email', $email) }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>
        <div>
            <label class="block text-sm mb-1">Nueva contraseña</label>
            <input type="password" name="password" required
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>
        <div>
            <label class="block text-sm mb-1">Repetir contraseña</label>
            <input type="password" name="password_confirmation" required
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>
        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            Cambiar contraseña
        </button>
    </form>
</x-layouts.guest>