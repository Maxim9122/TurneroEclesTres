{{--
    Lista de servicios con sus adicionales para los formularios del staff.
    Envía servicios[] y adicionales[] (y precios_adicionales[id] si editarPrecios está activo). Los adicionales aparecen al marcar su servicio y, si se
    desmarca el servicio, quedan deshabilitados (no se envían).
    En cada cambio dispara el evento "servicios-cambiaron" con { servicios: [...], adicionales: [...] }
    para las pantallas que necesitan reaccionar (por ej. recalcular horarios).
--}}
@props([
    'servicios',
    'seleccionados' => [],
    'adicionalesSeleccionados' => [],
    'mostrarDuracion' => true,
    'editarPrecios' => false, // editar turno: permite fijar el precio final de cada adicional
    'precios' => [],          // [id del adicional => precio guardado en el turno]
])

@php
    $seleccionados = array_map('intval', (array) $seleccionados);
    $adicionalesSeleccionados = array_map('intval', (array) $adicionalesSeleccionados);
@endphp

<div x-data="{
        emitir() {
            const marcados = (nombre) => [...$el.querySelectorAll(`input[name='${nombre}[]']:checked:not(:disabled)`)].map(i => parseInt(i.value));
            $dispatch('servicios-cambiaron', { servicios: marcados('servicios'), adicionales: marcados('adicionales') });
        },
    }" x-init="$nextTick(() => emitir())" {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @foreach ($servicios as $servicio)
        @php($adicionales = $servicio->adicionalesActivos ?? collect())
        <div x-data="{ marcado: @js(in_array($servicio->id, $seleccionados)) }">
            <label class="flex items-center justify-between bg-white border border-mate-borde rounded-md px-3 py-2 text-sm cursor-pointer">
                <span class="flex items-center gap-2">
                    <input type="checkbox" name="servicios[]" value="{{ $servicio->id }}"
                        class="rounded border-mate-borde"
                        @checked(in_array($servicio->id, $seleccionados))
                        @change="marcado = $event.target.checked; $nextTick(() => emitir())">
                    {{ $servicio->nombre }}
                    @if ($mostrarDuracion)
                        <span class="text-xs text-mate-tinta/50">({{ $servicio->duracion_minutos }} min)</span>
                    @endif
                </span>
                <span>${{ number_format($servicio->precio, 2, ',', '.') }}</span>
            </label>

            @if ($adicionales->isNotEmpty())
                <div x-show="marcado" @if (!in_array($servicio->id, $seleccionados)) style="display: none;" @endif
                    class="ml-6 mt-1 space-y-1 border-l-2 border-mate-borde pl-3">
                    @foreach ($adicionales as $adicional)
                        @php($elegido = in_array($adicional->id, $adicionalesSeleccionados))
                        <div x-data="{ elegido: @js($elegido) }">
                            <label class="flex items-center justify-between gap-2 text-sm cursor-pointer py-0.5">
                                <span class="flex items-center gap-2">
                                    <input type="checkbox" name="adicionales[]" value="{{ $adicional->id }}"
                                        class="rounded border-mate-borde"
                                        :disabled="!marcado"
                                        @checked($elegido)
                                        @change="elegido = $event.target.checked; emitir()">
                                    + {{ $adicional->nombre }}
                                    @if ($mostrarDuracion && $adicional->duracion_minutos > 0)
                                        <span class="text-xs text-mate-tinta/50">(+{{ $adicional->duracion_minutos }} min)</span>
                                    @endif
                                </span>
                                @if (!$editarPrecios)
                                    <span class="text-mate-tinta/80">+${{ number_format($adicional->precio, 2, ',', '.') }} <span class="text-xs text-mate-tinta/50">aprox.</span></span>
                                @endif
                            </label>

                            @if ($editarPrecios)
                                {{-- Precio final del adicional para ESTE turno (se acuerda con el cliente) --}}
                                <div x-show="elegido" @if (!$elegido) style="display: none;" @endif class="flex items-center gap-2 pl-6 pb-1 text-xs text-mate-tinta/70">
                                    <label for="precio-adicional-{{ $adicional->id }}">Precio final</label>
                                    <span>$</span>
                                    <input type="number" id="precio-adicional-{{ $adicional->id }}" min="0" step="0.01"
                                        name="precios_adicionales[{{ $adicional->id }}]"
                                        value="{{ (float) ($precios[$adicional->id] ?? $adicional->precio) }}"
                                        :disabled="!marcado || !elegido"
                                        class="w-28 rounded-md border border-mate-borde bg-white px-2 py-1 text-sm">
                                    <span class="text-mate-tinta/50">(referencia ${{ number_format($adicional->precio, 0, ',', '.') }})</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
