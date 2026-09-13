@props(['imagenes' => [], 'nombre' => ''])

@if (count($imagenes) > 0)
    <div x-data="{ abierta: false, indice: 0, imagenes: {{ json_encode($imagenes) }} }">
        {{-- Miniatura clickeable --}}
        <button type="button" @click="abierta = true; indice = 0" class="block w-full h-full">
            {{ $slot }}
        </button>

        {{-- Modal con carrusel --}}
        <div x-show="abierta" x-cloak
            class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4"
            style="display: none;">
            <button type="button" @click="abierta = false"
                class="absolute top-4 right-4 text-white text-3xl leading-none">&times;</button>

            <div class="relative max-w-md w-full" @click.outside="abierta = false">
                <img :src="'{{ asset('storage') }}/' + imagenes[indice]" class="w-full rounded-lg max-h-[70vh] object-contain bg-black">

                <template x-if="imagenes.length > 1">
                    <div>
                        <button type="button" @click="indice = (indice - 1 + imagenes.length) % imagenes.length"
                            class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 rounded-full w-9 h-9 flex items-center justify-center text-lg">
                            &larr;
                        </button>
                        <button type="button" @click="indice = (indice + 1) % imagenes.length"
                            class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 rounded-full w-9 h-9 flex items-center justify-center text-lg">
                            &rarr;
                        </button>

                        <div class="flex justify-center gap-1.5 mt-3">
                            <template x-for="(img, i) in imagenes" :key="i">
                                <button type="button" @click="indice = i"
                                    class="w-2 h-2 rounded-full"
                                    :class="indice === i ? 'bg-white' : 'bg-white/40'"></button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
@endif