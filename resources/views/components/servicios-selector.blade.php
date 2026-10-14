{{--
    Lista de servicios con sus adicionales para los formularios del staff.
    Envía servicios[] y adicionales[]. Los adicionales aparecen al marcar su servicio y, si se
    desmarca el servicio, quedan deshabilitados (no se envían).
    En cada cambio dispara el evento "servicios-cambiaron" con { servicios: [...], adicionales: [...] }
    para las pantallas que necesitan reaccionar (por ej. recalcular horarios).
--}}
@props([
    'servicios',
    'seleccionados' => [],
    'adicionalesSeleccionados' => [],
    'mostrarDuracion' => true,
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
                        <label class="flex items-center justify-between gap-2 text-sm cursor-pointer py-0.5">
                            <span class="flex items-center gap-2">
                                <input type="checkbox" name="adicionales[]" value="{{ $adicional->id }}"
                                    class="rounded border-mate-borde"
                                    :disabled="!marcado"
                                    @checked(in_array($adicional->id, $adicionalesSeleccionados))
                                    @change="emitir()">
                                + {{ $adicional->nombre }}
                                @if ($mostrarDuracion && $adicional->duracion_minutos > 0)
                                    <span class="text-xs text-mate-tinta/50">(+{{ $adicional->duracion_minutos }} min)</span>
                                @endif
                            </span>
                            <span class="text-mate-tinta/80">+${{ number_format($adicional->precio, 2, ',', '.') }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
