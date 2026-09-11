<x-layouts.app title="Panel de mi empresa - EclesTres" :logout-route="route('staff.logout')">

    @php
        $usuarioActual = auth('web')->user();
        $empresa = $usuarioActual->empresa;
    @endphp

    <p class="text-sm text-mate-tinta/70 mb-6">
        Hola, {{ $usuarioActual->nombre }} ({{ $usuarioActual->rol }}).
    </p>

    {{-- Visible para CUALQUIER staff logueado: admin u operador --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route('staff.empresa.turnos.index') }}"
            class="inline-block bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
            Ver turnos de hoy
        </a>
        <a href="{{ route('staff.empresa.pedidos.index') }}"
            class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
            Ver pedidos
        </a>
        <a href="{{ route('staff.mi-perfil.edit') }}"
            class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
            Mi perfil
        </a>
    </div>

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
            <a href="{{ route('staff.empresa.direccion.edit') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Configurar dirección
            </a>
            <a href="{{ route('staff.empresa.horario-general.edit') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Horario general del negocio
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
            <a href="{{ route('staff.empresa.productos.index') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Gestionar productos
            </a>
            <a href="{{ route('staff.empresa.reportes.comisiones') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Reporte de comisiones
            </a>
            <a href="{{ route('staff.empresa.reportes.pedidos') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Reporte de pedidos
            </a>
        </div>
    @endif

</x-layouts.app>