<x-layouts.app title="Empresas - EclesTres" :logout-route="route('staff.logout')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">Empresas registradas</h1>
        <a href="{{ route('staff.plataforma.dashboard') }}" class="text-sm text-mate-salvia">Volver</a>
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-wrap gap-2 mb-6 text-sm">
        <a href="{{ route('staff.plataforma.empresas.index') }}"
            class="px-3 py-1.5 rounded-full border {{ !$estado ? 'bg-mate-salvia text-white border-mate-salvia' : 'border-mate-borde' }}">
            Todas
        </a>
        @foreach (['pendiente' => 'Pendientes', 'activa' => 'Activas', 'suspendida' => 'Suspendidas', 'rechazada' => 'Rechazadas'] as $key => $label)
            <a href="{{ route('staff.plataforma.empresas.index', ['estado' => $key]) }}"
                class="px-3 py-1.5 rounded-full border {{ $estado === $key ? 'bg-mate-salvia text-white border-mate-salvia' : 'border-mate-borde' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="space-y-3" x-data="{
        modalRechazoAbierto: false,
        formRechazo: null,
        modalConfirmarAbierto: false,
        formConfirmar: null,
        mensajeConfirmar: ''
    }">
        @forelse ($empresas as $empresa)
            <div class="bg-mate-superficie border border-mate-borde rounded-lg p-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                    <p class="font-medium">{{ $empresa->nombre }}</p>
                    <p class="text-xs text-mate-tinta/60">
                        {{ ucfirst($empresa->rubro) }} ·
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs
                            @class([
                                'bg-yellow-100 text-yellow-800' => $empresa->estado === 'pendiente',
                                'bg-green-100 text-green-800' => $empresa->estado === 'activa',
                                'bg-red-100 text-red-800' => $empresa->estado === 'suspendida',
                                'bg-gray-100 text-gray-700' => in_array($empresa->estado, ['rechazada', 'cancelada']),
                            ])">
                            {{ ucfirst($empresa->estado) }}
                        </span>
                    </p>
                    @if ($empresa->estado === 'rechazada' && $empresa->motivo_rechazo)
                        <p class="text-xs text-red-700 mt-1 italic">Motivo: {{ $empresa->motivo_rechazo }}</p>
                    @endif
                </div>

                <div class="flex gap-2 text-sm">
                    @if ($empresa->estado === 'pendiente')
                        <form method="POST" action="{{ route('staff.plataforma.empresas.activar', $empresa) }}">
                            @csrf
                            <button class="px-3 py-1.5 rounded-md bg-mate-salvia text-white">Activar</button>
                        </form>

                        <form method="POST" action="{{ route('staff.plataforma.empresas.rechazar', $empresa) }}"
                            x-ref="form-rechazo-{{ $empresa->id }}">
                            @csrf
                            <input type="hidden" name="motivo_rechazo" x-ref="motivo-{{ $empresa->id }}">
                        </form>
                        <button type="button" class="px-3 py-1.5 rounded-md border border-mate-borde"
                            @click="modalRechazoAbierto = true; formRechazo = $refs['form-rechazo-{{ $empresa->id }}']; motivoInput = $refs['motivo-{{ $empresa->id }}']">
                            Rechazar
                        </button>
                    @elseif ($empresa->estado === 'activa')
                        <form method="POST" action="{{ route('staff.plataforma.empresas.suspender', $empresa) }}">
                            @csrf
                            <button type="button" class="px-3 py-1.5 rounded-md border border-red-300 text-red-700"
                                @click="modalConfirmarAbierto = true; formConfirmar = $el.closest('form'); mensajeConfirmar = '¿Suspender esta empresa? No podrá operar hasta que la reactives.'">
                                Suspender
                            </button>
                        </form>
                    @elseif ($empresa->estado === 'suspendida')
                        <form method="POST" action="{{ route('staff.plataforma.empresas.reactivar', $empresa) }}">
                            @csrf
                            <button class="px-3 py-1.5 rounded-md bg-mate-salvia text-white">Reactivar</button>
                        </form>
                    @elseif ($empresa->estado === 'rechazada')
                        <form method="POST" action="{{ route('staff.plataforma.empresas.reactivar', $empresa) }}">
                            @csrf
                            <button class="px-3 py-1.5 rounded-md bg-mate-salvia text-white">Reactivar</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-mate-tinta/60">No hay empresas en este estado.</p>
        @endforelse

        {{-- Modal para pedir motivo de rechazo --}}
        <div x-show="modalRechazoAbierto" x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50"
            style="display: none;"
            x-data="{ motivo: '' }">
            <div @click.outside="modalRechazoAbierto = false" class="bg-white rounded-lg max-w-sm w-full p-5">
                <p class="text-sm font-medium mb-3">Motivo del rechazo</p>
                <textarea x-model="motivo" rows="3" placeholder="Ej: Datos incompletos, dirección inválida..."
                    class="w-full rounded-md border border-mate-borde px-3 py-2 text-sm mb-4"></textarea>
                <div class="flex gap-2">
                    <button type="button" @click="modalRechazoAbierto = false"
                        class="flex-1 border border-mate-borde rounded-md py-2 text-sm font-medium">
                        Cancelar
                    </button>
                    <button type="button"
                        @click="motivoInput.value = motivo; modalRechazoAbierto = false; $nextTick(() => formRechazo.submit())"
                        :disabled="!motivo"
                        class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white rounded-md py-2 text-sm font-medium">
                        Rechazar
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal genérico de confirmación (suspender) --}}
        <div x-show="modalConfirmarAbierto" x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50"
            style="display: none;">
            <div @click.outside="modalConfirmarAbierto = false" class="bg-white rounded-lg max-w-sm w-full p-5">
                <p class="text-sm mb-5" x-text="mensajeConfirmar"></p>
                <div class="flex gap-2">
                    <button type="button" @click="modalConfirmarAbierto = false"
                        class="flex-1 border border-mate-borde rounded-md py-2 text-sm font-medium">
                        Cancelar
                    </button>
                    <button type="button" @click="modalConfirmarAbierto = false; $nextTick(() => formConfirmar.submit())"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white rounded-md py-2 text-sm font-medium">
                        Sí, suspender
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>