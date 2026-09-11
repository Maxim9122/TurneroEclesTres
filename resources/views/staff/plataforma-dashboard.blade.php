<x-layouts.app title="Panel de plataforma - EclesTres" :logout-route="route('staff.logout')">
    <h1 class="font-display text-2xl mb-2">Panel de plataforma</h1>
    <p class="text-sm text-mate-tinta/70 mb-6">Hola, {{ auth('web')->user()->nombre }}.</p>

    <a href="{{ route('staff.plataforma.empresas.index') }}"
        class="inline-block bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
        Ver empresas registradas
    </a>
    <a href="{{ route('staff.plataforma.clientes.index') }}"
        class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
        Clientes
    </a>
    <a href="{{ route('staff.plataforma.staff.index') }}"
        class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
        Staff de empresas
    </a>
</x-layouts.app>