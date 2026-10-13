/* ==========================================================
   Virtue & Verve — home page
   ========================================================== */

// ---------- Categories ----------
$('#cats').innerHTML = CATEGORIES.map((c, i) => `
  <a href="category.html?c=${c.cat}" class="cat" data-reveal style="--d:${i * .1}s">
    <div class="cat-arch"><img src="${c.img}" alt="${c.label}" loading="lazy"></div>
    <h3>${c.label}</h3><small>${c.note}</small>
  </a>`).join('');

// ---------- Featured bags ----------
const grid = $('#grid');
grid.innerHTML = PRODUCTS.slice(0, 8).map((p, i) => cardHTML(p, `data-reveal style="--d:${(i % 4) * .08}s"`)).join('');
bindGrid(grid);
observeReveals();

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

// ---------- Hero parallax ----------
if (matchMedia('(hover: hover) and (pointer: fine)').matches) {
  const layers = $$('[data-depth]', $('#stage'));
  addEventListener('mousemove', e => {
    const x = e.clientX / innerWidth - .5, y = e.clientY / innerHeight - .5;
    layers.forEach(l => { l.style.translate = `${-x * l.dataset.depth}px ${-y * l.dataset.depth}px`; });
  });
}
