<x-layouts.app title="Panel de mi empresa - EclesTres" :logout-route="route('staff.logout')">
    <h1 class="font-display text-2xl mb-2">Panel de {{ auth('web')->user()->empresa->nombre }}</h1>
    <p class="text-sm text-mate-tinta/70">
        Hola, {{ auth('web')->user()->nombre }} ({{ auth('web')->user()->rol }}).
    </p>
</x-layouts.app>