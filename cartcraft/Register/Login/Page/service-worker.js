// Name of the cache
const CACHE_NAME = "cartcraft-cache-v1";

// List of files to cache
const ASSETS = [
    "/cartcraft/Register/login/Page/customersPage.php",
    "/cartcraft/Register/login/Page/css/customersPage.css",
    "/cartcraft/Register/login/Page//css/customersNav.css",
    "/cartcraft/Register/login/Page/image/craft.png",
    "/cartcraft/Register/login/Page/image/meow.jpg", // Replace with actual default profile image name if used

    "/cartcraft/Register/login/Page/css/all.min.css", // External resources
];

// Install the service worker
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log("Caching assets...");
            return cache.addAll(ASSETS);
        })
    );
});

// Activate the service worker
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        console.log("Deleting old cache:", cache);
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

// Fetch event to serve cached assets
self.addEventListener("fetch", (event) => {
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            // Serve cached file if available, otherwise fetch from the network
            return (
                cachedResponse ||
                fetch(event.request).catch(() => {
                    // Fallback for failed requests (e.g., offline)
                    if (event.request.destination === "document") {
                        return caches.match("/customersPage.php");
                    }
                })
            );
        })
    );
});
