// Service worker de EclesTres.
//
// Criterio: no romper nada de lo que ya funciona.
// - Las páginas (HTML) SIEMPRE se piden a la red: tienen sesión, token CSRF y datos en vivo,
//   así que nunca se guardan en caché. Solo si no hay conexión se muestra /offline.html.
// - Solo se cachean archivos estáticos: /build (CSS/JS con hash en el nombre), íconos y fuentes.
// - POST, peticiones a la API de notificaciones, PDFs, imágenes subidas, etc. pasan de largo.

const VERSION = 'v3';
const CACHE_ESTATICOS = `eclestres-estaticos-${VERSION}`;
const CACHE_FUENTES = `eclestres-fuentes-${VERSION}`;
const OFFLINE_URL = '/offline.html';

const PRECACHE = [
    OFFLINE_URL,
    '/favicon.svg',
    '/icons/icon-192.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_ESTATICOS)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((claves) => Promise.all(
                claves
                    .filter((clave) => clave.startsWith('eclestres-') && ![CACHE_ESTATICOS, CACHE_FUENTES].includes(clave))
                    .map((clave) => caches.delete(clave))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // Navegación: red primero, página offline si falla. Nunca se cachea el HTML.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Assets compilados por Vite: el nombre cambia con cada build, se pueden cachear para siempre.
    if (url.origin === self.location.origin && url.pathname.startsWith('/build/')) {
        event.respondWith(cachePrimero(request, CACHE_ESTATICOS));
        return;
    }

    // Íconos propios: se muestran desde caché y se actualizan en segundo plano.
    if (url.origin === self.location.origin && (url.pathname.startsWith('/icons/') || url.pathname === '/favicon.svg')) {
        event.respondWith(cacheYActualizar(request, CACHE_ESTATICOS));
        return;
    }

    // Google Fonts.
    if (url.origin === 'https://fonts.googleapis.com' || url.origin === 'https://fonts.gstatic.com') {
        event.respondWith(cacheYActualizar(request, CACHE_FUENTES));
        return;
    }

    // Todo lo demás va directo a la red, igual que sin service worker.
});

// Avisos push de turnos y pedidos nuevos (los envía app/Services/PushService.php).
self.addEventListener('push', (event) => {
    let datos = {};
    try {
        datos = event.data ? event.data.json() : {};
    } catch (e) {
        datos = { body: event.data ? event.data.text() : '' };
    }

    event.waitUntil(
        self.registration.showNotification(datos.title || 'EclesTres', {
            body: datos.body || '',
            icon: datos.icon || '/icons/icon-192.png',
            badge: '/icons/icon-192.png',
            tag: datos.tag,
            renotify: !!datos.tag,
            data: { url: datos.url || '/' },
        })
    );
});

// Al tocar el aviso: si la app ya está abierta se lleva esa ventana a la sección; si no, se abre una nueva.
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const destino = new URL(event.notification.data?.url || '/', self.location.origin).href;
    event.waitUntil(abrirDestino(destino));
});

async function abrirDestino(destino) {
    // Solo las ventanas controladas por este service worker se pueden navegar.
    const ventanas = await self.clients.matchAll({ type: 'window' });

    // Primero navegar (no necesita el permiso del clic) y recién después enfocar,
    // así si el enfoque falla la ventana igual queda en la sección correcta.
    for (const ventana of ventanas) {
        try {
            const navegada = await ventana.navigate(destino);
            if (navegada) {
                try { await navegada.focus(); } catch (e) {}
                return;
            }
        } catch (e) {
            // Probar con la siguiente ventana o abrir una nueva.
        }
    }

    try {
        await self.clients.openWindow(destino);
    } catch (e) {
        console.error('No se pudo abrir el destino del aviso', e);
    }
}

async function guardarEnCache(cache, request, respuesta) {
    if (respuesta && (respuesta.ok || respuesta.type === 'opaque')) {
        await cache.put(request, respuesta.clone());
    }
    return respuesta;
}

async function cachePrimero(request, nombreCache) {
    const cache = await caches.open(nombreCache);
    const enCache = await cache.match(request);
    if (enCache) return enCache;
    const respuesta = await fetch(request);
    return guardarEnCache(cache, request, respuesta);
}

async function cacheYActualizar(request, nombreCache) {
    const cache = await caches.open(nombreCache);
    const enCache = await cache.match(request);
    const desdeRed = fetch(request)
        .then((respuesta) => guardarEnCache(cache, request, respuesta))
        .catch(() => enCache);
    return enCache || desdeRed;
}
