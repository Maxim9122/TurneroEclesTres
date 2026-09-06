<x-layouts.app title="Mi cuenta - EclesTres" :logout-route="route('cliente.logout')">
    <h1 class="font-display text-2xl mb-2">Hola, {{ auth('cliente')->user()->nombre }}</h1>
    <p class="text-sm text-mate-tinta/70 mb-6">Buscá tu salón ideal y reservá en segundos.</p>

    @php $recientes = auth('cliente')->user()->empresasRecientes(); @endphp

    @if ($recientes->isNotEmpty())
        <div class="mb-8">
            <p class="text-sm font-medium mb-3">Tus accesos rápidos</p>
            <div class="grid grid-cols-3 gap-3">
                @foreach ($recientes as $empresa)
                    <div class="rounded-lg overflow-hidden border border-mate-borde" style="{{ $empresa->fondoCss() }}">
                        <div class="bg-black/15 flex flex-col items-center gap-1 p-3">
                            @if ($empresa->logo_path)
                                <img src="{{ asset('storage/' . $empresa->logo_path) }}"
                                    class="w-10 h-10 rounded-full object-cover bg-white shadow">
                            @else
                                <div class="w-10 h-10 rounded-full bg-white/80"></div>
                            @endif
                            <span class="text-xs text-white text-center drop-shadow font-medium truncate w-full">
                                {{ $empresa->nombre }}
                            </span>
                        </div>
                        <a href="{{ route('publico.empresa', $empresa) }}"
                            class="block text-center text-xs bg-mate-superficie py-1.5">
                            Ver / Reservar
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

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