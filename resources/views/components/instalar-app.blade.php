{{-- Guía para instalar EclesTres como app (PWA). Se oculta sola si ya está instalada o si la cerraron. --}}
<div data-instalar-app
    x-data="{
        visible: false,
        abierta: false,
        tab: 'android',
        puedeInstalar: false,
        init() {
            const instalada = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
            let cerrada = false;
            try { cerrada = localStorage.getItem('eclestres_instalar_cerrada') === '1'; } catch (e) {}
            if (instalada || cerrada) return;

            const ua = navigator.userAgent;
            const esIos = /iphone|ipad|ipod/i.test(ua) || (/macintosh/i.test(ua) && navigator.maxTouchPoints > 1);
            this.tab = esIos ? 'ios' : (/android/i.test(ua) ? 'android' : 'pc');
            this.puedeInstalar = !!window.eclesInstalar;
            this.visible = true;

            window.addEventListener('eclestres-instalable', () => { this.puedeInstalar = true; });
            window.addEventListener('appinstalled', () => { this.visible = false; });
        },
        async instalar() {
            const evento = window.eclesInstalar;
            if (!evento) return;
            evento.prompt();
            const { outcome } = await evento.userChoice;
            window.eclesInstalar = null;
            this.puedeInstalar = false;
            if (outcome === 'accepted') this.visible = false;
        },
        cerrar() {
            this.visible = false;
            try { localStorage.setItem('eclestres_instalar_cerrada', '1'); } catch (e) {}
        },
    }"
    x-show="visible" style="display: none;"
    {{ $attributes->merge(['class' => 'bg-mate-superficie border border-mate-borde rounded-md']) }}>

    {{-- En celular: título arriba y botones en un renglón aparte. En pantallas grandes: todo en una línea. --}}
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 px-4 py-3">
        <span class="text-xl leading-none">📲</span>
        <button type="button" @click="abierta = !abierta" class="flex-1 min-w-0 text-left text-sm">
            <span class="font-medium">Instalá EclesTres en tu celular o compu</span>
            <span class="block text-xs text-mate-tinta/60">Queda con su ícono, como una app. Gratis y sin tiendas.</span>
        </button>
        <button type="button" @click="cerrar()" class="sm:order-last text-mate-tinta/40 hover:text-mate-tinta text-lg leading-none self-start sm:self-center" aria-label="No mostrar más">&times;</button>
        <div class="w-full sm:w-auto flex items-center gap-3 pl-8 sm:pl-0">
            <button type="button" x-show="puedeInstalar" @click="instalar()"
                class="bg-mate-salvia hover:bg-mate-salvia-oscuro text-white rounded-md px-3 py-1.5 text-xs font-medium whitespace-nowrap">
                Instalar ahora
            </button>
            <button type="button" @click="abierta = !abierta" class="text-xs text-mate-salvia font-medium whitespace-nowrap"
                x-text="abierta ? 'Ocultar pasos' : 'Ver cómo'"></button>
        </div>
    </div>

    <div x-show="abierta" x-collapse style="display: none;">
        <div class="px-4 pb-4 border-t border-mate-borde pt-3">
            <div class="flex gap-1 mb-3 text-xs font-medium" role="tablist">
                @foreach (['android' => '🤖 Android', 'ios' => '📱 iPhone', 'pc' => '💻 Compu'] as $clave => $nombre)
                    <button type="button" role="tab" @click="tab = '{{ $clave }}'"
                        :class="tab === '{{ $clave }}' ? 'bg-mate-salvia text-white border-mate-salvia' : 'bg-white border-mate-borde text-mate-tinta/70'"
                        class="flex-1 border rounded-md px-2 py-1.5 whitespace-nowrap">{{ $nombre }}</button>
                @endforeach
            </div>

            <ol x-show="tab === 'android'" class="list-decimal pl-5 space-y-1.5 text-sm text-mate-tinta/80">
                <li>Abrí <strong>eclestres.com</strong> en <strong>Chrome</strong>.</li>
                <li>Tocá el menú <strong>⋮</strong> (arriba a la derecha).</li>
                <li>Elegí <strong>Instalar app</strong> o <strong>Agregar a pantalla de inicio</strong>.</li>
                <li>Confirmá con <strong>Instalar</strong>. El ícono <strong>EcT</strong> aparece en tu pantalla de inicio.</li>
            </ol>

            <ol x-show="tab === 'ios'" style="display: none;" class="list-decimal pl-5 space-y-1.5 text-sm text-mate-tinta/80">
                <li>Abrí <strong>eclestres.com</strong> en <strong>Safari</strong> (desde otros navegadores puede no aparecer la opción).</li>
                <li>Tocá <strong>Compartir</strong>: el cuadrado con una flecha hacia arriba (abajo en el centro en iPhone, arriba a la derecha en iPad).</li>
                <li>Deslizá hacia abajo y tocá <strong>Agregar a inicio</strong>. Si no la ves, tocá <strong>Editar acciones</strong> y agregala.</li>
                <li>Tocá <strong>Agregar</strong> (arriba a la derecha) y abrí EclesTres desde el ícono nuevo.</li>
            </ol>

            <ol x-show="tab === 'pc'" style="display: none;" class="list-decimal pl-5 space-y-1.5 text-sm text-mate-tinta/80">
                <li>Abrí <strong>eclestres.com</strong> en <strong>Chrome</strong> o <strong>Edge</strong>.</li>
                <li>Tocá el ícono de <strong>instalar</strong> que aparece a la derecha de la barra de direcciones (una pantallita con una flecha).</li>
                <li>Si no aparece: en Chrome, menú <strong>⋮ → Transmitir, guardar y compartir → Instalar página como app</strong>; en Edge, menú <strong>⋯ → Aplicaciones → Instalar este sitio como una aplicación</strong>.</li>
                <li>Confirmá con <strong>Instalar</strong>. Queda un acceso directo en el escritorio y en el menú Inicio.</li>
            </ol>

            {{ $slot }}
        </div>
    </div>
</div>
