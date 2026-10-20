const CACHE_NAME = 'fenix-sistema-dynamic-v1';

// Solo dejamos los recursos estáticos más críticos al instalar
const PRECACHE_ASSETS = [
    '/',
    '/logo2.png'
];

// Instalación inicial
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(PRECACHE_ASSETS))
    );
    self.skipWaiting();
});

// Activación y limpieza de cachés antiguas
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Interceptar peticiones: Estrategia "Network First, falling back to Cache" (Red primero, si falla usa caché)
self.addEventListener('fetch', event => {
    // Solo manejamos peticiones GET
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // Si la respuesta es válida, la clonamos y la guardamos en la caché dinámica automáticamente
                if (response && response.status === 200) {
                    let responseClone = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // Si no hay internet, buscamos el recurso en cualquier caché disponible
                return caches.match(event.request).then(cachedResponse => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Opcional: si la página no está en caché y no hay internet, se podria retornar una vista genérica offline
                    return caches.match('/');
                });
            })
    );
});