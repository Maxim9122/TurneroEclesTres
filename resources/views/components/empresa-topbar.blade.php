@props(['empresa', 'backRoute' => null, 'backLabel' => null])

<div style="{{ $empresa->fondoCss() }}">
    <div class="bg-black/15 px-4 py-4 sm:px-6 flex items-center gap-3">
        @if ($backRoute)
            <a href="{{ $backRoute }}" class="text-white text-lg shrink-0">&larr;</a>
        @endif

        @if ($empresa->logo_path)
            <img src="{{ asset('storage/' . $empresa->logo_path) }}"
                class="w-10 h-10 rounded-full object-cover bg-white shadow shrink-0">
        @endif

        <div class="min-w-0">
            <p class="text-white font-display text-base leading-tight drop-shadow truncate">{{ $empresa->nombre }}</p>
            @if ($backLabel)
                <p class="text-white/80 text-xs">{{ $backLabel }}</p>
            @endif
        </div>
    </div>
</div>