// ponytail: bump versi ini tiap kali daftar precache berubah -> cache lama otomatis dihapus di 'activate'.
const CACHE = 'bella-cache-v3';

// Aset statis saja. JANGAN pernah precache '/' atau HTML apa pun:
// navigasi halaman harus selalu dari network (kalau di-cache, redirect/HTML basi bikin ERR_FAILED).
const PRECACHE = [
  '/css/app.css',
  '/js/app.js',
  '/dist/assets/css/components.css',
  '/dist/assets/sounds/unknown-ll.mp3',
  '/dist/assets/sounds/finish-pulling.mp3',
  '/dist/assets/sounds/scan-customer-first.mp3',
  '/dist/assets/sounds/already-scan-ll.mp3',
  '/dist/assets/sounds/already-scan.mp3',
  '/dist/assets/sounds/already-pulled.mp3',
  '/dist/assets/sounds/fullfilled.mp3',
  '/dist/assets/sounds/notMatch-ll.mp3',
  '/dist/assets/sounds/notMatch.mp3',
  '/dist/assets/sounds/notExist.mp3',
  '/dist/assets/sounds/uncomplete-ll.mp3',
  '/dist/assets/sounds/unknown.mp3',
  '/dist/assets/css/components.css.map',
  '/dist/assets/css/style.css',
  '/dist/assets/modules/bootstrap/css/bootstrap.min.css',
  '/dist/assets/modules/fontawesome/css/all.min.css',
  '/dist/assets/modules/izitoast/css/iziToast.min.css',
  '/dist/assets/modules/bootstrap-social/bootstrap-social.css',
  '/dist/assets/modules/izitoast/js/iziToast.min.js',
  '/dist/assets/modules/jquery.min.js',
  '/dist/assets/modules/popper.js',
  '/dist/assets/modules/tooltip.js',
  '/dist/assets/modules/bootstrap/js/bootstrap.min.js',
  '/dist/assets/modules/nicescroll/jquery.nicescroll.min.js',
  '/dist/assets/modules/moment.min.js',
  '/dist/assets/modules/jquery.sparkline.min.js',
  '/dist/assets/modules/chart.min.js',
  '/dist/assets/modules/owlcarousel2/dist/owl.carousel.min.js',
  '/dist/assets/modules/summernote/summernote-bs4.js',
  '/dist/assets/modules/chocolat/dist/js/jquery.chocolat.min.js',
  '/dist/assets/modules/jqvmap/dist/jqvmap.min.css',
  '/dist/assets/modules/summernote/summernote-bs4.css',
  '/dist/assets/modules/owlcarousel2/dist/assets/owl.carousel.min.css',
  '/dist/assets/modules/owlcarousel2/dist/assets/owl.theme.default.min.css',
  '/dist/assets/js/stisla.js',
  '/dist/assets/js/scripts.js',
  '/dist/assets/js/custom.js',
  '/dist/assets/js/page/index.js',
];

self.addEventListener('install', function (event) {
  self.skipWaiting();
  event.waitUntil(
    // ponytail: satu aset gagal -> jangan gagalkan seluruh install; skip yang error.
    caches.open(CACHE).then(function (cache) {
      return Promise.all(
        PRECACHE.map(function (url) {
          return cache.add(url).catch(function () {});
        })
      );
    })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys.filter(function (k) { return k !== CACHE; })
            .map(function (k) { return caches.delete(k); })
      );
    }).then(function () { return self.clients.claim(); })
  );
});

const manifest = {
  name: 'Bella',
  short_name: 'Bella',
  start_url: '/',
  display: 'standalone',
  icons: [
    { src: '/icons/logo.png', sizes: '128x128', type: 'image/png' },
  ],
};

self.addEventListener('fetch', function (event) {
  var req = event.request;

  if (req.url.endsWith('manifest.json')) {
    event.respondWith(
      new Response(JSON.stringify(manifest), {
        headers: { 'Content-Type': 'application/json' },
      })
    );
    return;
  }

  // Navigasi halaman (dokumen HTML): selalu network, SW tidak ikut campur.
  // Ini yang mencegah ERR_FAILED dari cached redirect & bikin perubahan UI langsung kelihatan.
  if (req.mode === 'navigate') {
    return;
  }

  // Aset statis: cache-first, fallback network.
  event.respondWith(
    caches.match(req).then(function (res) {
      return res || fetch(req);
    })
  );
});
