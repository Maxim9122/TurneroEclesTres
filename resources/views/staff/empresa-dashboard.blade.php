<x-layouts.app title="Panel de mi empresa - EclesTres" :logout-route="route('staff.logout')">

    @php
        $usuarioActual = auth('web')->user();
        $empresa = $usuarioActual->empresa;
    @endphp

    <div class="rounded-lg overflow-hidden mb-6" style="{{ $empresa->fondoCss() }}">
        <div class="bg-black/15 px-5 py-8 flex items-center gap-4">
            @if ($empresa->logo_path)
                <img src="{{ asset('storage/' . $empresa->logo_path) }}"
                    class="w-16 h-16 rounded-full object-cover bg-white shadow shrink-0">
            @endif
            <div>
                <h1 class="font-display text-2xl text-white drop-shadow">{{ $empresa->nombre }}</h1>
                <p class="text-sm text-white/90">{{ ucfirst($empresa->rubro) }}</p>
            </div>
        </div>
    </div>

    <p class="text-sm text-mate-tinta/70 mb-6">
        Hola, {{ $usuarioActual->nombre }} ({{ $usuarioActual->rol }}).
    </p>

    @if ($usuarioActual->esAdmin())
        <div class="mb-6 bg-mate-superficie border border-mate-borde rounded-md p-4">
            <p class="text-sm text-mate-tinta/70 mb-1">Tu link público para compartir con clientes:</p>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ route('publico.empresa', $empresa) }}"
                    class="flex-1 text-sm bg-white border border-mate-borde rounded-md px-3 py-2"
                    onclick="this.select()">
                <a href="{{ route('publico.empresa', $empresa) }}" target="_blank"
                    class="text-sm text-mate-salvia font-medium whitespace-nowrap">Ver perfil →</a>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('staff.empresa.branding.edit') }}"
                class="inline-block bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
                Configurar logo y fondo
            </a>
            <a href="{{ route('staff.empresa.turnos.index') }}"
                class="inline-block mb-6 bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
                Ver turnos de hoy
            </a>
            <a href="{{ route('staff.empresa.direccion.edit') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Configurar dirección
            </a>
            <a href="{{ route('staff.empresa.operadores.index') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Gestionar operadores
            </a>
            <a href="{{ route('staff.empresa.servicios.index') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Gestionar servicios
            </a>
            <a href="{{ route('staff.empresa.profesionales.index') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Gestionar profesionales
            </a>
            <a href="{{ route('staff.empresa.horario-general.edit') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Horario general del negocio
            </a>
        </div>
    @endif

    @if ($usuarioActual->esOperador())
        @if ($usuarioActual->profesional)
            <a href="{{ route('staff.empresa.profesionales.horarios.edit', $usuarioActual->profesional) }}"
                class="inline-block bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
                Configurar mis horarios
            </a>
        @else
            <p class="text-sm text-mate-tinta/60">
                Todavía no estás vinculado como profesional. Pedile al admin que te asocie desde "Gestionar profesionales" si vas a atender turnos.
            </p>
        @endif
    @endif

</x-layouts.app>