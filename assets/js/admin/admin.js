/* ==========================================================
   Virtue & Verve — admin, shared by every admin page
   Sidebar and top bar, the data layer, and small helpers.
   Needs data.js loaded first (it supplies the starting catalogue).

   DATA: for now everything is kept in this browser (localStorage)
   so the admin can be tried without a database. Every page talks to
   the `api` object below and nothing else, so connecting Supabase
   later means rewriting `api` only. Row shapes match supabase/schema.sql.
   ========================================================== */

const $ = (s, el = document) => el.querySelector(s);
const $$ = (s, el = document) => [...el.querySelectorAll(s)];
const PAGE = document.body.dataset.page;
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const icon = (name, style = 'solid') => `<i class="fa-${style} fa-${name}"></i>`;
const uid = () => crypto.randomUUID ? crypto.randomUUID() : 'id-' + Date.now() + '-' + Math.random().toString(16).slice(2);
const slug = s => String(s).toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
const clone = v => JSON.parse(JSON.stringify(v));
// Photos saved by the shop are site-relative; uploads are stored inline
const asset = url => !url ? '' : /^(data:|blob:|https?:)/.test(url) ? url : '../' + url;
const DAY = 864e5;

// ---------- Sign-in (preview only: real login arrives with Supabase) ----------
const session = {
  get email() { try { return sessionStorage.getItem('vv_admin'); } catch { return null; } },
  start(email) { try { sessionStorage.setItem('vv_admin', email); } catch { /* storage unavailable */ } },
  end() { try { sessionStorage.removeItem('vv_admin'); } catch { /* storage unavailable */ } }
};
if (!LIVE && PAGE !== 'login' && !session.email) location.replace('login.html');

// ---------- Starting data ----------
const STATUSES = [
  { id: 'new', label: 'New', icon: 'bell', next: 'Mark as paid' },
  { id: 'paid', label: 'Paid', icon: 'credit-card', next: 'Mark as packed' },
  { id: 'packed', label: 'Packed', icon: 'box', next: 'Mark as sent' },
  { id: 'sent', label: 'Sent', icon: 'truck-fast', next: 'Mark as delivered' },
  { id: 'delivered', label: 'Delivered', icon: 'circle-check' }
];
const statusOf = id => STATUSES.find(s => s.id === id) || { id: 'cancelled', label: 'Cancelled', icon: 'ban' };
const LOW_STOCK = 3;

function seed() {
  // A small repeatable random generator so the sample orders look the same on every reset
  let s = 20261013;
  const rand = () => { s = (s * 1664525 + 1013904223) % 4294967296; return s / 4294967296; };
  const pick = list => list[Math.floor(rand() * list.length)];
  const rules = { 'new-in': { tag: 'New' }, bestsellers: { tag: 'Bestseller' }, 'emerald-edit': { family: 'Green' } };
  const now = Date.now();

  const categories = COLLECTIONS.map((c, i) => {
    const cat = CATEGORIES.find(k => k.cat === c.id);
    return { id: c.id, kind: c.type, label: c.label, singular: cat ? cat.one : '', note: c.note, intro: c.intro,
      image_url: c.img, rule: rules[c.id] || null, sort: i + 1, visible: true };
  });

  const products = PRODUCTS.map((p, i) => {
    const [material, size, carry] = SPECS[p.id] || [];
    return { id: p.id, name: p.name, kind: p.kind, category_id: p.cat, price: p.price, tag: p.tag || '', description: p.desc,
      material: material || '', size: size || '', carry: carry || '', active: true, sort: i + 1, created_at: p.added,
      colours: p.colors.map(([name, hex, image_url, family, extra = []]) => ({ id: uid(), name, hex, family, images: [image_url, ...extra], image_url, stock: 2 + Math.floor(rand() * 14) })) };
  });
  products[3].colours[0].stock = 2;  // a couple of low-stock examples for the dashboard
  products[9].colours[0].stock = 0;

  const people = [['Adaeze', 'Okafor'], ['Tolu', 'Bankole'], ['Maya', 'Williams'], ['Zainab', 'Bello'], ['Chioma', 'Eze'],
    ['Grace', 'Mensah'], ['Ife', 'Adeyemi'], ['Sophie', 'Clarke'], ['Amaka', 'Nwosu'], ['Halima', 'Yusuf']];
  const towns = ['Lekki', 'Ikeja', 'Victoria Island', 'Abuja', 'Port Harcourt', 'Ibadan'];
  const orders = [];
  for (let day = 13; day >= 0; day--) {
    const count = day === 0 ? 2 : Math.floor(rand() * 3) + (day % 6 === 0 ? 2 : 0);
    for (let n = 0; n < count; n++) {
      const [first, last] = pick(people), ship = pick(DELIVERY), zone = ZONES.find(z => z.id === ship.zone);
      const region = pick(zone.regions);
      const items = Array.from({ length: rand() < .3 ? 2 : 1 }, () => {
        const p = pick(products), c = pick(p.colours);
        return { product_id: p.id, colour_id: c.id, name: p.name, kind: p.kind, colour: c.name, image_url: c.image_url, price: p.price, qty: rand() < .15 ? 2 : 1 };
      });
      const subtotal = items.reduce((t, i) => t + i.price * i.qty, 0);
      const status = day === 0 ? 'new' : day === 1 ? pick(['new', 'paid']) : day <= 3 ? pick(['paid', 'packed']) : day <= 6 ? 'sent' : 'delivered';
      orders.push({
        id: uid(), number: 'VV-' + Math.floor(100000 + rand() * 900000), status: orders.length === 4 ? 'cancelled' : status,
        first_name: first, last_name: last, email: `${first.toLowerCase()}.${last.toLowerCase()}@example.com`, phone: '+000 000 000 0000',
        address: [`${Math.floor(1 + rand() * 60)} Sample Street`, zone.id === 'lagos' ? pick(towns) : region, zone.id === 'international' ? 'United Kingdom' : 'Nigeria'],
        delivery_label: ship.label, delivery_note: ship.note, delivery_price: ship.price, delivery_zone: zone.label, delivery_region: region,
        payment_method: pick(PAYMENTS).label, subtotal, total: subtotal + ship.price,
        gift_note: rand() < .25 ? 'Happy birthday! With all my love.' : '', admin_note: '',
        created_at: new Date(now - day * DAY - Math.floor(rand() * 9 * 36e5)).toISOString(), sample: true, items
      });
    }
  }

  const messages = [
    ['Zainab Bello', 'A question about a bag', '', 'Hello! Does the Seraphine come with a longer strap? I’d love to wear it crossbody.', 0.2, false],
    ['Grace Mensah', 'An exchange', orders[2].number, 'I ordered the Mimi in Vanilla but I think Emerald would suit the outfit better. Can I swap it?', 1.3, false],
    ['Sophie Clarke', 'Gifting or bulk orders', '', 'I’m looking for eight clutches as bridesmaid gifts for March. Do you offer anything for larger orders?', 3.1, true]
  ].map(([name, topic, order_number, message, days, is_read]) => ({
    id: uid(), name, email: name.toLowerCase().replace(' ', '.') + '@example.com', topic, order_number, message, is_read,
    created_at: new Date(now - days * DAY).toISOString(), sample: true
  }));

  const subscribers = people.slice(0, 6).map(([first, last], i) => ({
    email: `${first.toLowerCase()}.${last.toLowerCase()}@example.com`, created_at: new Date(now - (i * 2 + 1) * DAY).toISOString(), sample: true
  }));

  return {
    version: DATA_VERSION, categories, products, orders, messages, subscribers,
    delivery_zones: ZONES.map((z, i) => ({ ...z, regions: [...z.regions], sort: i + 1 })),
    delivery_options: DELIVERY.map((d, i) => ({ ...d, sort: i + 1, active: true })),
    settings: {
      currency: CURRENCY, email: STORE.email, whatsapp: STORE.whatsapp, instagram: STORE.instagram, hours: STORE.hours,
      return_days: STORE.returnDays, preview: STORE.preview, announcement: [...ANNOUNCE]
    }
  };
}

// ---------- Preview mode: everything kept in this browser ----------
const DB_KEY = 'vv_db';
function readDB() { try { return JSON.parse(localStorage.getItem(DB_KEY)); } catch { return null; } }
function writeDB() {
  try { localStorage.setItem(DB_KEY, JSON.stringify(db)); return true; }
  catch { toast('This browser’s storage is full — try smaller photos', 'triangle-exclamation'); return false; }
}
let db;
if (LIVE) {
  db = { settings: { currency: '₦' } }; // the real settings arrive with `ready` below
} else {
  db = readDB();
  if (!db || db.version !== DATA_VERSION) { db = seed(); writeDB(); }
  // Every colour has a list of photos; image_url is always the first (main) one
  db.products.forEach(p => p.colours.forEach(c => {
    if (!Array.isArray(c.images) || !c.images.length) c.images = c.image_url ? [c.image_url] : [];
    c.image_url = c.images[0] || '';
  }));
  collectInbox();
}

// Orders, messages and sign-ups made on the shop (in this browser) wait in an inbox; collect them
function collectInbox() {
  let box;
  try { box = JSON.parse(localStorage.getItem('vv_inbox')); } catch { box = null; }
  if (!box) return;
  (box.orders || []).forEach(o => {
    if (db.orders.some(x => x.number === o.number)) return;
    const items = o.items.map(i => {
      const p = db.products.find(x => x.id === i.id), c = p && p.colours[i.color];
      if (c) c.stock = Math.max(0, c.stock - i.qty);
      return { product_id: i.id, colour_id: c ? c.id : null, name: i.name, kind: i.kind, colour: i.colour, image_url: c ? c.image_url : '', price: i.price, qty: i.qty };
    });
    db.orders.push({
      id: uid(), number: o.number, status: 'new', first_name: o.customer.first, last_name: o.customer.last, email: o.customer.email,
      phone: o.customer.phone, address: o.address, delivery_label: o.delivery.label, delivery_note: o.delivery.note, delivery_price: o.delivery.price,
      delivery_zone: o.delivery.zone || '', delivery_region: o.region || '',
      payment_method: o.payment, subtotal: o.subtotal, total: o.total, gift_note: o.note || '', admin_note: '', created_at: o.placed, items
    });
  });
  (box.messages || []).forEach(m => db.messages.push({ id: uid(), name: m.name, email: m.email, topic: m.topic || '', order_number: m.order || '',
    message: m.message, is_read: false, created_at: m.created_at }));
  (box.subscribers || []).forEach(sub => { if (!db.subscribers.some(x => x.email === sub.email)) db.subscribers.push(sub); });
  try { localStorage.removeItem('vv_inbox'); } catch { /* storage unavailable */ }
  writeDB();
}

// ---------- Signed in? (database mode) ----------
// Every api call waits for this. Visitors who aren't signed-in admins are sent to the login page.
const never = new Promise(() => {});
async function isAdmin() {
  const { data: { user } } = await sb.auth.getUser();
  if (!user) return false;
  const { data } = await sb.from('admins').select('user_id').eq('user_id', user.id).maybeSingle();
  return !!data;
}
const ready = !LIVE ? Promise.resolve() : (async () => {
  const { data: { session: current } } = await sb.auth.getSession();
  if (PAGE === 'login') return; // the login page decides for itself
  if (!current) { location.replace('login.html'); return never; }
  if (!await isAdmin()) { await sb.auth.signOut(); location.replace('login.html'); return never; }
  const { data, error } = await sb.from('settings').select('*');
  if (error) toast(error.message, 'triangle-exclamation');
  db.settings = fromDb.settings(data);
  db.email = current.user.email;
})();

// ---------- The data layer every page uses ----------
const localApi = {
  async list(table) { return clone(db[table]); },
  async get(table, id, key = 'id') { const row = db[table].find(r => r[key] === id); return row ? clone(row) : null; },
  async save(table, row, key = 'id') {
    const i = db[table].findIndex(r => r[key] === row[key]);
    if (i > -1) db[table][i] = clone(row); else db[table].push(clone(row));
    if (!writeDB()) throw new Error('Could not save');
    return row;
  },
  async remove(table, id, key = 'id') { db[table] = db[table].filter(r => r[key] !== id); writeDB(); },
  async replace(table, rows) { db[table] = clone(rows); if (!writeDB()) throw new Error('Could not save'); },
  async settings() { return clone(db.settings); },
  async saveSettings(values) { Object.assign(db.settings, clone(values)); if (!writeDB()) throw new Error('Could not save'); },
  async reset() { try { localStorage.removeItem(DB_KEY); } catch { /* storage unavailable */ } }
};

// The same calls, answered by Supabase
const liveApi = (() => {
  const run = async query => {
    const { data, error } = await query;
    if (error) { toast(error.message, 'triangle-exclamation'); throw error; }
    return data;
  };
  const select = {
    products: () => sb.from('products').select('*, colours:product_colours(*)').order('sort'),
    orders: () => sb.from('orders').select('*, items:order_items(*)').order('created_at', { ascending: false }),
    categories: () => sb.from('categories').select('*').order('sort'),
    messages: () => sb.from('messages').select('*').order('created_at', { ascending: false }),
    subscribers: () => sb.from('subscribers').select('*').order('created_at', { ascending: false }),
    delivery_zones: () => sb.from('delivery_zones').select('*').order('sort'),
    delivery_options: () => sb.from('delivery_options').select('*').order('sort')
  };
  const shape = { products: fromDb.product, orders: fromDb.order, delivery_options: fromDb.option };
  const toRow = {
    products: p => ({ id: p.id, name: p.name, kind: p.kind, category_id: p.category_id, price: Number(p.price) || 0, tag: p.tag || null,
      description: p.description || null, material: p.material || null, size: p.size || null, carry: p.carry || null,
      active: !!p.active, sort: p.sort || 0, created_at: p.created_at || undefined }),
    categories: c => ({ id: c.id, kind: c.kind, label: c.label, singular: c.singular || null, note: c.note || null, intro: c.intro || null,
      image_url: c.image_url || null, rule: c.rule || null, sort: c.sort || 0, visible: !!c.visible }),
    delivery_zones: z => ({ id: z.id, label: z.label, regions: z.regions || [], sort: z.sort || 0 }),
    delivery_options: d => ({ id: d.id, zone_id: d.zone, label: d.label, note: d.note || null, price: Number(d.price) || 0, sort: d.sort || 0, active: d.active !== false }),
    orders: o => ({ status: o.status, admin_note: o.admin_note || null }),
    messages: m => ({ is_read: !!m.is_read })
  };
  // The shop keeps a copy of the catalogue in this browser; drop it after a change so the shop shows the new version
  const forget = () => { try { localStorage.removeItem('vv_catalogue'); } catch { /* storage unavailable */ } };
  const inList = ids => `(${ids.map(id => `"${String(id).replace(/"/g, '')}"`).join(',')})`;

  // New photos arrive as inline images; put them in the photo store and keep the web address
  async function upload(url, folder) {
    if (!url || !url.startsWith('data:')) return url;
    const blob = await (await fetch(url)).blob();
    const path = `${folder}/${uid()}.jpg`;
    await run(sb.storage.from('products').upload(path, blob, { contentType: 'image/jpeg' }));
    return sb.storage.from('products').getPublicUrl(path).data.publicUrl;
  }

  return {
    async list(table) { await ready; const rows = await run(select[table]()); return shape[table] ? rows.map(shape[table]) : rows; },
    async get(table, id, key = 'id') {
      await ready;
      const rows = await run(select[table]().eq(key, id));
      return rows[0] ? (shape[table] ? shape[table](rows[0]) : rows[0]) : null;
    },
    async save(table, row) {
      await ready;
      forget();
      if (table === 'products') {
        for (const c of row.colours) {
          c.images = await Promise.all(c.images.map(url => upload(url, row.id)));
          c.image_url = c.images[0] || '';
        }
        await run(sb.from('products').upsert(toRow.products(row)));
        let gone = sb.from('product_colours').delete().eq('product_id', row.id);
        if (row.colours.length) gone = gone.not('id', 'in', inList(row.colours.map(c => c.id)));
        await run(gone);
        if (row.colours.length) await run(sb.from('product_colours').upsert(row.colours.map((c, i) => ({
          id: c.id, product_id: row.id, name: c.name, hex: c.hex, family: c.family || null, images: c.images, stock: Number(c.stock) || 0, sort: i + 1
        }))));
        return row;
      }
      if (table === 'orders' || table === 'messages') {
        await run(sb.from(table).update(toRow[table](row)).eq('id', row.id));
        return row;
      }
      if (table === 'categories') row.image_url = await upload(row.image_url, 'categories');
      await run(sb.from(table).upsert(toRow[table] ? toRow[table](row) : row));
      return row;
    },
    async remove(table, id, key = 'id') { await ready; forget(); await run(sb.from(table).delete().eq(key, id)); },
    // Make the table hold exactly these rows (used for reordering and the delivery settings)
    async replace(table, rows) {
      await ready;
      forget();
      if (table === 'categories') for (const c of rows) c.image_url = await upload(c.image_url, 'categories');
      if (rows.length) await run(sb.from(table).upsert(rows.map(toRow[table])));
      const ids = rows.map(r => r.id);
      await run(ids.length ? sb.from(table).delete().not('id', 'in', inList(ids)) : sb.from(table).delete().neq('id', ''));
    },
    async settings() { await ready; return clone(db.settings); },
    async saveSettings(values) {
      await ready;
      Object.assign(db.settings, clone(values));
      forget();
      await run(sb.from('settings').upsert(toDbSettings(db.settings)));
    },
    async reset() {}
  };
})();

const api = LIVE ? liveApi : localApi;

// ---------- Formatting ----------
const money = n => db.settings.currency + Number(n || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
const short = (n, unit, letter) => db.settings.currency + (n / unit).toFixed(n / unit < 10 ? 1 : 0).replace(/\.0$/, '') + letter;
const compact = n => n >= 1e6 ? short(n, 1e6, 'M') : n >= 1e4 ? short(n, 1e3, 'K') : money(Math.round(n));
const fmtDate = (iso, withTime) => new Date(iso).toLocaleString(undefined, withTime
  ? { day: 'numeric', month: 'short', year: 'numeric', hour: 'numeric', minute: '2-digit' }
  : { day: 'numeric', month: 'short', year: 'numeric' });
function ago(iso) {
  const mins = Math.max(0, Math.round((Date.now() - new Date(iso)) / 6e4));
  if (mins < 60) return mins <= 1 ? 'Just now' : `${mins} min ago`;
  if (mins < 1440) return `${Math.round(mins / 60)} hr ago`;
  const days = Math.round(mins / 1440);
  return days === 1 ? 'Yesterday' : days < 7 ? `${days} days ago` : fmtDate(iso);
}
const statusBadge = id => { const s = statusOf(id); return `<span class="badge ${s.id}">${icon(s.icon)}${s.label}</span>`; };
const stockOf = p => p.colours.reduce((n, c) => n + Number(c.stock || 0), 0);
function stockBadge(n) {
  if (n <= 0) return `<span class="badge out">${icon('circle-xmark')}Out of stock</span>`;
  if (n <= LOW_STOCK) return `<span class="badge low">${icon('triangle-exclamation')}${n} left</span>`;
  return `<span class="badge ok">${icon('circle-check')}${n} in stock</span>`;
}
// Which bags belong to a category or an edit
const inCategory = (cat, p) => cat.kind === 'category' ? p.category_id === cat.id
  : cat.rule && cat.rule.tag ? p.tag === cat.rule.tag
  : cat.rule && cat.rule.family ? p.colours.some(c => c.family === cat.rule.family) : false;

// ---------- Page furniture ----------
const NAV = [
  ['dashboard', 'index.html', 'gauge-high', 'Dashboard'],
  ['orders', 'orders.html', 'receipt', 'Orders'],
  ['products', 'products.html', 'bag-shopping', 'Products'],
  ['categories', 'categories.html', 'layer-group', 'Categories'],
  ['customers', 'customers.html', 'users', 'Customers'],
  ['messages', 'messages.html', 'envelope', 'Messages'],
  ['settings', 'settings.html', 'gear', 'Settings']
];
const PARENT = { order: 'orders', product: 'products' }; // detail pages light up their section

const common = `
  <div class="veil" id="veil"></div>
  <aside class="sheet" id="sheet" aria-hidden="true">
    <div class="sheet-head"><h2 id="sheetTitle"></h2><button class="icon-btn" data-shut aria-label="Close">${icon('xmark')}</button></div>
    <form class="sheet-body" id="sheetBody" novalidate></form>
    <div class="sheet-foot"><button class="btn btn-ghost" data-shut>Cancel</button><button class="btn btn-primary" id="sheetSave">Save</button></div>
  </aside>
  <div class="dialog" id="dialog" role="alertdialog" aria-modal="true">
    <h2 id="dialogTitle"></h2><p id="dialogText"></p>
    <div class="form-foot"><button class="btn btn-ghost" id="dialogNo">Cancel</button><button class="btn btn-danger" id="dialogYes"></button></div>
  </div>
  <div class="toast" id="toast" role="status"></div>`;

if (PAGE === 'login') {
  document.body.insertAdjacentHTML('beforeend', common);
} else {
  const counts = LIVE ? {} : { orders: db.orders.filter(o => o.status === 'new').length, messages: db.messages.filter(m => !m.is_read).length };
  const section = PARENT[PAGE] || PAGE;
  document.body.innerHTML = `
  <div class="shell">
    <aside class="side" id="side">
      <a href="index.html" class="side-logo">Virtue <span>&amp;</span> Verve</a>
      <p class="side-tag">Admin</p>
      <nav aria-label="Admin">
        ${NAV.map(([key, href, ic, label]) => `<a href="${href}"${key === section ? ' class="active" aria-current="page"' : ''}>${icon(ic)}${label}<span class="count" id="count-${key}"${counts[key] ? '' : ' hidden'}>${counts[key] || 0}</span></a>`).join('')}
      </nav>
      <div class="side-foot">
        <a href="../index.html" target="_blank">${icon('arrow-up-right-from-square')}View shop</a>
        <button id="signOut">${icon('right-from-bracket')}Sign out</button>
      </div>
    </aside>
    <div class="scrim" id="scrim"></div>
    <div class="main">
      <header class="top">
        <button class="burger" id="burger" aria-label="Open menu">${icon('bars')}</button>
        <div><h1 id="pageTitle"></h1><small id="pageSub"></small></div>
        <div class="top-actions" id="pageActions"></div>
        <span class="avatar" id="avatar" title="${esc(session.email || '')}">${esc((session.email || 'A')[0].toUpperCase())}</span>
      </header>
      <main class="content" id="app"></main>
    </div>
  </div>${common}`;

  const toggleSide = open => { $('#side').classList.toggle('open', open); $('#scrim').classList.toggle('open', open); };
  $('#burger').addEventListener('click', () => toggleSide(true));
  $('#scrim').addEventListener('click', () => toggleSide(false));
  $('#signOut').addEventListener('click', async () => {
    if (LIVE) await sb.auth.signOut(); else session.end();
    location.href = 'login.html';
  });

  // Database mode: show who is signed in and the counts of new orders and unread messages
  if (LIVE) ready.then(async () => {
    $('#avatar').textContent = (db.email || 'A')[0].toUpperCase();
    $('#avatar').title = db.email || '';
    const count = async (table, column, value) => (await sb.from(table).select('id', { count: 'exact', head: true }).eq(column, value)).count || 0;
    const [orders, messages] = await Promise.all([count('orders', 'status', 'new'), count('messages', 'is_read', false)]);
    [['orders', orders], ['messages', messages]].forEach(([key, n]) => { const el = $(`#count-${key}`); el.textContent = n; el.hidden = !n; });
  });
}

const app = $('#app');
function setHead(title, sub = '', actions = '') {
  $('#pageTitle').textContent = title;
  $('#pageSub').textContent = sub;
  $('#pageActions').innerHTML = actions;
  document.title = `${title} — Virtue & Verve admin`;
}
const emptyState = (ic, title, text) => `<div class="empty">${icon(ic)}<h3>${title}</h3><p>${text}</p></div>`;

// ---------- Toast, confirm box, side panel ----------
let toastTimer;
function toast(msg, ic = 'circle-check') {
  const el = $('#toast');
  if (!el) return;
  el.innerHTML = icon(ic) + esc(msg);
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 2800);
}
// Show a message on the next page (used after save-and-redirect)
function flash(msg) { try { sessionStorage.setItem('vv_flash', msg); } catch { /* storage unavailable */ } }
try {
  const waiting = sessionStorage.getItem('vv_flash');
  if (waiting) { sessionStorage.removeItem('vv_flash'); setTimeout(() => toast(waiting), 300); }
} catch { /* storage unavailable */ }

const veil = $('#veil'), sheet = $('#sheet'), dialog = $('#dialog');
function confirmBox(title, text, yes = 'Delete') {
  return new Promise(resolve => {
    $('#dialogTitle').textContent = title;
    $('#dialogText').textContent = text;
    $('#dialogYes').textContent = yes;
    const done = answer => {
      dialog.classList.remove('open');
      if (!sheet.classList.contains('open')) veil.classList.remove('open');
      resolve(answer);
    };
    $('#dialogYes').onclick = () => done(true);
    $('#dialogNo').onclick = () => done(false);
    veil.classList.add('open');
    dialog.classList.add('open');
  });
}
// A form that slides in from the right. onSave gets the form's values and returns false to stay open.
function openSheet(title, html, onSave) {
  $('#sheetTitle').textContent = title;
  $('#sheetBody').innerHTML = html;
  $('#sheetSave').onclick = async () => {
    const form = $('#sheetBody');
    if (!form.reportValidity()) return;
    if (await onSave(Object.fromEntries(new FormData(form)), form) !== false) closeSheet();
  };
  veil.classList.add('open');
  sheet.classList.add('open');
  sheet.setAttribute('aria-hidden', 'false');
}
function closeSheet() {
  sheet.classList.remove('open');
  sheet.setAttribute('aria-hidden', 'true');
  if (!dialog.classList.contains('open')) veil.classList.remove('open');
}
document.addEventListener('click', e => { if (e.target.closest('[data-shut]')) { e.preventDefault(); closeSheet(); } });
if (veil) veil.addEventListener('click', () => { if (!dialog.classList.contains('open')) closeSheet(); });
addEventListener('keydown', e => { if (e.key === 'Escape' && !dialog.classList.contains('open')) closeSheet(); });

// Whole table rows that open a page
document.addEventListener('click', e => {
  const row = e.target.closest('tr[data-href]');
  if (row && !e.target.closest('a, button, input, label')) location.href = row.dataset.href;
});

// ---------- Photos ----------
// Shrinks a chosen photo so it is quick to load (and fits in browser storage for now)
function readPhoto(file, max = 1000) {
  return new Promise((resolve, reject) => {
    if (!file || !file.type.startsWith('image/')) return reject(new Error('Please choose an image file'));
    const img = new Image();
    img.onload = () => {
      const scale = Math.min(1, max / Math.max(img.width, img.height));
      const canvas = document.createElement('canvas');
      canvas.width = Math.round(img.width * scale);
      canvas.height = Math.round(img.height * scale);
      canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
      URL.revokeObjectURL(img.src);
      resolve(canvas.toDataURL('image/jpeg', .82));
    };
    img.onerror = () => reject(new Error('That photo could not be read'));
    img.src = URL.createObjectURL(file);
  });
}
const dropHTML = (url, attrs = '') => `
  <label class="drop${url ? ' has' : ''}">
    ${url ? `<img src="${esc(asset(url))}" alt="">` : `<span>${icon('image')}Add photo</span>`}
    <input type="file" accept="image/*" ${attrs}>
  </label>`;

// ---------- Login page ----------
if (PAGE === 'login') {
  if (LIVE) {
    $('.login-box .preview-note').remove(); // real sign-in from here on
    isAdmin().then(yes => { if (yes) location.replace('index.html'); });
  } else if (session.email) location.replace('index.html');

  $('#loginForm').addEventListener('submit', async e => {
    e.preventDefault();
    const f = e.target.elements, button = $('button[type="submit"]', e.target);
    if (!LIVE) { session.start(f.email.value.trim()); location.href = 'index.html'; return; }
    button.disabled = true;
    const { error } = await sb.auth.signInWithPassword({ email: f.email.value.trim(), password: f.password.value });
    if (error) {
      button.disabled = false;
      return toast(/invalid login/i.test(error.message) ? 'That email and password don’t match' : error.message, 'triangle-exclamation');
    }
    if (!await isAdmin()) {
      await sb.auth.signOut();
      button.disabled = false;
      return toast('This account doesn’t have admin access', 'triangle-exclamation');
    }
    location.href = 'index.html';
  });
}
