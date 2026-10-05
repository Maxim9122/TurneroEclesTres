@php
    $usuarioGuia = auth('web')->user();
    $esAdminGuia = $usuarioGuia->esAdmin();

    // Cada sección: título, a quién se le muestra, rutas donde es "la sección actual", link directo y contenido.
    $seccionesGuia = [
        'inicio' => [
            'titulo' => 'Primeros pasos',
            'icono' => '🚀',
            'solo_admin' => true,
            'rutas' => ['staff.empresa.dashboard'],
            'link' => null,
            'resumen' => 'Orden recomendado para dejar tu negocio listo para recibir reservas y pedidos.',
            'pasos' => [
                'Cargá la <strong>dirección</strong> y el <strong>logo y fondo</strong> de tu negocio.',
                'Definí el <strong>horario general</strong> de atención.',
                'Creá tus <strong>servicios</strong> (nombre, duración y precio).',
                'Cargá al menos un <strong>profesional activo</strong>: sin profesionales tus clientes no pueden reservar.',
                'Si vendés productos, creá <strong>categorías</strong> y <strong>productos</strong>.',
                'Compartí tu <strong>link público o código QR</strong> (en el panel principal).',
                '<strong>Instalá la app</strong> en tu celular o compu, <strong>activá los avisos</strong> para enterarte al instante de cada turno o pedido y, si querés, el <strong>ingreso con huella</strong> para no escribir la contraseña (ver las secciones de abajo).',
            ],
            'notas' => [],
        ],
        'app' => [
            'titulo' => 'Instalar la app',
            'icono' => '📲',
            'solo_admin' => false,
            'rutas' => [],
            'link' => null,
            'resumen' => 'EclesTres se puede instalar como una app en el celular o la compu: queda con su ícono, abre en pantalla completa y carga más rápido. No hace falta bajar nada de ninguna tienda.',
            'pasos' => [
                '<strong>Android (Chrome):</strong> entrá a eclestres.com, tocá el menú <strong>⋮</strong> y elegí <strong>Instalar app</strong> (o <em>Agregar a pantalla de inicio</em>).',
                '<strong>iPhone / iPad (Safari):</strong> entrá a eclestres.com, tocá <strong>Compartir</strong> (el cuadrado con la flecha: abajo en iPhone, arriba en iPad), deslizá y elegí <strong>Agregar a inicio</strong>. Si no aparece, tocá <em>Editar acciones</em> y agregala.',
                '<strong>Compu (Chrome o Edge):</strong> entrá a eclestres.com y tocá el ícono de <strong>instalar</strong> a la derecha de la barra de direcciones. Si no aparece: en Chrome, menú <strong>⋮ → Transmitir, guardar y compartir → Instalar página como app</strong>; en Edge, <strong>⋯ → Aplicaciones → Instalar este sitio como una aplicación</strong>.',
                'Listo: abrí EclesTres desde el ícono nuevo e ingresá con tu email y contraseña como siempre.',
            ],
            'notas' => [
                'En el panel principal también tenés el recuadro "Instalá EclesTres" con estos pasos y, en Android y compu, un botón "Instalar ahora".',
                'Si no hay internet, la app te muestra una pantalla de "Sin conexión" y se recarga sola cuando vuelve la señal.',
                'Las mejoras del sistema se aplican solas. Si alguna vez algo no se ve actualizado, cerrá la app del todo y volvé a abrirla.',
            ],
        ],
        'huella' => [
            'titulo' => 'Ingresar con huella, rostro o patrón',
            'icono' => '🔐',
            'solo_admin' => false,
            'rutas' => [],
            'link' => ['staff.mi-perfil.edit', 'Ir a Mi perfil para activarlo'],
            'resumen' => 'Entrá al sistema sin escribir la contraseña, usando el mismo desbloqueo de tu celular o compu: huella, rostro, patrón o PIN.',
            'pasos' => [
                '<strong>Activalo (una vez por dispositivo):</strong> desde el celular o compu donde lo quieras usar, ingresá con tu contraseña y andá a <strong>Mi perfil</strong>.',
                'En el recuadro <strong>Ingreso con huella, rostro o patrón</strong> tocá <strong>Activar en este dispositivo</strong> y confirmá con tu huella, rostro, patrón o PIN. Vas a ver el dispositivo en la lista (por ejemplo "Android · Chrome").',
                '<strong>Para ingresar:</strong> en la pantalla de ingreso tocá <strong>Ingresar con huella, rostro o patrón</strong>, poné la huella y entrás directo a tu panel.',
                '<strong>Si perdés o cambiás el celular:</strong> en Mi perfil tocá <strong>Quitar</strong> junto a ese dispositivo y deja de servir para entrar.',
            ],
            'notas' => [
                'Es seguro: tu huella nunca sale del dispositivo y la llave solo sirve para TU cuenta. Otra persona no puede usar su huella para entrar a la tuya.',
                'Ojo: cualquier huella, rostro o patrón que desbloquee ESE dispositivo puede usarlo. Activalo solo en celulares o compus personales, no en una compu compartida del negocio.',
                'Cada persona del equipo lo activa con su propio usuario. Si usás varios dispositivos, activalo en cada uno.',
                'La contraseña sigue funcionando siempre. Si el botón no aparece, actualizá el navegador (Chrome, Edge o Safari) o ingresá con tu contraseña.',
            ],
        ],
        'avisos' => [
            'titulo' => 'Avisos de turnos y pedidos',
            'icono' => '🔔',
            'solo_admin' => false,
            'rutas' => $esAdminGuia ? [] : ['staff.empresa.dashboard'],
            'link' => ['staff.empresa.dashboard', 'Ir al panel para activarlos'],
            'resumen' => 'Recibí una notificación en el celular o la compu cada vez que un cliente reserva un turno o hace un pedido, aunque la app esté cerrada.',
            'pasos' => [
                'En el <strong>panel principal</strong> buscá el recuadro <strong>🔔 Avisos en este dispositivo</strong> y tocá <strong>Activar avisos</strong>.',
                'Cuando el navegador pregunte, tocá <strong>Permitir</strong>. Te llega enseguida un aviso de prueba para confirmar que funciona (lo podés repetir con <em>Enviar aviso de prueba</em>).',
                'Desde ese momento, con cada reserva o compra te llega un aviso como <em>"📅 Nuevo turno #25 · Juan · Corte · 30/09 10:30 hs"</em> o <em>"🛒 Nuevo pedido #8"</em>.',
                '<strong>Tocá el aviso</strong> y se abre EclesTres directo en ese turno o pedido, resaltado.',
                'Con la app abierta, además aparece un <strong>cartel con sonido</strong> abajo a la derecha, y si estás en Turnos o Pedidos la pantalla se recarga sola.',
            ],
            'notas' => [
                'Se activan por dispositivo: hacelo en cada celular o compu donde quieras recibirlos. Cada persona del equipo activa los suyos con su usuario.',
                'En iPhone primero hay que instalar la app (ver "Instalar la app"), abrirla desde el ícono y activar los avisos desde ahí.',
                '¿No llegan? Revisá que las notificaciones del celular o la compu estén permitidas y que no esté activado "No molestar". Si dice "bloqueadas", habilitalas desde el candado 🔒 junto a la dirección y recargá. Si sigue sin andar, tocá Desactivar y volvé a activarlos.',
                'Los turnos que carga el propio negocio (manuales u orden de llegada) no envían aviso, porque ya los está cargando alguien del equipo.',
            ],
        ],
        'servicios' => [
            'titulo' => 'Servicios',
            'icono' => '✂️',
            'solo_admin' => true,
            'rutas' => ['staff.empresa.servicios.*'],
            'link' => ['staff.empresa.servicios.index', 'Ir a Servicios'],
            'resumen' => 'Son los trabajos que tus clientes pueden reservar como turno.',
            'pasos' => [
                'Desde el panel entrá a <strong>Gestionar servicios</strong> y tocá <strong>+ Nuevo servicio</strong>.',
                'Completá <strong>nombre</strong>, <strong>duración en minutos</strong> y <strong>precio</strong>. La duración define cuánto tiempo ocupa el turno en la agenda.',
                'Opcional: <strong>días para renovar</strong> (ej. 30). El sistema te avisa en <em>Próximas renovaciones</em> cuándo el cliente debería volver.',
                'Opcional: hasta <strong>3 fotos</strong> (una principal y dos adicionales).',
                'Guardá. Desde el listado podés <strong>Editar</strong> o <strong>Pausar</strong> un servicio para que deje de mostrarse sin borrarlo.',
            ],
            'notas' => [],
        ],
        'profesionales' => [
            'titulo' => 'Profesionales y sus horarios',
            'icono' => '👤',
            'solo_admin' => true,
            'rutas' => ['staff.empresa.profesionales.*'],
            'link' => ['staff.empresa.profesionales.index', 'Ir a Profesionales'],
            'resumen' => 'Las personas que atienden los turnos. Hace falta al menos uno activo para recibir reservas.',
            'pasos' => [
                'Entrá a <strong>Gestionar profesionales</strong> → <strong>+ Nuevo profesional</strong>.',
                'Cargá <strong>nombre</strong>, foto (opcional) y <strong>% de comisión</strong>: es lo que se lleva sobre lo recaudado; el resto queda para el negocio.',
                'Con el botón <strong>Horarios</strong> podés darle un horario propio (días + rango desde/hasta).',
                'Si no le cargás horario propio, <strong>usa el horario general</strong> del negocio.',
            ],
            'notas' => ['Podés pausar un profesional desde el listado sin perder su historial.'],
        ],
        'horarios' => [
            'titulo' => 'Horario general',
            'icono' => '🕒',
            'solo_admin' => true,
            'rutas' => ['staff.empresa.horario-general.*'],
            'link' => ['staff.empresa.horario-general.edit', 'Ir a Horario general'],
            'resumen' => 'Los días y horas en que atiende el negocio. Lo heredan los profesionales sin horario propio.',
            'pasos' => [
                'En <strong>Agregar rango horario</strong> elegí los días y el horario desde/hasta.',
                'Podés cargar varios rangos por día (ej. mañana y tarde por separado).',
                'Los días sin rangos figuran como <strong>sin atención</strong>.',
            ],
            'notas' => [],
        ],
        'turnos' => [
            'titulo' => 'Turnos',
            'icono' => '📅',
            'solo_admin' => false,
            'rutas' => ['staff.empresa.turnos.*'],
            'link' => ['staff.empresa.turnos.index', 'Ir a Turnos'],
            'resumen' => 'La agenda del día. Navegá por semana o elegí una fecha puntual.',
            'pasos' => [
                'Los turnos nuevos llegan como <strong>Pendiente</strong>. Tocá <strong>Confirmar</strong> para aceptarlos.',
                'Cuando el cliente fue atendido, marcá <strong>Completado</strong> (así cuenta para las comisiones). Si no vino, <strong>No asistió</strong>.',
                'Podés <strong>Cancelar</strong> un turno pendiente o confirmado.',
                '<strong>+ Orden de llegada</strong>: registra a un cliente que llega sin turno y se atiende en el momento.',
                'Desde cada turno: <strong>Enviar WhatsApp</strong>, <strong>Enviar comprobante</strong> o <strong>Editar profesional/servicios</strong>.',
                'Para encontrar un turno por su número, usá el <strong>buscador por N°</strong> del panel principal (opción <em>Turno</em>).',
            ],
            'notas' => [],
        ],
        'renovaciones' => [
            'titulo' => 'Próximas renovaciones',
            'icono' => '🔁',
            'solo_admin' => false,
            'rutas' => ['staff.empresa.renovaciones.*'],
            'link' => ['staff.empresa.renovaciones.index', 'Ir a Renovaciones'],
            'resumen' => 'Clientes a los que les toca repetir un servicio en los próximos 5 días (según los "días para renovar" del servicio).',
            'pasos' => [
                'Contactá al cliente por WhatsApp desde la lista.',
                'Con <strong>Agendar turno</strong> le reservás directamente con el mismo servicio.',
                'Cuando lo resolviste, marcá la renovación como <strong>resuelta</strong> para que salga de la lista.',
            ],
            'notas' => ['Abajo se muestran también las renovaciones vencidas de los últimos 30 días.'],
        ],
        'productos' => [
            'titulo' => 'Productos y categorías',
            'icono' => '📦',
            'solo_admin' => true,
            'rutas' => ['staff.empresa.productos.*', 'staff.empresa.categorias.*'],
            'link' => ['staff.empresa.productos.index', 'Ir a Productos'],
            'resumen' => 'Lo que tus clientes pueden comprar desde tu perfil público con el carrito.',
            'pasos' => [
                'Primero creá las <strong>categorías</strong> (desde Productos → Gestionar categorías).',
                'Tocá <strong>+ Nuevo producto</strong>: nombre, categoría, descripción, <strong>precio</strong> y <strong>stock disponible</strong>.',
                'Agregá hasta <strong>3 fotos</strong>.',
                'Podés pausar un producto desde el listado para ocultarlo sin borrarlo.',
            ],
            'notas' => [],
        ],
        'pedidos' => [
            'titulo' => 'Pedidos',
            'icono' => '🛒',
            'solo_admin' => false,
            'rutas' => ['staff.empresa.pedidos.*'],
            'link' => ['staff.empresa.pedidos.index', 'Ir a Pedidos'],
            'resumen' => 'Las compras de productos que hacen tus clientes. Filtrá por estado o por rango de fechas.',
            'pasos' => [
                'Recorrido de un pedido: <strong>Pendiente → Confirmado → En preparación → Listo / Enviado → Entregado</strong>.',
                'Podés cancelarlo si no se puede cumplir.',
                '<strong>Editar venta</strong> permite cambiar productos o la entrega; hay que indicar un <strong>motivo</strong>, que queda en el historial.',
                '<strong>Enviar remito</strong> le manda el comprobante al cliente.',
                'Para encontrar un pedido por su número, usá el <strong>buscador por N°</strong> del panel principal (opción <em>Pedido</em>).',
            ],
            'notas' => ['El número rojo en "Ver pedidos" del panel indica cuántos pedidos están pendientes.'],
        ],
        'operadores' => [
            'titulo' => 'Operadores',
            'icono' => '🧑‍💼',
            'solo_admin' => true,
            'rutas' => ['staff.empresa.operadores.*'],
            'link' => ['staff.empresa.operadores.index', 'Ir a Operadores'],
            'resumen' => 'Usuarios de tu equipo que manejan el día a día: turnos, renovaciones y pedidos (no ven la configuración).',
            'pasos' => [
                'Tocá <strong>Nuevo operador</strong> y cargá nombre, email y contraseña.',
                'Desde el listado podés <strong>cambiarle la contraseña</strong> o desactivarlo.',
            ],
            'notas' => [],
        ],
        'perfil-publico' => [
            'titulo' => 'Tu perfil público',
            'icono' => '🏪',
            'solo_admin' => true,
            'rutas' => ['staff.empresa.branding.*', 'staff.empresa.direccion.*'],
            'link' => ['staff.empresa.branding.edit', 'Configurar logo y fondo'],
            'resumen' => 'La página que ven tus clientes para reservar turnos y comprar.',
            'pasos' => [
                '<strong>Logo y fondo</strong>: subí tu logo (máx. 2MB) y elegí un color o imagen de fondo. Ves una vista previa.',
                '<strong>Dirección</strong>: ciudad, barrio, calle y altura. Ayuda a que te encuentren en el buscador.',
                'En el panel principal está tu <strong>link público</strong> y el <strong>código QR</strong> para descargar e imprimir.',
            ],
            'notas' => [],
        ],
        'reportes' => [
            'titulo' => 'Reportes',
            'icono' => '📊',
            'solo_admin' => true,
            'rutas' => ['staff.empresa.reportes.*'],
            'link' => ['staff.empresa.reportes.comisiones', 'Ir a Comisiones'],
            'resumen' => 'Números del negocio filtrados por rango de fechas.',
            'pasos' => [
                '<strong>Comisiones</strong>: por profesional, cuánto se recaudó, su comisión y lo que queda para el negocio. Solo cuenta turnos <strong>completados</strong>.',
                '<strong>Pedidos</strong>: total de pedidos, facturado (entregados), cancelados y detalle por estado y método de entrega.',
            ],
            'notas' => [],
        ],
        'mi-perfil' => [
            'titulo' => 'Mi perfil',
            'icono' => '⚙️',
            'solo_admin' => false,
            'rutas' => ['staff.mi-perfil.*'],
            'link' => ['staff.mi-perfil.edit', 'Ir a Mi perfil'],
            'resumen' => 'Tus datos personales, tu contraseña y el ingreso con huella.',
            'pasos' => [
                'Actualizá nombre, email y <strong>teléfono (WhatsApp)</strong>.',
                'Para cambiar la contraseña necesitás la actual.',
                'Desde acá también activás o quitás el <strong>ingreso con huella</strong> (ver la sección "Ingresar con huella, rostro o patrón").',
            ],
            'notas' => [],
        ],
    ];

    $seccionesGuia = array_filter($seccionesGuia, fn ($s) => $esAdminGuia || !$s['solo_admin']);

    $seccionActualGuia = null;
    foreach ($seccionesGuia as $clave => $s) {
        if ($s['rutas'] && request()->routeIs(...$s['rutas'])) {
            $seccionActualGuia = $clave;
            break;
        }
    }
@endphp

<div x-data="{
        abierta: false,
        seccion: null,
        actual: @js($seccionActualGuia),
        init() {
            try {
                this.abierta = localStorage.getItem('eclestres_guia_abierta') === '1';
                this.seccion = localStorage.getItem('eclestres_guia_seccion') || this.actual;
            } catch (e) {
                this.seccion = this.actual;
            }
        },
        guardar() {
            try {
                localStorage.setItem('eclestres_guia_abierta', this.abierta ? '1' : '0');
                localStorage.setItem('eclestres_guia_seccion', this.seccion || '');
            } catch (e) {}
        },
        alternar() {
            this.abierta = !this.abierta;
            if (this.abierta && !this.seccion) this.seccion = this.actual;
            this.guardar();
        },
        cerrar() {
            this.abierta = false;
            this.guardar();
        },
        elegir(clave) {
            this.seccion = this.seccion === clave ? null : clave;
            this.guardar();
        },
    }"
    @keydown.escape.window="if (abierta) cerrar()">

    {{-- Botón flotante: el aviso de notificaciones se muestra por encima de este botón --}}
    <button type="button" @click="alternar()"
        class="fixed bottom-5 right-5 z-40 w-10 h-10 rounded-full shadow-lg flex items-center justify-center text-lg font-display font-semibold transition-colors"
        :class="abierta ? 'bg-mate-tinta text-white' : 'bg-mate-salvia hover:bg-mate-salvia-oscuro text-white'"
        :aria-expanded="abierta"
        aria-label="Guía de ayuda" title="Guía de ayuda">
        ?
    </button>

    {{-- Panel de la guía: no bloquea la página, así se puede seguir usando el sistema con la guía abierta --}}
    <div x-show="abierta"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed z-40 bottom-[4.25rem] right-4 left-4 sm:left-auto sm:right-5 sm:w-96 max-h-[70vh] flex flex-col bg-mate-superficie border border-mate-borde rounded-lg shadow-xl"
        role="dialog" aria-label="Guía de uso"
        style="display: none;">

        <div class="flex items-center justify-between px-4 py-3 border-b border-mate-borde">
            <div>
                <p class="font-display text-lg leading-tight">Guía de uso</p>
                <p class="text-xs text-mate-tinta/60">Resumen de cómo manejar el sistema</p>
            </div>
            <button type="button" @click="cerrar()" class="text-mate-tinta/50 hover:text-mate-tinta text-2xl leading-none px-1" aria-label="Cerrar guía">&times;</button>
        </div>

        <div class="overflow-y-auto px-2 py-2">
            @foreach ($seccionesGuia as $clave => $s)
                <div class="border-b border-mate-borde/70 last:border-b-0">
                    <button type="button" @click="elegir('{{ $clave }}')"
                        class="w-full flex items-center gap-2 px-2 py-2.5 text-left text-sm hover:bg-mate-fondo/60 rounded-md">
                        <span class="w-5 text-center">{{ $s['icono'] }}</span>
                        <span class="flex-1 font-medium">{{ $s['titulo'] }}</span>
                        <span x-show="actual === '{{ $clave }}'"
                            class="text-[10px] uppercase tracking-wide bg-mate-salvia text-white rounded px-1.5 py-0.5">Estás acá</span>
                        <svg class="w-4 h-4 text-mate-tinta/50 transition-transform" :class="seccion === '{{ $clave }}' && 'rotate-180'"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="seccion === '{{ $clave }}'" x-collapse style="display: none;">
                        <div class="px-2 pb-3 text-sm text-mate-tinta/80 space-y-2">
                            <p>{{ $s['resumen'] }}</p>
                            <ol class="list-decimal pl-5 space-y-1">
                                @foreach ($s['pasos'] as $paso)
                                    <li>{!! $paso !!}</li>
                                @endforeach
                            </ol>
                            @foreach ($s['notas'] as $nota)
                                <p class="text-xs text-mate-tinta/60">💡 {{ $nota }}</p>
                            @endforeach
                            @if ($s['link'] && !request()->routeIs($s['link'][0], ...$s['rutas']))
                                <a href="{{ route($s['link'][0]) }}" class="inline-block text-mate-salvia font-medium">{{ $s['link'][1] }} →</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
