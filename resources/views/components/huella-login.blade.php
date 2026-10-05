{{-- Botón "Ingresar con huella" del login de staff. Solo aparece si el navegador lo soporta. --}}
<x-webauthn-js />

<div id="huella-login" class="mt-4" style="display: none;">
    <div class="flex items-center gap-3 my-4 text-xs text-mate-tinta/40">
        <span class="flex-1 border-t border-mate-borde"></span>o<span class="flex-1 border-t border-mate-borde"></span>
    </div>
    <button type="button" id="huella-login-boton"
        class="w-full border border-mate-salvia text-mate-salvia hover:bg-mate-salvia hover:text-white rounded-md py-2.5 text-sm font-medium transition-colors flex items-center justify-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
        </svg>
        <span id="huella-login-texto">Ingresar con huella, rostro o patrón</span>
    </button>
    <p id="huella-login-error" class="text-xs text-red-700 mt-2 text-center" style="display: none;"></p>
    <p class="text-xs text-mate-tinta/50 mt-2 text-center">Primero activalo desde <strong>Mi perfil</strong> en este dispositivo.</p>
</div>

<script>
    (() => {
        if (!window.EclesHuella.soportado()) return;

        const caja = document.getElementById('huella-login');
        const boton = document.getElementById('huella-login-boton');
        const texto = document.getElementById('huella-login-texto');
        const error = document.getElementById('huella-login-error');
        const textoOriginal = texto.textContent;
        caja.style.display = '';

        boton.addEventListener('click', async () => {
            boton.disabled = true;
            texto.textContent = 'Verificando...';
            error.style.display = 'none';
            try {
                const r = await window.EclesHuella.ingresar(@js(route('staff.huella.ingreso.opciones')), @js(route('staff.huella.ingreso')));
                window.location.href = r.redirect;
            } catch (e) {
                error.textContent = window.EclesHuella.mensajeError(e);
                error.style.display = '';
                boton.disabled = false;
                texto.textContent = textoOriginal;
            }
        });
    })();
</script>
