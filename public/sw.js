// Service worker de EclesTres.
//
// Criterio: no romper nada de lo que ya funciona.
// - Las páginas (HTML) SIEMPRE se piden a la red: tienen sesión, token CSRF y datos en vivo,
//   así que nunca se guardan en caché. Solo si no hay conexión se muestra /offline.html.
// - Solo se cachean archivos estáticos: /build (CSS/JS con hash en el nombre), íconos y fuentes.
// - POST, peticiones a la API de notificaciones, PDFs, imágenes subidas, etc. pasan de largo.

const VERSION = 'v1';
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
