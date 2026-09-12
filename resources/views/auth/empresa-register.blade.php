<x-layouts.guest title="Registrar mi empresa - EclesTres">
    <h1 class="font-display text-xl mb-1">Registrá tu negocio</h1>
    <p class="text-sm text-mate-tinta/70 mb-6">Vas a poder cargar servicios y productos apenas lo aprobemos.</p>

    @if ($errors->any())
        <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('staff.registro-empresa') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm mb-1">Nombre del negocio</label>
            <input type="text" name="empresa_nombre" required value="{{ old('empresa_nombre') }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Rubro</label>
            <select name="rubro" required
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
                <option value="">Seleccioná...</option>
                <optgroup label="Estética y belleza">
                    <option value="peluqueria">Peluquería</option>
                    <option value="barberia">Barbería</option>
                    <option value="estetica">Estética</option>
                    <option value="unas">Uñas</option>
                </optgroup>
                <option value="salud">Salud</option>
                <option value="especialista">Especialista</option>
                <option value="profesion">Profesión</option>
                <option value="otro">Otro</option>
            </select>
        </div>

        <div>
            <label class="block text-sm mb-1">Teléfono (opcional)</label>
            <input type="tel" name="telefono" value="{{ old('telefono') }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Ciudad</label>
            <input type="text" name="ciudad" required value="{{ old('ciudad') }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Barrio</label>
            <input type="text" name="barrio" required value="{{ old('barrio') }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="col-span-2">
                <label class="block text-sm mb-1">Calle</label>
                <input type="text" name="calle" required value="{{ old('calle') }}"
                    class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
            </div>
            <div>
                <label class="block text-sm mb-1">Altura</label>
                <input type="text" name="altura" value="{{ old('altura') }}"
                    class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
            </div>
        </div>

        <hr class="border-mate-borde my-2">

        <div>
            <label class="block text-sm mb-1">Tu nombre (admin de la empresa)</label>
            <input type="text" name="admin_nombre" required value="{{ old('admin_nombre') }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Tu email</label>
            <input type="email" name="admin_email" required value="{{ old('admin_email') }}"
                class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
        </div>

        <div>
            <label class="block text-sm mb-1">Tu teléfono (WhatsApp)</label>
            <input type="tel" name="admin_telefono" required value="{{ old('admin_telefono') }}"
                placeholder="Ej: 3841670079"
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
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            Registrar mi negocio
        </button>
    </form>

    <p class="text-sm text-center text-mate-tinta/70 mt-6">
        ¿Ya tenés cuenta? <a href="{{ route('staff.login') }}" class="text-mate-salvia font-medium">Ingresá acá</a>
    </p>
</x-layouts.guest>