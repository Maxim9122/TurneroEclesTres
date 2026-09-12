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
            Te vamos a avisar por WhatsApp apenas aprobemos tu registro.
        @elseif ($empresa?->estado === 'suspendida')
            Contactá a soporte de EclesTres para regularizar tu situación.
        @else
            Contactá a soporte de EclesTres.
        @endif
    </p>

    @if ($empresa?->estado === 'pendiente')
        @php
            $admin = auth('web')->user();
            $mensajeAlta = "Hola! Acabo de registrar mi negocio \"{$empresa->nombre}\" en EclesTres y quisiera solicitar el alta.\n"
                . "Rubro: {$empresa->rubro}\n"
                . "Admin: {$admin->nombre} ({$admin->email})";
            $linkAlta = \App\Support\WhatsApp::linkChat('3841670079', $mensajeAlta);
        @endphp

        <a href="{{ $linkAlta }}" target="_blank"
            class="block text-center bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium mb-4">
            📱 Solicitar alta por WhatsApp
        </a>
    @endif

    <form method="POST" action="{{ route('staff.logout') }}">
        @csrf
        <button type="submit" class="text-sm text-mate-salvia font-medium">Cerrar sesión</button>
    </form>
</x-layouts.guest>