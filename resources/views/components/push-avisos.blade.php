{{-- Activar / desactivar avisos push de turnos y pedidos nuevos en este dispositivo --}}
@if (config('services.webpush.public_key'))
    <div class="mb-6 bg-mate-superficie border border-mate-borde rounded-md p-4 max-w-xl"
        x-data="{
            estado: 'cargando',
            ocupado: false,
            mensaje: '',
            clave: @js(config('services.webpush.public_key')),
            async init() {
                const soportado = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
                const esIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
                const instalada = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;

                if (!soportado) {
                    this.estado = esIos && !instalada ? 'ios-instalar' : 'no-soportado';
                    return;
                }
                if (Notification.permission === 'denied') {
                    this.estado = 'bloqueado';
                    return;
                }
                try {
                    const reg = await navigator.serviceWorker.ready;
                    const sub = await reg.pushManager.getSubscription();
                    if (sub && Notification.permission === 'granted') {
                        this.estado = 'activo';
                        this.enviar('{{ route('staff.empresa.push.suscribir') }}', this.datos(sub)).catch(() => {});
                    } else {
                        this.estado = 'inactivo';
                    }
                } catch (e) {
                    this.estado = 'inactivo';
                }
            },
            datos(sub) {
                const json = sub.toJSON();
                return {
                    endpoint: json.endpoint,
                    keys: json.keys,
                    contentEncoding: (PushManager.supportedContentEncodings || ['aes128gcm'])[0],
                };
            },
            enviar(url, cuerpo) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(cuerpo || {}),
                }).then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                });
            },
            claveBytes() {
                const relleno = '='.repeat((4 - this.clave.length % 4) % 4);
                const base64 = (this.clave + relleno).replace(/-/g, '+').replace(/_/g, '/');
                return Uint8Array.from(atob(base64), c => c.charCodeAt(0));
            },
            async activar() {
                this.ocupado = true;
                this.mensaje = '';
                try {
                    const permiso = await Notification.requestPermission();
                    if (permiso !== 'granted') {
                        this.estado = permiso === 'denied' ? 'bloqueado' : 'inactivo';
                        return;
                    }
                    const reg = await navigator.serviceWorker.ready;
                    const sub = (await reg.pushManager.getSubscription())
                        || await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: this.claveBytes() });
                    await this.enviar('{{ route('staff.empresa.push.suscribir') }}', this.datos(sub));
                    this.estado = 'activo';
                    this.probar();
                } catch (e) {
                    this.mensaje = 'No se pudieron activar los avisos. Probá de nuevo en un momento.';
                } finally {
                    this.ocupado = false;
                }
            },
            async desactivar() {
                this.ocupado = true;
                this.mensaje = '';
                try {
                    const reg = await navigator.serviceWorker.ready;
                    const sub = await reg.pushManager.getSubscription();
                    if (sub) {
                        await this.enviar('{{ route('staff.empresa.push.desuscribir') }}', { endpoint: sub.endpoint }).catch(() => {});
                        await sub.unsubscribe();
                    }
                    this.estado = 'inactivo';
                } catch (e) {
                    this.mensaje = 'No se pudieron desactivar los avisos.';
                } finally {
                    this.ocupado = false;
                }
            },
            async probar() {
                try {
                    const r = await this.enviar('{{ route('staff.empresa.push.probar') }}');
                    this.mensaje = r.enviados > 0
                        ? 'Te enviamos un aviso de prueba a este dispositivo.'
                        : 'No se pudo enviar el aviso de prueba.';
                } catch (e) {
                    this.mensaje = 'No se pudo enviar el aviso de prueba.';
                }
            },
        }">

        <div class="flex items-start gap-3">
            <span class="text-xl leading-none mt-0.5">🔔</span>
            <div class="flex-1 min-w-0 text-sm">
                <p class="font-medium">Avisos en este dispositivo</p>

                <p x-show="estado === 'cargando'" class="text-mate-tinta/60">Verificando...</p>

                <div x-show="estado === 'inactivo'" x-cloak style="display: none;">
                    <p class="text-mate-tinta/70 mb-2">Recibí una notificación cuando entre un turno o pedido nuevo, aunque la app esté cerrada.</p>
                    <button type="button" @click="activar()" :disabled="ocupado"
                        class="bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-4 py-2 text-sm font-medium disabled:opacity-60">
                        <span x-text="ocupado ? 'Activando...' : 'Activar avisos'"></span>
                    </button>
                </div>

                <div x-show="estado === 'activo'" x-cloak style="display: none;">
                    <p class="text-green-800 mb-2">✓ Activados: te vamos a avisar de turnos y pedidos nuevos.</p>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="probar()" :disabled="ocupado" class="text-sm underline text-mate-salvia">Enviar aviso de prueba</button>
                        <button type="button" @click="desactivar()" :disabled="ocupado" class="text-sm underline text-mate-tinta/60">Desactivar</button>
                    </div>
                </div>

                <p x-show="estado === 'bloqueado'" x-cloak style="display: none;" class="text-mate-tinta/70">
                    Las notificaciones están bloqueadas para este sitio. Habilitalas desde la configuración del navegador (el candado 🔒 junto a la dirección) y recargá la página.
                </p>

                <p x-show="estado === 'ios-instalar'" x-cloak style="display: none;" class="text-mate-tinta/70">
                    En iPhone primero instalá la app: tocá <strong>Compartir</strong> → <strong>Agregar a inicio</strong>, abrila desde el ícono y volvé a esta pantalla.
                </p>

                <p x-show="estado === 'no-soportado'" x-cloak style="display: none;" class="text-mate-tinta/70">
                    Este navegador no permite notificaciones. Probá con Chrome, Edge o Safari actualizados.
                </p>

                <p x-show="mensaje" x-text="mensaje" x-cloak style="display: none;" class="text-xs text-mate-tinta/60 mt-2"></p>
            </div>
        </div>
    </div>
@endif
