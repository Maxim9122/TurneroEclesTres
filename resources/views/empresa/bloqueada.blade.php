<x-layouts.guest title="Cuenta en revisión - EclesTres">
    <h1 class="font-display text-xl mb-2">
        @if ($empresa?->estado === 'pendiente')
            Tu negocio está en revisión
        @elseif ($empresa?->estado === 'suspendida')
            Tu cuenta está suspendida
        @else
            Acceso no disponible
        @endif
    </h1>
    <p class="text-sm text-mate-tinta/70 mb-6">
        @if ($empresa?->estado === 'pendiente')
            Te vamos a avisar por email apenas aprobemos tu registro.
        @elseif ($empresa?->estado === 'suspendida')
            Contactá a soporte de EclesTres para regularizar tu situación.
        @else
            Contactá a soporte de EclesTres.
        @endif
    </p>
    <form method="POST" action="{{ route('staff.logout') }}">
        @csrf
        <button type="submit" class="text-sm text-mate-salvia font-medium">Cerrar sesión</button>
    </form>
</x-layouts.guest>