{{-- Mi perfil: activar / quitar el ingreso con huella en los dispositivos del usuario. --}}
@php
    $credencialesHuella = \App\Models\WebauthnCredencial::where('usuario_id', auth('web')->id())->orderBy('created_at')->get();
@endphp

<x-webauthn-js />

<div class="bg-mate-superficie border border-mate-borde rounded-lg p-5 space-y-3">
    <p class="font-medium text-sm">Ingreso con huella, rostro o patrón</p>
    <p class="text-xs text-mate-tinta/60">
        Entrá sin escribir la contraseña, usando el desbloqueo de tu celular o compu. Tu huella nunca sale del dispositivo
        y solo sirve para <strong>tu</strong> cuenta. Activalo solo en dispositivos personales.
    </p>

    @if ($credencialesHuella->isNotEmpty())
        <ul class="divide-y divide-mate-borde border border-mate-borde rounded-md bg-white">
            @foreach ($credencialesHuella as $c)
                <li class="flex items-center justify-between gap-3 px-3 py-2 text-sm">
                    <div class="min-w-0">
                        <p class="font-medium truncate">{{ $c->nombre }}</p>
                        <p class="text-xs text-mate-tinta/50">
                            Activado el {{ $c->created_at->format('d/m/Y') }}
                            · {{ $c->ultimo_uso_at ? 'último uso ' . $c->ultimo_uso_at->format('d/m/Y H:i') : 'sin usar todavía' }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('staff.huella.eliminar', $c) }}"
                        onsubmit="return confirm('¿Quitar el ingreso con huella de {{ $c->nombre }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs underline text-mate-tinta/60 hover:text-red-700">Quitar</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif

    <div id="huella-perfil-acciones" style="display: none;">
        <button type="button" id="huella-perfil-boton"
            class="w-full bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md py-2.5 text-sm font-medium">
            {{ $credencialesHuella->isEmpty() ? 'Activar en este dispositivo' : 'Activar también en este dispositivo' }}
        </button>
        <p id="huella-perfil-error" class="text-xs text-red-700 mt-2" style="display: none;"></p>
    </div>
    <p id="huella-perfil-no-soportado" class="text-xs text-mate-tinta/60" style="display: none;">
        Este navegador no permite ingresar con huella. Probá con Chrome, Edge o Safari actualizados.
    </p>
</div>

<script>
    (() => {
        if (!window.EclesHuella.soportado()) {
            document.getElementById('huella-perfil-no-soportado').style.display = '';
            return;
        }

        const acciones = document.getElementById('huella-perfil-acciones');
        const boton = document.getElementById('huella-perfil-boton');
        const error = document.getElementById('huella-perfil-error');
        const textoOriginal = boton.textContent;
        acciones.style.display = '';

        boton.addEventListener('click', async () => {
            boton.disabled = true;
            boton.textContent = 'Esperando tu huella...';
            error.style.display = 'none';
            try {
                await window.EclesHuella.registrar(@js(route('staff.huella.registro.opciones')), @js(route('staff.huella.registro')));
                window.location.reload();
            } catch (e) {
                error.textContent = window.EclesHuella.mensajeError(e);
                error.style.display = '';
                boton.disabled = false;
                boton.textContent = textoOriginal;
            }
        });
    })();
</script>
