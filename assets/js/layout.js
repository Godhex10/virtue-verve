/* ==========================================================
   Virtue & Verve — shared page furniture
   The header, footer, cart drawer and pop-ups live here once,
   and every page pulls them in. Needs data.js loaded first.
   ========================================================== */
(function () {
  const page = (location.pathname.split('/').pop() || 'index.html').toLowerCase();
  const here = href => href === page ? ' class="current" aria-current="page"' : '';
  const gem = '<i class="fa-solid fa-gem"></i>';
  const safe = t => String(t).replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
  const strip = ANNOUNCE.map(t => `<span>${safe(t)}</span>${gem}`).join('');
  const colLink = c => `<a href="category.html?c=${c.id}">${c.label}</a>`;
  const cats = COLLECTIONS.filter(c => c.type === 'category'), edits = COLLECTIONS.filter(c => c.type === 'edit');

  document.body.insertAdjacentHTML('afterbegin', `
  <div class="preloader" id="preloader" aria-hidden="true">
    <svg viewBox="0 0 120 120" class="preloader-bag">
      <path d="M42 50C42 18 78 18 78 50" />
      <path d="M26 50H94Q100 50 101 56L107 96Q108 106 98 106H22Q12 106 13 96L19 56Q20 50 26 50Z" />
      <path d="M54 76C46 68 50 61 60 68C70 61 74 68 66 76L60 82Z" class="preloader-heart" />
    </svg>
    <p class="preloader-name">Virtue <span>&amp;</span> Verve</p>
  </div>
  <div class="cursor-ring" aria-hidden="true"></div>

  <div class="announce"><div class="announce-track">${strip}${strip}</div></div>

  <header class="header" id="header">
    <div class="container header-inner">
      <button class="burger" id="burger" aria-label="Open menu" aria-expanded="false"><span></span><span></span></button>
      <a href="index.html" class="logo" aria-label="Virtue and Verve home">Virtue <span>&amp;</span> Verve</a>
      <nav class="nav" id="nav" aria-label="Main">
        <a href="shop.html"${here('shop.html')}>Shop</a>
        <a href="category.html?c=new-in">New in</a>
        <div class="nav-drop">
          <button class="nav-drop-btn" aria-haspopup="true">Collections <i class="fa-solid fa-chevron-down"></i></button>
          <div class="nav-menu">
            <div><h5>By shape</h5>${cats.map(colLink).join('')}</div>
            <div><h5>Edits</h5>${edits.map(colLink).join('')}</div>
          </div>
        </div>
        <a href="index.html#story">Our Story</a>
        <a href="contact.html"${here('contact.html')}>Contact</a>
      </nav>
      <div class="header-actions">
        <button class="icon-btn" id="searchBtn" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
        <button class="icon-btn" id="wishBtn" aria-label="Wishlist"><i class="fa-regular fa-heart"></i><span class="badge" id="wishCount" hidden>0</span></button>
        <button class="icon-btn" id="cartBtn" aria-label="Open shopping bag"><i class="fa-solid fa-bag-shopping"></i><span class="badge" id="cartCount" hidden>0</span></button>
      </div>
    </div>
  </header>`);

  // Called by script.js once the page content has loaded
  window.mountChrome = () => document.body.insertAdjacentHTML('beforeend', `
  <footer class="footer">
    <div class="container footer-grid">
      <div>
        <a href="index.html" class="logo logo-light">Virtue <span>&amp;</span> Verve</a>
        <p>Bags with virtue, worn with verve.</p>
      </div>
      <div><h4>Shop</h4><a href="shop.html">All bags</a>${edits.map(colLink).join('')}</div>
      <div><h4>By shape</h4>${cats.map(colLink).join('')}</div>
      <div><h4>Help</h4><a href="delivery.html">Delivery &amp; returns</a><a href="contact.html">Contact us</a><a href="privacy.html">Privacy policy</a><a href="terms.html">Terms &amp; conditions</a></div>
      <div><h4>Follow along</h4><a href="#"><i class="fa-brands fa-instagram"></i> Instagram</a><a href="#"><i class="fa-brands fa-tiktok"></i> TikTok</a><a href="#"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a></div>
    </div>
    <div class="container footer-base">
      <span>© ${new Date().getFullYear()} Virtue &amp; Verve. All rights reserved.</span>
      <span class="script">made with love <i class="fa-solid fa-heart"></i></span>
    </div>
  </footer>

  <div class="overlay" id="overlay"></div>

  <div class="search-panel" id="searchPanel" role="dialog" aria-label="Search">
    <form class="container search-form" action="search.html" role="search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="search" name="q" id="searchInput" placeholder="Search for a bag, colour or style" autocomplete="off" aria-label="Search">
      <button type="button" class="close" data-close aria-label="Close search"><i class="fa-solid fa-xmark"></i></button>
    </form>
    <div class="container search-suggest" id="searchSuggest"></div>
  </div>

  <aside class="drawer" id="drawer" aria-label="Shopping bag" aria-hidden="true">
    <div class="drawer-head">
      <h3>Your bag <span class="script">of joy</span></h3>
      <button class="close" data-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="drawer-body" id="cartItems"></div>
    <div class="drawer-foot">
      <div class="subtotal"><span>Subtotal</span><strong id="subtotal"></strong></div>
      <div class="drawer-btns">
        <a href="cart.html" class="btn btn-ghost"><span>View bag</span></a>
        <a href="checkout.html" class="btn btn-primary" id="checkout"><span>Checkout</span><i class="fa-solid fa-lock"></i></a>
      </div>
      <small><i class="fa-solid fa-gift"></i> Gift wrapping included, always.</small>
    </div>
  </aside>

  <div class="modal" id="modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-card">
      <button class="close" data-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
      <div class="modal-media" id="modalMedia"></div>
      <div class="modal-info" id="modalInfo"></div>
    </div>
  </div>

  <div class="toast" id="toast" role="status"></div>`);
})();
