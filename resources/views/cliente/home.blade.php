<x-layouts.app title="Mi cuenta - EclesTres" :logout-route="route('cliente.logout')">
    <h1 class="font-display text-2xl mb-2">Hola, {{ auth('cliente')->user()->nombre }}</h1>
    <p class="text-sm text-mate-tinta/70">Acá vas a poder buscar salones, sacar turnos y comprar productos.</p>
</x-layouts.app>