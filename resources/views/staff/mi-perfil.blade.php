<x-layouts.app title="Mi perfil - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Mi perfil</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
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

    <div class="grid gap-6 max-w-md">
        <form method="POST" action="{{ route('staff.mi-perfil.update') }}"
            class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-4">
            @csrf
            @method('PUT')
            <p class="font-medium text-sm">Datos personales</p>

            <div>
                <label class="block text-sm mb-1">Nombre</label>
                <input type="text" name="nombre" required value="{{ old('nombre', auth('web')->user()->nombre) }}"
                    class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
            </div>

            <div>
                <label class="block text-sm mb-1">Email</label>
                <input type="email" name="email" required value="{{ old('email', auth('web')->user()->email) }}"
                    class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
            </div>

            <div>
                <label class="block text-sm mb-1">Teléfono (WhatsApp)</label>
                <input type="tel" name="telefono" value="{{ old('telefono', auth('web')->user()->telefono) }}"
                    placeholder="Ej: 3841670079"
                    class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
            </div>

            <button type="submit"
                class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
                Guardar datos
            </button>
        </form>

        <form method="POST" action="{{ route('staff.mi-perfil.password') }}"
            class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-4">
            @csrf
            @method('PUT')
            <p class="font-medium text-sm">Cambiar contraseña</p>

            <div>
                <label class="block text-sm mb-1">Contraseña actual</label>
                <input type="password" name="password_actual" required
                    class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
            </div>

            <div>
                <label class="block text-sm mb-1">Nueva contraseña</label>
                <input type="password" name="password" required
                    class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
            </div>

            <div>
                <label class="block text-sm mb-1">Repetir nueva contraseña</label>
                <input type="password" name="password_confirmation" required
                    class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
            </div>

            <button type="submit"
                class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
                Cambiar contraseña
            </button>
        </form>
    </div>
</x-layouts.app>