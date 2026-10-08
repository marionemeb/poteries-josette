// Service worker of the site. Its only job: some browsers (Samsung Internet,
// older Chrome/Edge) only offer "Install" / "Add to home screen" when the site
// has one with a real fetch handler. It caches nothing but a small offline
// page, so the site itself always comes fresh from the network.
const CACHE = 'poteries-josette-v1';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE)
            .then(function (cache) { return cache.add(new Request(OFFLINE_URL, { cache: 'reload' })); })
            .then(function () { return self.skipWaiting(); })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys()
            .then(function (keys) {
                return Promise.all(keys.filter(function (key) { return key !== CACHE; }).map(function (key) {
                    return caches.delete(key);
                }));
            })
            .then(function () { return self.clients.claim(); })
    );
});

// Page loads go to the network as usual; only when it fails (no connection)
// does the visitor get the offline page instead of the browser's error.
self.addEventListener('fetch', function (event) {
    if (event.request.mode !== 'navigate') {
        return;
    }
    event.respondWith(
        fetch(event.request).catch(function () {
            return caches.match(OFFLINE_URL);
        })
    );
});
