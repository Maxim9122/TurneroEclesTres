@props(['empresa', 'cantidad' => 0])

@if ($cantidad > 0)
    <a href="{{ route('publico.carrito.index', $empresa) }}"
        class="fixed bottom-5 right-5 z-40 bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="w-6 h-6">
            <path d="M3 3h2l.4 2M7 13h10l3-8H5.4M7 13L5.4 5M7 13l-1.5 6h13M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"
                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="absolute -top-1 -right-1 bg-mate-arcilla text-white text-xs font-medium rounded-full w-5 h-5 flex items-center justify-center">
            {{ $cantidad }}
        </span>
    </a>
@endif