<x-layouts.guest title="Recuperar contraseña - EclesTres">
    <h1 class="font-display text-xl mb-1">Recuperar contraseña</h1>
    <p class="text-sm text-mate-tinta/70 mb-6">Te vamos a redirigir a WhatsApp para coordinar la recuperación con nosotros.</p>

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

    <form method="POST" action="{{ route('cliente.password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm mb-1">Email</label>
            <input type="email" name="email" required autofocus value="{{ old('email') }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>
        <button type="submit"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            Solicitar por WhatsApp
        </button>
    </form>

    <p class="text-sm text-center text-mate-tinta/70 mt-6">
        <a href="{{ route('cliente.login') }}" class="text-mate-salvia font-medium">Volver al login</a>
    </p>
</x-layouts.guest>