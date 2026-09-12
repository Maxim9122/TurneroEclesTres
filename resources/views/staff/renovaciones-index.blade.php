<x-layouts.app title="Próximas renovaciones - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Próximas renovaciones</h1>
        <a href="{{ route('staff.empresa.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    <p class="text-sm text-mate-tinta/70 mb-6">
        Clientes que deberían volver a hacerse un servicio en los próximos 5 días.
    </p>

    <div class="space-y-2 mb-10">
        @forelse ($proximas as $item)
            @php
                $mensaje = "Hola {$item->cliente_nombre}! Te escribimos de EclesTres para recordarte que ya se acerca "
                    . "el momento de renovar tu {$item->servicio_nombre} (aprox. " . \Carbon\Carbon::parse($item->fecha_renovacion)->format('d/m/Y') . "). "
                    . "¿Querés que te reservemos un turno?";
                $link = \App\Support\WhatsApp::linkChat($item->cliente_telefono, $mensaje);
            @endphp
            <div class="bg-mate-superficie border border-mate-borde rounded-md p-3 flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <p class="text-sm font-medium">{{ $item->cliente_nombre }}</p>
                    <p class="text-xs text-mate-tinta/60">
                        {{ $item->servicio_nombre }} · renovar el {{ \Carbon\Carbon::parse($item->fecha_renovacion)->format('d/m/Y') }}
                        ({{ \Carbon\Carbon::parse($item->fecha_renovacion)->diffForHumans() }})
                    </p>
                </div>

                @if ($link)
                    <a href="{{ $link }}" target="_blank" class="text-xs bg-mate-salvia text-white rounded-md px-3 py-1.5">
                        📱 Avisar por WhatsApp
                    </a>
                @else
                    <span class="text-xs text-mate-tinta/40">Sin teléfono</span>
                @endif
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No hay renovaciones próximas en los próximos 5 días.</p>
        @endforelse
    </div>

    <h2 class="font-display text-lg mb-3">Vencidas (últimas 30)</h2>
    <div class="space-y-2">
        @forelse ($vencidas as $item)
            <div class="bg-mate-superficie border border-mate-borde rounded-md p-3 opacity-75">
                <p class="text-sm font-medium">{{ $item->cliente_nombre }}</p>
                <p class="text-xs text-red-700">
                    {{ $item->servicio_nombre }} · venció el {{ \Carbon\Carbon::parse($item->fecha_renovacion)->format('d/m/Y') }}
                </p>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No hay renovaciones vencidas.</p>
        @endforelse
    </div>
</x-layouts.app>