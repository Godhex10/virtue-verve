/* ==========================================================
   Virtue & Verve — shared across every page
   (cart, wishlist, search, quick view, product cards, little delights)
   Needs data.js and layout.js loaded first.
   ========================================================== */

mountChrome(); // footer, cart drawer, search panel and pop-ups from layout.js

// ---------- Helpers ----------
const $ = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];
const money = n => CURRENCY + n.toLocaleString();
const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const product = id => PRODUCTS.find(p => p.id === id);
const zoneOf = region => ZONES.find(z => z.regions.includes(region)); // which delivery zone a state is in
const productURL = (p, c = 0) => `product.html?id=${p.id}${c ? `&colour=${c}` : ''}`;
const icon = (name, style = 'solid') => `<i class="fa-${style} fa-${name}"></i>`;
const photosOf = (p, c) => [p.colors[c][2], ...(p.colors[c][4] || [])]; // main photo first
const photo = (p, c, cls = '') => `<img class="${cls}" src="${p.colors[c][2]}" alt="${p.name} ${p.kind} in ${p.colors[c][0]}" loading="lazy">`;
const store = {
  get(key) { try { return JSON.parse(localStorage.getItem(key)) || []; } catch { return []; } },
  set(key, val) { try { localStorage.setItem(key, JSON.stringify(val)); } catch { /* storage unavailable */ } }
};

// Every word typed must appear somewhere in the bag's name, type, colours or description
function searchProducts(q, list = PRODUCTS) {
  const words = q.toLowerCase().split(/\s+/).filter(Boolean);
  if (!words.length) return [];
  return list.filter(p => {
    const cat = CATEGORIES.find(c => c.cat === p.cat);
    const hay = [p.name, p.kind, p.desc, cat.label, cat.one, p.tag || '', ...p.colors.flatMap(c => [c[0], c[3]])].join(' ').toLowerCase();
    return words.every(w => hay.includes(w));
  });
}

// ---------- State ----------
let cart = store.get('vv_cart').filter(i => product(i.id) && product(i.id).colors[i.color]);
let wishlist = store.get('vv_wish').filter(product);
const chosen = {}; // product id -> selected colour index
const subtotal = () => cart.reduce((n, i) => n + i.qty * product(i.id).price, 0);
const cartCount = () => cart.reduce((n, i) => n + i.qty, 0);

// ---------- Product cards ----------
const swatchesHTML = (p, active) => p.colors.length < 2 ? '' : `<div class="swatches">${p.colors.map(([name, hex], i) =>
  `<button class="swatch${i === active ? ' active' : ''}" style="--c:${hex}" data-color="${i}" aria-label="${name}" title="${name}"></button>`).join('')}</div>`;

function cardHTML(p, attrs = '') {
  const c = chosen[p.id] || 0, wished = wishlist.includes(p.id);
  return `
  <article class="card" data-id="${p.id}" data-cat="${p.cat}" ${attrs}>
    <div class="card-media">
      ${p.tag ? `<span class="tag${p.tag === 'New' ? ' new' : ''}">${p.tag}</span>` : ''}
      <button class="wish${wished ? ' on' : ''}" aria-label="Add ${p.name} to wishlist">${icon('heart', wished ? 'solid' : 'regular')}</button>
      <a class="card-link" href="${productURL(p, c)}" aria-label="View ${p.name}">${photo(p, c, 'card-photo')}</a>
      <button class="quick">Quick view</button>
    </div>
    <div class="card-body">
      <div class="card-row"><h3><a class="card-name" href="${productURL(p, c)}">${p.name}</a></h3><span class="price">${money(p.price)}</span></div>
      <p class="card-color">${p.kind} · <span>${p.colors[c][0]}</span></p>
      <div class="card-foot">
        ${swatchesHTML(p, c)}
        <button class="add">${icon('bag-shopping')}Add to bag</button>
      </div>
    </div>
  </article>`;
}

function paintCard(card, p, i) {
  if ((chosen[p.id] || 0) === i) return;
  chosen[p.id] = i;
  const img = $('.card-photo', card);
  img.src = p.colors[i][2];
  img.alt = `${p.name} ${p.kind} in ${p.colors[i][0]}`;
  img.classList.remove('swap'); void img.offsetWidth; img.classList.add('swap');
  $('.card-color span', card).textContent = p.colors[i][0];
  $$('.swatch', card).forEach((s, n) => s.classList.toggle('active', n === i));
  $$('.card-link, .card-name', card).forEach(a => { a.href = productURL(p, i); });
}

// Wire up clicks (and a gentle 3D tilt) for a grid of product cards
function bindGrid(grid) {
  grid.addEventListener('click', e => {
    const card = e.target.closest('.card');
    if (!card) return;
    const p = product(card.dataset.id);
    const swatch = e.target.closest('.swatch');

    if (swatch) paintCard(card, p, +swatch.dataset.color);
    else if (e.target.closest('.wish')) toggleWish(p, e.target.closest('.wish'));
    else if (e.target.closest('.add')) addToCart(p.id, chosen[p.id] || 0, 1, $('.card-photo', card));
    else if (e.target.closest('.quick')) openModal(p, chosen[p.id] || 0);
  });

  if (!matchMedia('(hover: hover) and (pointer: fine)').matches) return;
  grid.addEventListener('mousemove', e => {
    const card = e.target.closest('.card');
    if (!card) return;
    const r = card.getBoundingClientRect();
    const x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
    card.style.transform = `perspective(900px) rotateX(${-y * 5}deg) rotateY(${x * 5}deg) translateY(-5px)`;
  });
  grid.addEventListener('mouseout', e => {
    const card = e.target.closest('.card');
    if (card && !card.contains(e.relatedTarget)) card.style.transform = '';
  });
}

// ---------- Wishlist ----------
function renderWish() {
  const badge = $('#wishCount');
  badge.textContent = wishlist.length;
  badge.hidden = !wishlist.length;
  $('#wishBtn i').className = `fa-${wishlist.length ? 'solid' : 'regular'} fa-heart`;
}
function toggleWish(p, btn) {
  const on = !wishlist.includes(p.id);
  wishlist = on ? [...wishlist, p.id] : wishlist.filter(id => id !== p.id);
  store.set('vv_wish', wishlist);
  btn.classList.toggle('on', on);
  $('i', btn).className = `fa-${on ? 'solid' : 'regular'} fa-heart`;
  renderWish();
  if (on) {
    const r = btn.getBoundingClientRect();
    burst(r.left + r.width / 2, r.top + r.height / 2);
    toast(`${p.name} saved to your wishlist`, 'heart');
  }
}
$('#wishBtn').addEventListener('click', () => {
  toast(wishlist.length
    ? `On your wishlist: ${wishlist.map(id => product(id).name).join(', ')}`
    : 'Tap the heart on any bag to save it', 'heart');
});

// ---------- Cart ----------
const drawer = $('#drawer'), overlay = $('#overlay'), modal = $('#modal');
const qtyHTML = n => `<div class="qty"><button data-step="-1" aria-label="Decrease">${icon('minus')}</button><span>${n}</span><button data-step="1" aria-label="Increase">${icon('plus')}</button></div>`;

function renderCart() {
  const count = cartCount(), badge = $('#cartCount');
  badge.textContent = count;
  badge.hidden = !count;
  $('#subtotal').textContent = money(subtotal());
  $('#cartItems').innerHTML = cart.length ? cart.map((i, n) => {
    const p = product(i.id);
    return `<div class="item" data-index="${n}">
      <a class="item-img" href="${productURL(p, i.color)}">${photo(p, i.color)}</a>
      <div><h4>${p.name}</h4><small>${p.kind} · ${p.colors[i.color][0]}</small>${qtyHTML(i.qty)}</div>
      <div class="item-end"><strong>${money(p.price * i.qty)}</strong><button class="remove">Remove</button></div>
    </div>`;
  }).join('') : `<div class="empty"><div class="empty-icon">${icon('bag-shopping')}</div><strong>Your bag is empty</strong>She deserves a little something, doesn’t she?</div>`;
  store.set('vv_cart', cart);
  document.dispatchEvent(new Event('cart:change')); // the cart and checkout pages listen for this
}

function addToCart(id, color, qty, fromEl) {
  const line = cart.find(i => i.id === id && i.color === color);
  if (line) line.qty += qty; else cart.push({ id, color, qty });
  renderCart();
  const p = product(id);
  toast(`${p.name} in ${p.colors[color][0]} added to your bag`, 'bag-shopping');
  if (fromEl) fly(fromEl);
}
function changeQty(n, step) {
  cart[n].qty += step;
  if (cart[n].qty < 1) cart.splice(n, 1);
  renderCart();
}
function removeLine(n) { cart.splice(n, 1); renderCart(); }

// Shared by the drawer and the cart page: rows carry data-index, buttons carry data-step or .remove
function bindLines(box) {
  box.addEventListener('click', e => {
    const row = e.target.closest('[data-index]');
    if (!row) return;
    const step = e.target.closest('[data-step]');
    if (step) changeQty(+row.dataset.index, +step.dataset.step);
    else if (e.target.closest('.remove')) removeLine(+row.dataset.index);
  });
}
bindLines($('#cartItems'));

function showOverlay() { overlay.classList.add('open'); document.body.classList.add('lock'); }
function closeAll() {
  $$('.drawer, .modal, .overlay, .shop-side, .search-panel').forEach(el => el.classList.remove('open'));
  drawer.setAttribute('aria-hidden', 'true');
  modal.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('lock');
}
function openDrawer() { closeAll(); drawer.classList.add('open'); drawer.setAttribute('aria-hidden', 'false'); showOverlay(); }
$('#cartBtn').addEventListener('click', openDrawer);
overlay.addEventListener('click', closeAll);
document.addEventListener('click', e => { if (e.target.closest('[data-close]')) closeAll(); });
addEventListener('keydown', e => { if (e.key === 'Escape') closeAll(); });
$('#checkout').addEventListener('click', e => {
  if (!cart.length) { e.preventDefault(); toast('Add a bag you love first', 'heart'); }
});

// ---------- Search panel ----------
const searchPanel = $('#searchPanel'), searchInput = $('#searchInput'), searchSuggest = $('#searchSuggest');
function renderSuggest() {
  const q = searchInput.value.trim();
  if (!q) {
    searchSuggest.innerHTML = `<p class="suggest-label">Popular</p><div class="pills">${COLLECTIONS.map(c => `<a class="pill" href="category.html?c=${c.id}">${c.label}</a>`).join('')}</div>`;
    return;
  }
  const found = searchProducts(q);
  searchSuggest.innerHTML = found.length
    ? found.slice(0, 4).map(p => `<a class="suggest" href="${productURL(p)}"><span class="suggest-img">${photo(p, 0)}</span><span><strong>${p.name}</strong><small>${p.kind}</small></span><b>${money(p.price)}</b></a>`).join('')
      + `<a class="suggest-all" href="search.html?q=${encodeURIComponent(q)}">See all ${found.length} ${found.length === 1 ? 'result' : 'results'} ${icon('arrow-right')}</a>`
    : `<p class="suggest-label">Nothing for “${esc(q)}” yet — try a colour or a shape.</p>`;
}
$('#searchBtn').addEventListener('click', () => {
  closeAll();
  searchPanel.classList.add('open');
  showOverlay();
  renderSuggest();
  setTimeout(() => searchInput.focus(), 200);
});
searchInput.addEventListener('input', renderSuggest);

// ---------- Quick view ----------
function openModal(p, color) {
  let c = color, qty = 1;
  const media = $('#modalMedia'), info = $('#modalInfo');
  const paint = () => {
    media.innerHTML = photo(p, c);
    info.innerHTML = `
      <p class="script">${p.kind}</p>
      <h3>${p.name}</h3>
      <span class="price">${money(p.price)}</span>
      <p>${p.desc}</p>
      <div class="label">Colour — <b>${p.colors[c][0]}</b></div>
      ${swatchesHTML(p, c)}
      <div class="label">Quantity</div>
      ${qtyHTML(qty)}
      <div><button class="btn btn-primary" data-add>${icon('bag-shopping')}<span>Add to bag — ${money(p.price * qty)}</span></button></div>
      <a class="text-link" href="${productURL(p, c)}">View full details ${icon('arrow-right')}</a>`;
  };
  info.onclick = e => {
    const swatch = e.target.closest('.swatch'), step = e.target.closest('[data-step]');
    if (swatch) { c = +swatch.dataset.color; paint(); }
    else if (step) { qty = Math.max(1, qty + +step.dataset.step); paint(); }
    else if (e.target.closest('[data-add]')) {
      const card = $(`.card[data-id="${p.id}"]`);
      if (card) paintCard(card, p, c);
      addToCart(p.id, c, qty, media.firstElementChild);
      setTimeout(openDrawer, 850);
    }
  };
  paint();
  closeAll();
  modal.classList.add('open');
  modal.setAttribute('aria-hidden', 'false');
  showOverlay();
}

// ---------- Little delights ----------
let toastTimer;
function toast(msg, iconName = 'gem') {
  const el = $('#toast');
  el.innerHTML = icon(iconName) + msg;
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 2800);
}

function burst(x, y, n = 9) {
  for (let i = 0; i < n; i++) {
    const el = document.createElement('i');
    el.className = `burst fa-solid fa-${i % 3 ? 'heart' : 'star'}`;
    el.style.cssText = `left:${x}px;top:${y}px;color:${['#c9a96e', '#088178', '#9c7c42'][i % 3]}`;
    document.body.append(el);
    const a = Math.PI * 2 * i / n, dist = 34 + Math.random() * 44;
    el.animate([
      { transform: 'translate(-50%,-50%) scale(.4)', opacity: 1 },
      { transform: `translate(calc(-50% + ${Math.cos(a) * dist}px), calc(-50% + ${Math.sin(a) * dist - 26}px)) scale(1.4) rotate(${Math.random() * 90 - 45}deg)`, opacity: 0 }
    ], { duration: 800 + Math.random() * 400, easing: 'cubic-bezier(.2,.8,.2,1)' }).onfinish = () => el.remove();
  }
}

function fly(fromEl) {
  const from = fromEl.getBoundingClientRect(), to = $('#cartBtn').getBoundingClientRect();
  const clone = fromEl.cloneNode(true);
  clone.className = 'fly';
  clone.style.cssText = `left:${from.left}px;top:${from.top}px;width:${from.width}px;height:${from.height}px`;
  document.body.append(clone);
  const dx = to.left + to.width / 2 - (from.left + from.width / 2);
  const dy = to.top + to.height / 2 - (from.top + from.height / 2);
  clone.animate([
    { transform: 'none', opacity: 1 },
    { transform: `translate(${dx * .5}px, ${dy * .5 - 70}px) scale(.45) rotate(-10deg)`, opacity: 1, offset: .55 },
    { transform: `translate(${dx}px, ${dy}px) scale(.06) rotate(16deg)`, opacity: .4 }
  ], { duration: 850, easing: 'cubic-bezier(.4,0,.2,1)' }).onfinish = () => {
    clone.remove();
    const btn = $('#cartBtn');
    btn.classList.remove('bump'); void btn.offsetWidth; btn.classList.add('bump');
    burst(to.left + to.width / 2, to.top + to.height / 2, 6);
  };
}

// Falling gold petals inside a container
function petals(box, n = 14) {
  if (!box) return;
  for (let i = 0; i < n; i++) {
    const p = document.createElement('i');
    const size = 7 + Math.random() * 9;
    p.className = 'petal';
    p.style.cssText = `left:${Math.random() * 100}%;width:${size}px;height:${size}px;--x:${Math.random() * 240 - 120}px;animation-duration:${9 + Math.random() * 9}s;animation-delay:${-Math.random() * 18}s`;
    box.append(p);
  }
}
petals($('#petals'));

// Cursor ring
if (matchMedia('(hover: hover) and (pointer: fine)').matches) {
  const ring = $('.cursor-ring');
  let mx = 0, my = 0, rx = 0, ry = 0;
  addEventListener('mousemove', e => {
    mx = e.clientX; my = e.clientY;
    ring.classList.add('on');
    ring.classList.toggle('big', !!e.target.closest('a, button, .card-media'));
  });
  document.addEventListener('mouseleave', () => ring.classList.remove('on'));
  (function follow() {
    rx += (mx - rx) * .18; ry += (my - ry) * .18;
    ring.style.transform = `translate(${rx}px, ${ry}px)`;
    requestAnimationFrame(follow);
  })();
}

// Header + mobile nav
const header = $('#header'), burger = $('#burger'), nav = $('#nav');
const onScroll = () => header.classList.toggle('scrolled', scrollY > 40);
addEventListener('scroll', onScroll, { passive: true });
onScroll();
const toggleNav = open => {
  nav.classList.toggle('open', open);
  burger.classList.toggle('open', open);
  burger.setAttribute('aria-expanded', open);
  document.body.classList.toggle('lock', open);
};
burger.addEventListener('click', () => toggleNav(!nav.classList.contains('open')));
nav.addEventListener('click', e => { if (e.target.closest('a')) toggleNav(false); });

// Accordions (product details, FAQs)
document.addEventListener('click', e => {
  const head = e.target.closest('.acc-head');
  if (!head) return;
  const open = head.parentElement.classList.toggle('open');
  head.setAttribute('aria-expanded', open);
});

// Until the database is connected, orders, messages and sign-ups made here are left
// in this browser for the admin to collect (see assets/js/admin/admin.js)
function sendToAdmin(kind, row) {
  if (typeof LIVE !== 'undefined' && LIVE) {
    const insert = kind === 'messages'
      ? sb.from('messages').insert({ name: row.name, email: row.email, topic: row.topic || null, order_number: row.order || null, message: row.message })
      : kind === 'subscribers' ? sb.from('subscribers').insert({ email: row.email }) : null;
    if (insert) insert.then(({ error }) => {
      if (error && error.code !== '23505') { console.error(error); toast('Sorry, that didn’t go through — please try again', 'circle-exclamation'); }
    }); // 23505 = already on the newsletter, which is fine
    return;
  }
  try {
    const box = JSON.parse(localStorage.getItem('vv_inbox')) || {};
    (box[kind] = box[kind] || []).push(row);
    localStorage.setItem('vv_inbox', JSON.stringify(box));
  } catch { /* storage unavailable */ }
}

// Simple forms (contact, newsletter): thank the visitor and reset
document.addEventListener('submit', e => {
  const form = e.target.closest('form[data-thanks]');
  if (!form) return;
  e.preventDefault();
  if (form.dataset.inbox) sendToAdmin(form.dataset.inbox, { ...Object.fromEntries(new FormData(form)), created_at: new Date().toISOString() });
  const r = form.getBoundingClientRect();
  burst(r.left + r.width / 2, r.top + 40, 14);
  toast(form.dataset.thanks, 'envelope-open-text');
  form.reset();
});

// Fill in store details and delivery prices wherever a page asks for them
$$('[data-store]').forEach(el => { el.textContent = STORE[el.dataset.store]; });
$$('[data-delivery-table]').forEach(el => {
  el.innerHTML = ZONES.map(z => DELIVERY.filter(d => d.zone === z.id).map((d, i) =>
    `<tr>${i ? '' : `<td rowspan="${DELIVERY.filter(x => x.zone === z.id).length}"><strong>${esc(z.label)}</strong></td>`}<td>${esc(d.label)}</td><td>${esc(d.note)}</td><td>${d.price ? money(d.price) : 'Free'}</td></tr>`).join('')).join('');
});
if (!STORE.preview) $$('[data-preview]').forEach(el => el.remove());

// Scroll reveals — call again after adding [data-reveal] elements to the page
const io = new IntersectionObserver(entries => entries.forEach(en => {
  if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
}), { threshold: .12, rootMargin: '0px 0px -40px' });
const observeReveals = () => $$('[data-reveal]:not(.in)').forEach(el => io.observe(el));
observeReveals();

// Preloader — plays once per visit, then pages open straight away
const seen = document.documentElement.classList.contains('seen');
const start = performance.now();
function reveal() {
  setTimeout(() => {
    $('#preloader').classList.add('done');
    document.body.classList.add('loaded');
    try { sessionStorage.setItem('vv_seen', '1'); } catch { /* storage unavailable */ }
  }, seen ? 0 : Math.max(0, 1900 - (performance.now() - start)));
}
if (seen || document.readyState === 'complete') reveal(); else addEventListener('load', reveal);

renderCart();
renderWish();
