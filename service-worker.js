const CACHE_NAME = 'pegachave-v14';
const ASSETS = [
  '/',
  '/index.php',
  '/consulta',
  '/regras',
  '/logo_pwa.jpg',
  '/assets/css/quiosque.css',
  '/assets/js/quiosque.js',
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
  'https://unpkg.com/html5-qrcode'
];

// === IndexedDB Wrapper ===
const DB_NAME = 'PegaChaveDB';
const STORE_NAME = 'offline_scans';

function openDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, 1);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function getOfflineScans() {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE_NAME, 'readonly');
        const req = tx.objectStore(STORE_NAME).getAll();
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function deleteOfflineScan(id) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const req = tx.objectStore(STORE_NAME).delete(id);
        req.onsuccess = () => resolve();
        req.onerror = () => reject(req.error);
    });
}

// === Background Sync ===
self.addEventListener('sync', event => {
    if (event.tag === 'sync-pegachave-scans') {
        event.waitUntil(syncOfflineScans());
    }
});

async function syncOfflineScans() {
    const scans = await getOfflineScans();
    if (scans.length === 0) return;

    const pathParts = self.location.pathname.split('/');
    pathParts.pop(); // remove service-worker.js
    const basePath = pathParts.join('/');

    for (let item of scans) {
        try {
            const response = await fetch(`${self.location.origin}${basePath}/api/processar_scan`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ qr_code: item.code })
            });
            
            if (response.ok) {
                await deleteOfflineScan(item.id);
            } else {
                break; // Se a API retornar erro de servidor, para e tenta depois
            }
        } catch (err) {
            console.error("SW Background Sync Falhou:", err);
            break;
        }
    }
    
    // Notifica o frontend que o sync terminou
    const clients = await self.clients.matchAll();
    clients.forEach(client => client.postMessage('sync-complete'));
}

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
