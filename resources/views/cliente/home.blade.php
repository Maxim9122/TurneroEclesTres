<x-layouts.app title="Mi cuenta - EclesTres" :logout-route="route('cliente.logout')">
    <h1 class="font-display text-2xl mb-2">Hola, {{ auth('cliente')->user()->nombre }}</h1>
    <p class="text-sm text-mate-tinta/70 mb-6">Buscá tu salón ideal y reservá en segundos.</p>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('home') }}"
            class="inline-block bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
            Buscar salones
        </a>
        <a href="{{ route('cliente.turnos.index') }}"
            class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
            Mis turnos
        </a>
    </div>
</x-layouts.app>