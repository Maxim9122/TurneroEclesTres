<x-layouts.app title="Panel de mi empresa - EclesTres" :logout-route="route('staff.logout')">

    @php
        $usuarioActual = auth('web')->user();
        $empresa = $usuarioActual->empresa;
    @endphp

    <p class="text-sm text-mate-tinta/70 mb-6">
        Hola, {{ $usuarioActual->nombre }} ({{ $usuarioActual->rol }}).
    </p>

    @if ($sinProfesionales)
        <div class="mb-6 text-sm text-yellow-800 bg-yellow-50 border border-yellow-200 rounded-md p-3">
            ⚠️ Todavía no cargaste ningún profesional activo. Sin al menos uno, tus clientes <strong>no van a poder reservar turnos</strong>.
            @if (auth('web')->user()->esAdmin())
                <a href="{{ route('staff.empresa.profesionales.create') }}" class="underline font-medium">Cargar uno ahora</a>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('staff.empresa.buscar') }}" class="mb-6 max-w-sm">
        @csrf
        <div class="flex gap-2 mb-2">
            <input type="number" name="numero" required placeholder="Buscar por N°..."
                class="flex-1 rounded-md border border-mate-borde bg-white px-3 py-2 text-sm">
            <button type="submit" class="bg-mate-salvia text-white rounded-md px-4 py-2 text-sm font-medium">🔍</button>
        </div>
        <div class="flex gap-4 text-sm text-mate-tinta/70">
            <label class="flex items-center gap-1.5">
                <input type="radio" name="tipo" value="turno" checked class="border-mate-borde">
                Turno
            </label>
            <label class="flex items-center gap-1.5">
                <input type="radio" name="tipo" value="pedido" class="border-mate-borde">
                Pedido
            </label>
        </div>
    </form>
    
    {{-- Visible para CUALQUIER staff logueado: admin u operador --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route('staff.empresa.turnos.index') }}"
            class="inline-block bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
            Ver turnos de hoy
        </a>
        <a href="{{ route('staff.empresa.renovaciones.index') }}"
            class="relative inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
            Próximas renovaciones
            @if ($cantidadRenovaciones > 0)
                <span class="absolute -top-2 -right-2 bg-green-600 text-white text-xs font-medium rounded-full w-5 h-5 flex items-center justify-center">
                    {{ $cantidadRenovaciones }}
                </span>
            @endif
        </a>
        <a href="{{ route('staff.empresa.pedidos.index') }}"
            class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
            Ver pedidos
        </a>
        <a href="{{ route('staff.mi-perfil.edit') }}"
            class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
            Mi perfil
        </a>
    </div>

    @if ($usuarioActual->esAdmin())
        <div class="mb-6 bg-mate-superficie border border-mate-borde rounded-md p-4"
            x-data="{ qrAbierto: false }">
            <p class="text-sm text-mate-tinta/70 mb-1">Tu link público para compartir con clientes:</p>
            <div class="flex items-center gap-2 flex-wrap">
                <input type="text" readonly value="{{ route('publico.empresa', $empresa) }}"
                    class="flex-1 min-w-[180px] text-sm bg-white border border-mate-borde rounded-md px-3 py-2"
                    onclick="this.select()">
                <a href="{{ route('publico.empresa', $empresa) }}" target="_blank"
                    class="text-sm text-mate-salvia font-medium whitespace-nowrap">Ver perfil →</a>
                <button type="button" @click="qrAbierto = true"
                    class="text-sm bg-mate-salvia text-white rounded-md px-3 py-2 font-medium whitespace-nowrap">
                    📱 Ver código QR
                </button>
            </div>

            {{-- Modal con el QR --}}
            <div x-show="qrAbierto" x-cloak
                class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50"
                style="display: none;">
                <div @click.outside="qrAbierto = false" class="bg-white rounded-lg max-w-xs w-full p-5 text-center">
                    <p class="font-medium text-sm mb-3">Código QR de {{ $empresa->nombre }}</p>

                    @php
                        $urlEmpresa = route('publico.empresa', $empresa);
                        $urlQr = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($urlEmpresa);
                    @endphp

                    <img src="{{ $urlQr }}" alt="Código QR" class="mx-auto mb-4 border border-mate-borde rounded-md" width="250" height="250">

                    <p class="text-xs text-mate-tinta/50 mb-4">Tus clientes pueden escanearlo con la cámara del celular para entrar directo a tu perfil.</p>

                    <div class="flex gap-2">
                        <a href="{{ $urlQr }}" download="qr-{{ $empresa->slug }}.png"
                            class="flex-1 bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2 text-sm font-medium">
                            Descargar
                        </a>
                        <button type="button" @click="qrAbierto = false"
                            class="flex-1 border border-mate-borde rounded-md py-2 text-sm font-medium">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('staff.empresa.branding.edit') }}"
                class="inline-block bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2.5 text-sm font-medium">
                Configurar logo y fondo
            </a>
            <a href="{{ route('staff.empresa.direccion.edit') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Configurar dirección
            </a>
            <a href="{{ route('staff.empresa.horario-general.edit') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Horario general del negocio
            </a>
            <a href="{{ route('staff.empresa.operadores.index') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Gestionar operadores
            </a>
            <a href="{{ route('staff.empresa.servicios.index') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Gestionar servicios
            </a>
            <a href="{{ route('staff.empresa.profesionales.index') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Gestionar profesionales
            </a>
            <a href="{{ route('staff.empresa.productos.index') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Gestionar productos
            </a>
            <a href="{{ route('staff.empresa.reportes.comisiones') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Reporte de comisiones
            </a>
            <a href="{{ route('staff.empresa.reportes.pedidos') }}"
                class="inline-block border border-mate-borde rounded-md px-4 py-2.5 text-sm font-medium">
                Reporte de pedidos
            </a>
        </div>
    @endif

</x-layouts.app>