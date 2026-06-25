const CACHE_VERSION = 'collectinfos-pwa-v1';
const STATIC_CACHE = CACHE_VERSION + '-static';

const PRECACHE_URLS = [
    '/offline.html',
    '/manifest.webmanifest',
    '/css/collectinfos.css',
    '/js/collectinfos.js',
    '/js/pwa.js',
    '/favicon.ico',
    '/favicon-32.png',
    '/favicon-180.png',
    '/favicon.png',
    '/images/collectinfo-logo.jpg',
    '/vendor/fontawesome-free/css/all.min.css',
    '/vendor/fontawesome-free/webfonts/fa-solid-900.woff2',
    '/vendor/fontawesome-free/webfonts/fa-regular-400.woff2',
    '/vendor/fontawesome-free/webfonts/fa-brands-400.woff2',
];

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(STATIC_CACHE).then(function (cache) {
            return Promise.allSettled(
                PRECACHE_URLS.map(function (url) {
                    return cache.add(url);
                })
            );
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys
                    .filter(function (key) {
                        return key.startsWith('collectinfos-pwa-') && key !== STATIC_CACHE;
                    })
                    .map(function (key) {
                        return caches.delete(key);
                    })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', function (event) {
    var request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    var url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(function (response) {
                    return response;
                })
                .catch(function () {
                    return caches.match('/offline.html');
                })
        );
        return;
    }

    if (!isStaticAsset(url.pathname)) {
        return;
    }

    event.respondWith(
        caches.match(request).then(function (cached) {
            var networkFetch = fetch(request).then(function (response) {
                if (response && response.ok) {
                    var copy = response.clone();
                    caches.open(STATIC_CACHE).then(function (cache) {
                        cache.put(request, copy);
                    });
                }

                return response;
            });

            return cached || networkFetch;
        })
    );
});

function isStaticAsset(pathname) {
    if (/\.(css|js|woff2?|png|jpe?g|webp|ico|svg)$/i.test(pathname)) {
        return true;
    }

    return (
        pathname.indexOf('/css/') === 0 ||
        pathname.indexOf('/js/') === 0 ||
        pathname.indexOf('/vendor/') === 0 ||
        pathname.indexOf('/images/') === 0
    );
}
