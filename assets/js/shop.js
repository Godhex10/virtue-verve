/* ==========================================================
   Virtue & Verve — shop and category pages (filters, sorting)
   shop.html shows every bag; category.html?c=<id> shows one collection.
   ========================================================== */

const params = new URLSearchParams(location.search);
const grid = $('#grid');

// On category.html the list is narrowed to one collection before any filter applies
const onCategoryPage = document.body.dataset.page === 'category';
const COL = onCategoryPage ? COLLECTIONS.find(c => c.id === params.get('c')) : null;
if (onCategoryPage && !COL) location.replace('shop.html');
const BASE = COL ? PRODUCTS.filter(COL.match) : PRODUCTS;

const prices = BASE.map(p => p.price);
const MIN_PRICE = Math.min(...prices), MAX_PRICE = Math.max(...prices);
const TAGS = ['New', 'Bestseller'];
const SORTS = {
  featured: () => 0,
  newest: (a, b) => b.added.localeCompare(a.added),
  'price-asc': (a, b) => a.price - b.price,
  'price-desc': (a, b) => b.price - a.price,
  name: (a, b) => a.name.localeCompare(b.name)
};

// ---------- Filter state (kept in the address bar so views can be shared) ----------
const list = key => new Set((params.get(key) || '').split(',').filter(Boolean));
const state = {
  cats: new Set([...list('cat')].filter(c => CATEGORIES.some(k => k.cat === c))),
  colors: new Set([...list('colour')].filter(c => FAMILIES[c])),
  tags: new Set([...list('tag')].filter(t => TAGS.includes(t))),
  max: Math.min(MAX_PRICE, Math.max(MIN_PRICE, +params.get('max') || MAX_PRICE)),
  q: params.get('q') || '',
  sort: SORTS[params.get('sort')] ? params.get('sort') : 'featured'
};

function syncURL() {
  const q = new URLSearchParams();
  if (COL) q.set('c', COL.id);
  if (state.cats.size) q.set('cat', [...state.cats].join(','));
  if (state.colors.size) q.set('colour', [...state.colors].join(','));
  if (state.tags.size) q.set('tag', [...state.tags].join(','));
  if (state.max < MAX_PRICE) q.set('max', state.max);
  if (state.q) q.set('q', state.q);
  if (state.sort !== 'featured') q.set('sort', state.sort);
  const s = q.toString();
  try { history.replaceState(null, '', s ? `?${s}` : location.pathname); } catch { /* file:// in some browsers */ }
}

// ---------- Category page banner ----------
if (COL) {
  $('#shopTitle').innerHTML = `<em>${COL.label}</em>`;
  $('#shopIntro').textContent = COL.intro;
  $('#shopEyebrow').textContent = COL.note.toLowerCase();
  $('#crumbHere').textContent = COL.label;
  $('#bannerPhoto').innerHTML = `<img src="${COL.img}" alt="${COL.label}">`;
  $('#colPills').innerHTML = COLLECTIONS.map(c =>
    `<a class="pill${c === COL ? ' active' : ''}" href="category.html?c=${c.id}">${c.label}</a>`).join('');
  document.title = `${COL.label} — Virtue & Verve`;
}

// ---------- Sidebar (only offers options that exist in this list) ----------
const option = (attr, value, label, count, swatch = '') => `
  <button class="fopt" ${attr}="${value}" aria-pressed="false">
    <span class="fbox">${icon('check')}</span>${swatch}<span class="flabel">${label}</span><span class="fcount">${count}</span>
  </button>`;
const group = (id, html) => { $(id).innerHTML = html; $(id).closest('.fgroup').hidden = !html; };

group('#fCats', COL && COL.type === 'category' ? '' : CATEGORIES
  .map(c => [c, BASE.filter(p => p.cat === c.cat).length]).filter(([, n]) => n)
  .map(([c, n]) => option('data-cat', c.cat, c.label, n)).join(''));

group('#fColors', Object.entries(FAMILIES)
  .map(([name, hex]) => [name, hex, BASE.filter(p => p.colors.some(c => c[3] === name)).length]).filter(([, , n]) => n)
  .map(([name, hex, n]) => option('data-family', name, name, n, `<span class="fdot" style="--c:${hex}"></span>`)).join(''));

group('#fTags', TAGS
  .map(t => [t, BASE.filter(p => p.tag === t).length]).filter(([, n]) => n && n < BASE.length)
  .map(([t, n]) => option('data-tag', t, t === 'New' ? 'New in' : 'Bestsellers', n)).join(''));

const priceInput = $('#fPrice'), sideSearch = $('#shopSearch'), sortSelect = $('#sortBy');
priceInput.min = MIN_PRICE; priceInput.max = MAX_PRICE; priceInput.value = state.max;
priceInput.closest('.fgroup').hidden = MIN_PRICE === MAX_PRICE;
$('#fPriceMin').textContent = money(MIN_PRICE);
sideSearch.value = state.q;
sortSelect.value = state.sort;

// ---------- Filtering ----------
function matches(p) {
  if (state.cats.size && !state.cats.has(p.cat)) return false;
  if (state.colors.size && !p.colors.some(c => state.colors.has(c[3]))) return false;
  if (state.tags.size && !state.tags.has(p.tag)) return false;
  return p.price <= state.max;
}

const press = (btn, on) => { btn.classList.toggle('on', on); btn.setAttribute('aria-pressed', on); };

function render() {
  const pool = state.q ? searchProducts(state.q, BASE) : BASE;
  const found = pool.filter(matches).sort(SORTS[state.sort]);

  // Show each bag in the colour that matched the filter (or the collection's own colour)
  const families = state.colors.size ? state.colors : COL && COL.family ? new Set([COL.family]) : null;
  if (families) found.forEach(p => {
    const i = p.colors.findIndex(c => families.has(c[3]));
    if (i > -1 && !families.has(p.colors[chosen[p.id] || 0][3])) chosen[p.id] = i;
  });

  grid.innerHTML = found.map((p, i) => cardHTML(p, `style="animation-delay:${Math.min(i, 8) * .05}s"`)).join('');
  $$('.card', grid).forEach(c => c.classList.add('pop'));
  grid.hidden = !found.length;
  $('#emptyShop').hidden = !!found.length;

  const n = found.length, label = `${n} ${n === 1 ? 'bag' : 'bags'}`;
  $('#resultCount').innerHTML = `Showing <b>${label}</b>${n < BASE.length ? ` of ${BASE.length}` : ''}`;
  $('#sideCount').textContent = label;

  $$('[data-cat]', $('#fCats')).forEach(b => press(b, state.cats.has(b.dataset.cat)));
  $$('[data-family]').forEach(b => press(b, state.colors.has(b.dataset.family)));
  $$('[data-tag]', $('#fTags')).forEach(b => press(b, state.tags.has(b.dataset.tag)));
  $('#fPriceOut').textContent = state.max < MAX_PRICE ? `Up to ${money(state.max)}` : 'Any price';
  priceInput.style.setProperty('--fill', `${(state.max - MIN_PRICE) / (MAX_PRICE - MIN_PRICE || 1) * 100}%`);

  renderChips();
  syncURL();
}

// ---------- Active filter chips ----------
function renderChips() {
  const chips = [
    ...[...state.cats].map(c => ['cat', c, CATEGORIES.find(k => k.cat === c).label]),
    ...[...state.colors].map(c => ['family', c, c]),
    ...[...state.tags].map(t => ['tag', t, t === 'New' ? 'New in' : 'Bestsellers']),
    ...(state.max < MAX_PRICE ? [['max', '', `Up to ${money(state.max)}`]] : []),
    ...(state.q ? [['q', '', `“${esc(state.q)}”`]] : [])
  ];
  $('#chips').innerHTML = chips.map(([type, value, label]) =>
    `<button class="chip-x" data-type="${type}" data-value="${value}">${label}${icon('xmark')}</button>`).join('')
    + (chips.length > 1 ? '<button class="chip-clear" data-clear>Clear all</button>' : '');
  $('#filterBadge').textContent = chips.length;
  $('#filterBadge').hidden = !chips.length;
}

function clearAll() {
  state.cats.clear(); state.colors.clear(); state.tags.clear();
  state.max = MAX_PRICE; state.q = '';
  priceInput.value = MAX_PRICE; sideSearch.value = '';
  render();
}

const toggle = (set, value) => { set.has(value) ? set.delete(value) : set.add(value); render(); };

// ---------- Events ----------
$('#shopSide').addEventListener('click', e => {
  const b = e.target.closest('.fopt');
  if (!b) return;
  if (b.dataset.cat) toggle(state.cats, b.dataset.cat);
  else if (b.dataset.family) toggle(state.colors, b.dataset.family);
  else if (b.dataset.tag) toggle(state.tags, b.dataset.tag);
});
priceInput.addEventListener('input', () => { state.max = +priceInput.value; render(); });
sideSearch.addEventListener('input', () => { state.q = sideSearch.value.trim(); render(); });
sortSelect.addEventListener('change', () => { state.sort = sortSelect.value; render(); });

$('#chips').addEventListener('click', e => {
  if (e.target.closest('[data-clear]')) return clearAll();
  const chip = e.target.closest('.chip-x');
  if (!chip) return;
  const { type, value } = chip.dataset;
  if (type === 'cat') state.cats.delete(value);
  else if (type === 'family') state.colors.delete(value);
  else if (type === 'tag') state.tags.delete(value);
  else if (type === 'max') { state.max = MAX_PRICE; priceInput.value = MAX_PRICE; }
  else if (type === 'q') { state.q = ''; sideSearch.value = ''; }
  render();
});
$('#emptyClear').addEventListener('click', clearAll);

// Filters slide in from the side on phones
$('#filterToggle').addEventListener('click', () => {
  closeAll();
  $('#shopSide').classList.add('open');
  showOverlay();
});

bindGrid(grid);
render();
