const CACHE_NAME = 'pegachave-v2';
const ASSETS = [
  '/',
  '/index.php',
  '/consulta.php',
  '/regras.php',
  '/logo_pwa.jpg',
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
  'https://unpkg.com/html5-qrcode'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(ASSETS).catch(err => console.warn('Falha ao cachear ativos no install:', err));
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  // Ignorar chamadas de API para evitar cacheamento de requisições dinâmicas
  if (event.request.url.includes('/api/')) {
    return;
  }
  
  // Estratégia Network-First: Tenta buscar da rede primeiro. Se falhar, busca no cache.
  event.respondWith(
    fetch(event.request).then(response => {
      // Se a requisição for bem-sucedida, atualiza o cache local
      if (response.status === 200 && event.request.url.startsWith(self.location.origin)) {
        const responseClone = response.clone();
        caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, responseClone);
        });
      }
      return response;
    }).catch(() => {
      // Fallback para o cache se o dispositivo estiver offline
      return caches.match(event.request).then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse;
        }
        // Se for navegação de página e não achar no cache, retorna o index.php
        if (event.request.mode === 'navigate') {
          return caches.match('/index.php');
        }
      });
    })
  );
});
