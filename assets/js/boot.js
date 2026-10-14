/* ==========================================================
   Virtue & Verve — starts every shop page
   Loads the database connection and the catalogue first, then the
   page's own scripts, so everything draws with the real bags and prices.

   Usage, at the end of <body>:
     <script src="assets/js/boot.js" data-page="shop.js"></script>
   (data-page is optional: leave it off for pages with no script of their own)
   ========================================================== */
(function () {
  const me = document.currentScript;
  const base = me.src.replace(/boot\.js(\?.*)?$/, '');
  const pageScript = me.dataset.page;
  const CACHE = 'vv_catalogue';

  const add = src => new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = src;
    s.async = false;
    s.onload = resolve;
    s.onerror = () => reject(new Error('Could not load ' + src));
    document.body.appendChild(s);
  });
  const show = () => document.documentElement.classList.add('ready');
  setTimeout(show, 4000); // never leave the page hidden, whatever happens

  (async () => {
    // The shop still opens (with the built-in catalogue) if the library can't be reached
    await add('https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2.45.4/dist/umd/supabase.min.js').catch(err => console.error(err));
    await add(base + 'config.js');
    await add(base + 'supa.js');
    await add(base + 'data.js');

    if (LIVE) {
      // Use the copy saved on the last visit straight away (if any) and refresh it quietly for next time.
      // Without a saved copy, wait for the database.
      let saved = null;
      try { saved = JSON.parse(localStorage.getItem(CACHE)); } catch { /* storage unavailable */ }
      const fresh = loadCatalogue().then(data => {
        try { localStorage.setItem(CACHE, JSON.stringify(data)); } catch { /* storage unavailable */ }
        return data;
      });
      let data = saved && saved.version === DATA_VERSION ? saved : null;
      if (data) fresh.catch(err => console.error('Catalogue refresh failed', err));
      else data = await fresh.catch(err => { console.error('Could not load the catalogue', err); return null; });
      if (data) applyCatalogue(data);
    }

    for (const file of ['layout.js', 'script.js', ...(pageScript ? [pageScript] : [])]) await add(base + file);
    show();
  })().catch(err => { console.error(err); show(); });
})();
