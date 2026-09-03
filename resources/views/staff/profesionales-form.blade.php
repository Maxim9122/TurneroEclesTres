@if ($errors->any())
    <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-md p-3">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <label class="block text-sm mb-1">Nombre</label>
    <input type="text" name="nombre" required value="{{ old('nombre', $profesional->nombre ?? '') }}"
        class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mate-salvia">
</div>

<div>
    <label class="block text-sm mb-1">Foto (opcional)</label>
    <input type="file" name="foto" accept="image/*"
        class="w-full text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-mate-salvia file:text-white text-mate-tinta/70">
    @isset($profesional)
        @if ($profesional->foto_path)
            <img src="{{ asset('storage/' . $profesional->foto_path) }}" class="w-16 h-16 rounded-full object-cover mt-2">
        @endif
    @endisset
</div>

<div>
    <label class="block text-sm mb-1">Vincular a un operador (opcional)</label>
    <select name="usuario_id" class="w-full rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
        <option value="">Ninguno — no usa el sistema</option>
        @foreach ($operadoresDisponibles as $operador)
            <option value="{{ $operador->id }}"
                @selected(old('usuario_id', $profesional->usuario_id ?? null) == $operador->id)>
                {{ $operador->nombre }} ({{ $operador->email }})
            </option>
        @endforeach
    </select>
    <p class="text-xs text-mate-tinta/50 mt-1">
        Elegilo solo si esta persona también entra al sistema como operador y este es su propio perfil de agenda.
    </p>
</div>