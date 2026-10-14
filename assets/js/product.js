/* ==========================================================
   Virtue & Verve — product page (product.html?id=<bag id>)
   ========================================================== */

const query = new URLSearchParams(location.search);
const bag = product(query.get('id'));
const pdp = $('#pdp');

if (!bag) {
  $('#crumbs').hidden = true;
  $('#relatedWrap').hidden = true;
  pdp.className = 'empty-shop';
  pdp.innerHTML = `<div class="empty-icon">${icon('magnifying-glass')}</div><h3>We can’t find that bag</h3>
    <p>It may have sold out or moved. The rest of the collection is waiting.</p>
    <a class="btn btn-primary" href="shop.html"><span>Shop all bags</span></a>`;
} else {
  const cat = CATEGORIES.find(c => c.cat === bag.cat);
  const [material, size, carry] = SPECS[bag.id] || [];
  let colour = bag.colors[+query.get('colour')] ? +query.get('colour') : 0, qty = 1, shot = 0; // shot = which photo of this colour

  document.title = `${bag.name} ${bag.kind} — Virtue & Verve`;
  $('#crumbs').innerHTML = `<a href="index.html">Home</a>${icon('chevron-right')}<a href="category.html?c=${cat.cat}">${cat.label}</a>${icon('chevron-right')}<span>${bag.name}</span>`;

  const accordion = (title, body, open = false) => `
    <div class="acc${open ? ' open' : ''}">
      <button class="acc-head" aria-expanded="${open}">${title}${icon('plus')}</button>
      <div class="acc-body"><div>${body}</div></div>
    </div>`;

  pdp.innerHTML = `
    <div class="gallery">
      <div class="gallery-main" id="galleryMain">
        ${bag.tag ? `<span class="tag${bag.tag === 'New' ? ' new' : ''}">${bag.tag}</span>` : ''}
        <img id="mainPhoto" src="" alt="">
      </div>
      <div class="thumbs" id="thumbs"></div>
    </div>

    <div class="pdp-info">
      <p class="script">${bag.kind}</p>
      <h1>${bag.name}</h1>
      <p class="pdp-price">${money(bag.price)}</p>
      <p class="pdp-desc">${bag.desc}</p>

      <div class="label">Colour — <b id="colourName"></b></div>
      <div id="colourPick">${swatchesHTML(bag, colour)}</div>

      <div class="label">Quantity</div>
      <div class="pdp-buy">
        <div id="qtyBox">${qtyHTML(qty)}</div>
        <button class="btn btn-primary" id="addBtn">${icon('bag-shopping')}<span id="addLabel"></span></button>
        <button class="wish pdp-wish" id="wishToggle" aria-label="Save to wishlist">${icon('heart', 'regular')}</button>
      </div>

      <ul class="perk-list">
        <li>${icon('gift')}Gift-wrapped with a bow, always</li>
        <li>${icon('truck-fast')}Delivery across Nigeria and beyond</li>
        <li>${icon('rotate-left')}Exchanges within ${STORE.returnDays} days</li>
      </ul>

      ${accordion('Details', `<dl class="specs">
        <dt>Material</dt><dd>${material || '—'}</dd>
        <dt>Size</dt><dd>${size || '—'}</dd>
        <dt>Carry</dt><dd>${carry || '—'}</dd>
        <dt>Style</dt><dd>${cat.label}</dd></dl>`, true)}
      ${accordion('Delivery & returns', `<p>Orders are packed within one working day. Choose standard, express or collection at checkout.</p>
        <p>Not quite right? Exchange it within ${STORE.returnDays} days, unused and in its original packaging.
        <a class="text-link" href="delivery.html">Read the full policy ${icon('arrow-right')}</a></p>`)}
      ${accordion('Care', `<p>Keep your bag in its dust bag when you’re not carrying it. Wipe gently with a soft, dry cloth, and keep it away from rain, direct heat and perfume.</p>`)}
    </div>`;

  const mainPhoto = $('#mainPhoto'), main = $('#galleryMain'), wishToggle = $('#wishToggle');

  const paint = () => {
    const [name] = bag.colors[colour], shots = photosOf(bag, colour);
    if (shot >= shots.length) shot = 0;
    if (mainPhoto.getAttribute('src') !== shots[shot]) {
      mainPhoto.src = shots[shot];
      mainPhoto.classList.remove('swap'); void mainPhoto.offsetWidth; mainPhoto.classList.add('swap');
    }
    $('#thumbs').innerHTML = shots.length > 1 ? shots.map((src, i) =>
      `<button class="thumb${i === shot ? ' active' : ''}" data-shot="${i}" aria-label="Photo ${i + 1} of ${shots.length}"><img src="${src}" alt=""></button>`).join('') : '';
    mainPhoto.alt = `${bag.name} ${bag.kind} in ${name}`;
    $('#colourName').textContent = name;
    $('#colourPick').innerHTML = swatchesHTML(bag, colour);
    $('#qtyBox').innerHTML = qtyHTML(qty);
    $('#addLabel').textContent = `Add to bag — ${money(bag.price * qty)}`;
  };

  pdp.addEventListener('click', e => {
    const pick = e.target.closest('.swatch'), thumb = e.target.closest('.thumb'), step = e.target.closest('#qtyBox [data-step]');
    if (pick) { colour = +pick.dataset.color; shot = 0; paint(); }
    else if (thumb) { shot = +thumb.dataset.shot; paint(); }
    else if (step) { qty = Math.max(1, qty + +step.dataset.step); paint(); }
    else if (e.target.closest('#addBtn')) addToCart(bag.id, colour, qty, mainPhoto);
    else if (e.target.closest('#wishToggle')) toggleWish(bag, wishToggle);
  });

  if (wishlist.includes(bag.id)) { wishToggle.classList.add('on'); wishToggle.innerHTML = icon('heart'); }

  // Hover to zoom into the photo
  if (matchMedia('(hover: hover) and (pointer: fine)').matches) {
    main.addEventListener('mousemove', e => {
      const r = main.getBoundingClientRect();
      mainPhoto.style.transformOrigin = `${(e.clientX - r.left) / r.width * 100}% ${(e.clientY - r.top) / r.height * 100}%`;
      main.classList.add('zoom');
    });
    main.addEventListener('mouseleave', () => main.classList.remove('zoom'));
  }

  paint();

  // You may also like: same shape first, then the rest
  const related = [...PRODUCTS.filter(p => p.cat === bag.cat), ...PRODUCTS.filter(p => p.cat !== bag.cat)]
    .filter(p => p !== bag).slice(0, 4);
  const relatedGrid = $('#related');
  relatedGrid.innerHTML = related.map((p, i) => cardHTML(p, `data-reveal style="--d:${i * .08}s"`)).join('');
  bindGrid(relatedGrid);
  observeReveals();
}
