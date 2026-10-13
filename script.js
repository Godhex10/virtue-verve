/* ==========================================================
   Virtue & Verve — storefront
   ========================================================== */

// ---------- Store settings (edit these) ----------
const CURRENCY = '$';

// Each colour is [name, swatch hex, photo]. Add more colours to a product and swatches appear.
const PRODUCTS = [
  { id: 'seraphine', name: 'Seraphine', kind: 'Top-Handle', cat: 'top-handle', price: 189, tag: 'Bestseller',
    desc: 'Our signature. A structured top-handle in pebbled leather with a polished gold clasp — ladylike, but never shy.',
    colors: [['Verve Teal', '#0f7f86', 'images/seraphine.jpg']] },
  { id: 'amara', name: 'Amara', kind: 'Tote', cat: 'tote', price: 165, tag: 'New',
    desc: 'The carry-everything tote. Roomy enough for your laptop and your lipstick collection, in leather that only gets better with time.',
    colors: [['Caramel', '#b5651d', 'images/amara.jpg']] },
  { id: 'lola', name: 'Lola', kind: 'Crossbody', cat: 'crossbody', price: 129,
    desc: 'Quilted, curved and finished with a gold teardrop. Carry it by the handle or sling it on — made for brunch and everything after.',
    colors: [['Rosewater', '#d99a8f', 'images/lola.jpg']] },
  { id: 'belle', name: 'Belle', kind: 'Chain Clutch', cat: 'clutch', price: 98, tag: 'New',
    desc: 'A flap clutch with a delicate chain. Tuck it under your arm for dinner, drape it on your shoulder for dancing.',
    colors: [['Blush', '#f1b6c1', 'images/belle.jpg']] },
  { id: 'mimi', name: 'Mimi', kind: 'Pleated Wristlet', cat: 'clutch', price: 86, tag: 'Bestseller',
    desc: 'Small bag, big personality. A sculpted, pleated wristlet that fits your phone, your gloss and your best secrets.',
    colors: [['Vanilla', '#e9e4d6', 'images/mimi-vanilla.jpg'], ['Emerald', '#12a150', 'images/mimi-emerald.jpg']] },
  { id: 'eden', name: 'Eden', kind: 'Weekend Tote', cat: 'tote', price: 210,
    desc: 'Your weekend-away companion. Heritage tweed trimmed in deep green leather, generously sized and ready whenever you are.',
    colors: [['Forest Tweed', '#1f5a3c', 'images/eden.jpg']] },
  { id: 'nova', name: 'Nova', kind: 'Mini Top-Handle', cat: 'top-handle', price: 112,
    desc: 'The Seraphine’s little sister. All the polish in a palm-sized silhouette, with a turn-lock you’ll love to click.',
    colors: [['Blush', '#f0b4ae', 'images/nova.jpg']] },
  { id: 'cleo', name: 'Cleo', kind: 'Shoulder Bag', cat: 'crossbody', price: 138,
    desc: 'Soft, slouchy and quietly chic. A buttery shoulder bag in a shade that goes with absolutely everything.',
    colors: [['Sand', '#e6c9a8', 'images/cleo.jpg']] }
];

const CATEGORIES = [
  { cat: 'tote', label: 'Totes', note: 'Carry it all', img: 'images/amara.jpg' },
  { cat: 'top-handle', label: 'Top-handles', note: 'Polished & poised', img: 'images/nova.jpg' },
  { cat: 'crossbody', label: 'Crossbody', note: 'Hands-free', img: 'images/cat-crossbody.jpg' },
  { cat: 'clutch', label: 'Clutches', note: 'After dark', img: 'images/cat-clutch.jpg' }
];

const MOODS = [
  { label: 'Brunch', id: 'lola', color: 0, line: 'Mimosas at eleven. The Lola keeps your hands free for the important things.' },
  { label: 'Boss day', id: 'seraphine', color: 0, line: 'Walk in like you own the room. The Seraphine does half the talking for you.' },
  { label: 'Date night', id: 'belle', color: 0, line: 'Lipstick, keys, a little mystery. The Belle carries exactly enough.' },
  { label: 'Getaway', id: 'eden', color: 0, line: 'Out of office, into the weekend. The Eden packs for wherever you’re headed.' }
];

// ---------- Helpers ----------
const $ = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];
const money = n => CURRENCY + n.toLocaleString();
const product = id => PRODUCTS.find(p => p.id === id);
const icon = (name, style = 'solid') => `<i class="fa-${style} fa-${name}"></i>`;
const photo = (p, c, cls = '') => `<img class="${cls}" src="${p.colors[c][2]}" alt="${p.name} ${p.kind} in ${p.colors[c][0]}" loading="lazy">`;
const store = {
  get(key) { try { return JSON.parse(localStorage.getItem(key)) || []; } catch { return []; } },
  set(key, val) { try { localStorage.setItem(key, JSON.stringify(val)); } catch { /* storage unavailable */ } }
};

// ---------- State ----------
let cart = store.get('vv_cart').filter(i => product(i.id) && product(i.id).colors[i.color]);
let wishlist = store.get('vv_wish').filter(product);
const chosen = {}; // product id -> selected colour index

$('#year').textContent = new Date().getFullYear();

// ---------- Categories ----------
$('#cats').innerHTML = CATEGORIES.map((c, i) => `
  <a href="#shop" class="cat" data-cat="${c.cat}" data-reveal style="--d:${i * .1}s">
    <div class="cat-arch"><img src="${c.img}" alt="${c.label}" loading="lazy"></div>
    <h3>${c.label}</h3><small>${c.note}</small>
  </a>`).join('');

$('#cats').addEventListener('click', e => {
  const cat = e.target.closest('.cat');
  if (cat) setFilter(cat.dataset.cat);
});

// ---------- Product grid ----------
const grid = $('#grid');
const swatchesHTML = (p, active) => p.colors.length < 2 ? '' : `<div class="swatches">${p.colors.map(([name, hex], i) =>
  `<button class="swatch${i === active ? ' active' : ''}" style="--c:${hex}" data-color="${i}" aria-label="${name}" title="${name}"></button>`).join('')}</div>`;

grid.innerHTML = PRODUCTS.map((p, i) => {
  const wished = wishlist.includes(p.id);
  return `
  <article class="card" data-id="${p.id}" data-cat="${p.cat}" data-reveal style="--d:${(i % 4) * .08}s">
    <div class="card-media">
      ${p.tag ? `<span class="tag${p.tag === 'New' ? ' new' : ''}">${p.tag}</span>` : ''}
      <button class="wish${wished ? ' on' : ''}" aria-label="Add ${p.name} to wishlist">${icon('heart', wished ? 'solid' : 'regular')}</button>
      ${photo(p, 0, 'card-photo')}
      <button class="quick">Quick view</button>
    </div>
    <div class="card-body">
      <div class="card-row"><h3>${p.name}</h3><span class="price">${money(p.price)}</span></div>
      <p class="card-color">${p.kind} · <span>${p.colors[0][0]}</span></p>
      <div class="card-foot">
        ${swatchesHTML(p, 0)}
        <button class="add">${icon('bag-shopping')}Add to bag</button>
      </div>
    </div>
  </article>`;
}).join('');

function paintCard(card, p, i) {
  if (chosen[p.id] === i || (!chosen[p.id] && !i)) return;
  chosen[p.id] = i;
  const img = $('.card-photo', card);
  img.src = p.colors[i][2];
  img.alt = `${p.name} ${p.kind} in ${p.colors[i][0]}`;
  img.classList.remove('swap'); void img.offsetWidth; img.classList.add('swap');
  $('.card-color span', card).textContent = p.colors[i][0];
  $$('.swatch', card).forEach((s, n) => s.classList.toggle('active', n === i));
}

grid.addEventListener('click', e => {
  const card = e.target.closest('.card');
  if (!card) return;
  const p = product(card.dataset.id);
  const swatch = e.target.closest('.swatch');

  if (swatch) paintCard(card, p, +swatch.dataset.color);
  else if (e.target.closest('.wish')) toggleWish(p, e.target.closest('.wish'));
  else if (e.target.closest('.add')) addToCart(p.id, chosen[p.id] || 0, 1, $('.card-photo', card));
  else if (e.target.closest('.card-media')) openModal(p, chosen[p.id] || 0);
});

// Gentle 3D tilt on hover
if (matchMedia('(hover: hover) and (pointer: fine)').matches) {
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

// ---------- Filters ----------
function setFilter(cat) {
  $$('.filter').forEach(f => f.classList.toggle('active', f.dataset.filter === cat));
  let shown = 0;
  $$('.card', grid).forEach(card => {
    const show = cat === 'all' || card.dataset.cat === cat;
    card.classList.toggle('hide', !show);
    card.classList.remove('pop');
    if (show) {
      card.classList.add('in');
      void card.offsetWidth;
      card.style.animationDelay = `${shown++ * .06}s`;
      card.classList.add('pop');
    }
  });
}
$('#filters').addEventListener('click', e => {
  const f = e.target.closest('.filter');
  if (f) setFilter(f.dataset.filter);
});

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
  btn.innerHTML = icon('heart', on ? 'solid' : 'regular');
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

function renderCart() {
  const count = cart.reduce((n, i) => n + i.qty, 0);
  const badge = $('#cartCount');
  badge.textContent = count;
  badge.hidden = !count;
  $('#subtotal').textContent = money(cart.reduce((n, i) => n + i.qty * product(i.id).price, 0));
  $('#cartItems').innerHTML = cart.length ? cart.map((i, n) => {
    const p = product(i.id);
    return `<div class="item" data-index="${n}">
      <div class="item-img">${photo(p, i.color)}</div>
      <div><h4>${p.name}</h4><small>${p.kind} · ${p.colors[i.color][0]}</small>
        <div class="qty"><button data-step="-1" aria-label="Decrease">${icon('minus')}</button><span>${i.qty}</span><button data-step="1" aria-label="Increase">${icon('plus')}</button></div>
      </div>
      <div class="item-end"><strong>${money(p.price * i.qty)}</strong><button class="remove">Remove</button></div>
    </div>`;
  }).join('') : `<div class="empty"><div class="empty-icon">${icon('bag-shopping')}</div><strong>Your bag is empty</strong>She deserves a little something, doesn’t she?</div>`;
  store.set('vv_cart', cart);
}

function addToCart(id, color, qty, fromEl) {
  const line = cart.find(i => i.id === id && i.color === color);
  if (line) line.qty += qty; else cart.push({ id, color, qty });
  renderCart();
  const p = product(id);
  toast(`${p.name} in ${p.colors[color][0]} added to your bag`, 'bag-shopping');
  if (fromEl) fly(fromEl);
}

$('#cartItems').addEventListener('click', e => {
  const row = e.target.closest('.item');
  if (!row) return;
  const n = +row.dataset.index, step = e.target.closest('[data-step]');
  if (step) { cart[n].qty += +step.dataset.step; if (cart[n].qty < 1) cart.splice(n, 1); }
  else if (e.target.closest('.remove')) cart.splice(n, 1);
  else return;
  renderCart();
});

function openDrawer() { closeAll(); drawer.classList.add('open'); drawer.setAttribute('aria-hidden', 'false'); showOverlay(); }
function showOverlay() { overlay.classList.add('open'); document.body.classList.add('lock'); }
function closeAll() {
  [drawer, modal, overlay].forEach(el => el.classList.remove('open'));
  drawer.setAttribute('aria-hidden', 'true');
  modal.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('lock');
}
$('#cartBtn').addEventListener('click', openDrawer);
overlay.addEventListener('click', closeAll);
$$('[data-close]').forEach(b => b.addEventListener('click', closeAll));
addEventListener('keydown', e => { if (e.key === 'Escape') closeAll(); });
$('#checkout').addEventListener('click', () => {
  if (cart.length) toast('Checkout is in preview — payments go live at launch', 'lock');
  else toast('Add a bag you love first', 'heart');
});

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
      <div class="qty"><button data-step="-1" aria-label="Decrease">${icon('minus')}</button><span>${qty}</span><button data-step="1" aria-label="Increase">${icon('plus')}</button></div>
      <div><button class="btn btn-primary" data-add>${icon('bag-shopping')}<span>Add to bag — ${money(p.price * qty)}</span></button></div>`;
  };
  info.onclick = e => {
    const swatch = e.target.closest('.swatch'), step = e.target.closest('[data-step]');
    if (swatch) { c = +swatch.dataset.color; paint(); }
    else if (step) { qty = Math.max(1, qty + +step.dataset.step); paint(); }
    else if (e.target.closest('[data-add]')) {
      const card = $(`.card[data-id="${p.id}"]`, grid);
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

// ---------- Moods ----------
const moodTabs = $('#moodTabs'), moodBag = $('#moodBag'), moodInfo = $('#moodInfo');
moodTabs.innerHTML = MOODS.map((m, i) => `<button class="mood-tab" role="tab" data-mood="${i}">${m.label}</button>`).join('');
function setMood(i) {
  const m = MOODS[i], p = product(m.id);
  $$('.mood-tab').forEach((t, n) => t.classList.toggle('active', n === i));
  moodBag.classList.remove('swap');
  moodBag.innerHTML = photo(p, m.color);
  void moodBag.offsetWidth;
  moodBag.classList.add('swap');
  moodInfo.innerHTML = `
    <h3>The ${p.name} in ${p.colors[m.color][0]}</h3>
    <p>${m.line}</p>
    <div><span class="price">${money(p.price)}</span><button class="btn btn-gold" data-mood-add="${i}">${icon('bag-shopping')}<span>Add to bag</span></button></div>`;
}
moodTabs.addEventListener('click', e => { const t = e.target.closest('.mood-tab'); if (t) setMood(+t.dataset.mood); });
moodInfo.addEventListener('click', e => {
  const b = e.target.closest('[data-mood-add]');
  if (b) { const m = MOODS[+b.dataset.moodAdd]; addToCart(m.id, m.color, 1, moodBag.firstElementChild); }
});
setMood(0);

// ---------- Love notes slider ----------
const track = $('#notesTrack'), dots = $('#notesDots'), noteCount = track.children.length;
let noteIndex = 0, noteTimer;
dots.innerHTML = Array.from({ length: noteCount }, (_, i) => `<button aria-label="Review ${i + 1}"></button>`).join('');
function showNote(i) {
  noteIndex = (i + noteCount) % noteCount;
  track.style.transform = `translateX(-${noteIndex * 100}%)`;
  $$('button', dots).forEach((d, n) => d.classList.toggle('active', n === noteIndex));
  clearInterval(noteTimer);
  noteTimer = setInterval(() => showNote(noteIndex + 1), 6000);
}
dots.addEventListener('click', e => { const i = $$('button', dots).indexOf(e.target); if (i > -1) showNote(i); });
showNote(0);

// ---------- Newsletter ----------
$('#clubForm').addEventListener('submit', e => {
  e.preventDefault();
  e.target.reset();
  const r = e.target.getBoundingClientRect();
  burst(r.left + r.width / 2, r.top + r.height / 2, 16);
  toast('Welcome to the Verve Club, darling', 'envelope-open-text');
});

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

// Falling petals
const petals = $('#petals');
for (let i = 0; i < 14; i++) {
  const p = document.createElement('i');
  const size = 7 + Math.random() * 9;
  p.className = 'petal';
  p.style.cssText = `left:${Math.random() * 100}%;width:${size}px;height:${size}px;--x:${Math.random() * 240 - 120}px;animation-duration:${9 + Math.random() * 9}s;animation-delay:${-Math.random() * 18}s`;
  petals.append(p);
}

// Hero parallax + cursor ring
if (matchMedia('(hover: hover) and (pointer: fine)').matches) {
  const stage = $('#stage'), layers = $$('[data-depth]', stage), ring = $('.cursor-ring');
  let mx = 0, my = 0, rx = 0, ry = 0;
  addEventListener('mousemove', e => {
    mx = e.clientX; my = e.clientY;
    ring.classList.add('on');
    ring.classList.toggle('big', !!e.target.closest('a, button, .card-media'));
    const x = mx / innerWidth - .5, y = my / innerHeight - .5;
    layers.forEach(l => { l.style.translate = `${-x * l.dataset.depth}px ${-y * l.dataset.depth}px`; });
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

// Scroll reveals
const io = new IntersectionObserver(entries => entries.forEach(en => {
  if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
}), { threshold: .12, rootMargin: '0px 0px -40px' });
$$('[data-reveal]').forEach(el => io.observe(el));

// Preloader
const start = performance.now();
function reveal() {
  setTimeout(() => {
    $('#preloader').classList.add('done');
    document.body.classList.add('loaded');
  }, Math.max(0, 1900 - (performance.now() - start)));
}
if (document.readyState === 'complete') reveal(); else addEventListener('load', reveal);

renderCart();
renderWish();
