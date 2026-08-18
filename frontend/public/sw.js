const CACHE = 'jurihr-shell-v1'
const SHELL = ['/', '/offline.html', '/manifest.webmanifest', '/icon-192x192.png']

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(SHELL)))
  self.skipWaiting()
})

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
      .then(() => self.clients.claim()),
  )
})

self.addEventListener('fetch', (event) => {
  const { request } = event
  if (request.method !== 'GET') return

  const url = new URL(request.url)
  // API traffic must always hit the network: attendance, payslips and approvals
  // are never safe to serve from a cache.
  if (url.origin !== self.location.origin || url.pathname.startsWith('/api/')) return

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((response) => {
          caches.open(CACHE).then((cache) => cache.put('/', response.clone()))
          return response
        })
        .catch(() => caches.match('/').then((cached) => cached || caches.match('/offline.html'))),
    )
    return
  }

  event.respondWith(
    caches.match(request).then(
      (cached) =>
        cached ||
        fetch(request).then((response) => {
          if (response.ok) {
            caches.open(CACHE).then((cache) => cache.put(request, response.clone()))
          }
          return response.clone()
        }),
    ),
  )
})
