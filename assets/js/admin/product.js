/* ==========================================================
   Virtue & Verve — admin add / edit a bag
   product.html         -> a new bag
   product.html?id=<id> -> edit that bag
   ========================================================== */
(async () => {
  const id = new URLSearchParams(location.search).get('id');
  const all = await api.list('products');
  const categories = (await api.list('categories')).filter(c => c.kind === 'category').sort((a, b) => a.sort - b.sort);
  const families = Object.keys(FAMILIES);
  const blankColour = () => ({ id: uid(), name: '', hex: '#088178', family: families[0], image_url: '', stock: 0 });

  const p = id ? await api.get('products', id) : {
    id: '', name: '', kind: '', category_id: categories[0] ? categories[0].id : '', price: '', tag: '', description: '',
    material: '', size: '', carry: '', active: true, sort: all.length + 1, created_at: new Date().toISOString().slice(0, 10), colours: [blankColour()]
  };
  if (!p) {
    setHead('Bag not found');
    app.innerHTML = `<div class="card">${emptyState('bag-shopping', 'We can’t find that bag', 'It may have been deleted.')}<p style="text-align:center"><a class="btn btn-primary" href="products.html">Back to products</a></p></div>`;
    return;
  }

  setHead(id ? p.name : 'Add a bag', id ? 'Edit this bag' : 'A new bag for the shop',
    `<a class="btn btn-ghost" href="products.html">${icon('arrow-left')}<span>Products</span></a>`);

  const options = (list, value) => list.map(([v, label]) => `<option value="${esc(v)}"${v === value ? ' selected' : ''}>${esc(label)}</option>`).join('');

  app.innerHTML = `
    <form class="editor" id="form" novalidate>
      <div class="stack">
        <div class="card">
          <div class="card-head"><h2>The basics</h2></div>
          <div class="fields">
            <label class="field">Name<input name="name" value="${esc(p.name)}" placeholder="Seraphine" required></label>
            <label class="field">Type<input name="kind" value="${esc(p.kind)}" placeholder="Top-Handle" required></label>
            <label class="field">Category<select name="category_id" required>${options(categories.map(c => [c.id, c.label]), p.category_id)}</select></label>
            <label class="field">Price (${esc(db.settings.currency)})<input name="price" type="number" min="0" step="0.01" value="${esc(p.price)}" required></label>
            <label class="field wide">Description<textarea name="description" rows="3" placeholder="A sentence or two that makes her want it.">${esc(p.description)}</textarea></label>
          </div>
        </div>

        <div class="card">
          <div class="card-head"><div><h2>Colours, photos and stock</h2><small>Each colour has its own photo and its own stock count</small></div>
            <button type="button" class="btn btn-ghost btn-sm" id="addColour">${icon('plus')}Add colour</button></div>
          <div id="colours"></div>
        </div>

        <div class="card">
          <div class="card-head"><div><h2>Details</h2><small>Shown on the bag’s page</small></div></div>
          <div class="fields">
            <label class="field wide">Material<input name="material" value="${esc(p.material)}" placeholder="Pebbled leather, gold-tone hardware"></label>
            <label class="field">Size<input name="size" value="${esc(p.size)}" placeholder="26 × 19 × 10 cm"></label>
            <label class="field">How it’s carried<input name="carry" value="${esc(p.carry)}" placeholder="Top handle, detachable strap"></label>
          </div>
        </div>

        <div class="form-foot">
          ${id ? `<button type="button" class="btn btn-danger" id="delete">${icon('trash-can', 'regular')}Delete bag</button>` : ''}
          <span class="spacer"></span>
          <a class="btn btn-ghost" href="products.html">Cancel</a>
          <button class="btn btn-primary" type="submit">${icon('check')}${id ? 'Save changes' : 'Add bag'}</button>
        </div>
      </div>

      <aside class="preview stack">
        <div class="card">
          <p class="eyebrow">Visibility</p>
          <label class="switch"><input type="checkbox" name="active" ${p.active ? 'checked' : ''}><span></span>Show on the shop</label>
          <label class="field" style="margin-top:16px">Label<select name="tag">${options([['', 'None'], ['New', 'New'], ['Bestseller', 'Bestseller']], p.tag)}</select></label>
        </div>
        <div>
          <p class="eyebrow">How it looks on the shop</p>
          <div class="preview-card" id="preview"></div>
        </div>
      </aside>
    </form>`;

  const form = $('#form'), coloursBox = $('#colours');

  function renderColours() {
    coloursBox.innerHTML = p.colours.map((c, i) => `
      <div class="colour-row" data-i="${i}">
        ${dropHTML(c.image_url, 'data-photo')}
        <div class="colour-fields">
          <label class="field">Colour name<input data-f="name" value="${esc(c.name)}" placeholder="Verve Teal"></label>
          <label class="field">Swatch<input data-f="hex" type="color" value="${esc(c.hex)}"></label>
          <label class="field">Colour group<select data-f="family">${options(families.map(f => [f, f]), c.family)}</select></label>
          <label class="field">In stock<input data-f="stock" type="number" min="0" step="1" value="${esc(c.stock)}"></label>
          <button type="button" class="icon-btn danger" data-remove title="Remove colour" aria-label="Remove colour" ${p.colours.length < 2 ? 'disabled' : ''}>${icon('trash-can', 'regular')}</button>
        </div>
      </div>`).join('');
  }

  function renderPreview() {
    const f = form.elements, first = p.colours[0] || {};
    $('#preview').innerHTML = `
      <div class="preview-media">
        ${f.tag.value ? `<span class="preview-tag${f.tag.value === 'New' ? ' new' : ''}">${esc(f.tag.value)}</span>` : ''}
        ${first.image_url ? `<img src="${esc(asset(first.image_url))}" alt="">` : 'Add a photo'}
      </div>
      <div class="preview-body">
        <div class="preview-row"><h3>${esc(f.name.value || 'Bag name')}</h3><b>${money(f.price.value || 0)}</b></div>
        <small>${esc(f.kind.value || 'Type')} · ${esc(first.name || 'Colour')}</small>
        <div class="preview-dots">${p.colours.length > 1 ? p.colours.map(c => `<span class="dot" style="--c:${esc(c.hex)}"></span>`).join('') : ''}</div>
      </div>`;
  }

  // Typing in a colour row updates that colour straight away
  coloursBox.addEventListener('input', e => {
    const row = e.target.closest('.colour-row'), field = e.target.dataset.f;
    if (!row || !field) return;
    p.colours[+row.dataset.i][field] = field === 'stock' ? Math.max(0, parseInt(e.target.value, 10) || 0) : e.target.value;
    renderPreview();
  });
  coloursBox.addEventListener('change', async e => {
    if (!e.target.matches('[data-photo]')) return;
    const c = p.colours[+e.target.closest('.colour-row').dataset.i];
    try {
      c.image_url = await readPhoto(e.target.files[0]);
      renderColours();
      renderPreview();
    } catch (err) { toast(err.message, 'triangle-exclamation'); }
  });
  coloursBox.addEventListener('click', e => {
    if (!e.target.closest('[data-remove]')) return;
    p.colours.splice(+e.target.closest('.colour-row').dataset.i, 1);
    renderColours();
    renderPreview();
  });
  $('#addColour').addEventListener('click', () => { p.colours.push(blankColour()); renderColours(); renderPreview(); });
  form.addEventListener('input', renderPreview);

  form.addEventListener('submit', async e => {
    e.preventDefault();
    if (!form.reportValidity()) return;
    const missing = p.colours.find(c => !c.name.trim() || !c.image_url);
    if (missing) return toast('Each colour needs a name and a photo', 'triangle-exclamation');

    const f = form.elements;
    Object.assign(p, {
      name: f.name.value.trim(), kind: f.kind.value.trim(), category_id: f.category_id.value, price: Number(f.price.value),
      tag: f.tag.value, description: f.description.value.trim(), material: f.material.value.trim(), size: f.size.value.trim(),
      carry: f.carry.value.trim(), active: f.active.checked
    });
    if (!id) { // a new bag gets a web address made from its name
      let fresh = slug(p.name) || 'bag', n = 2;
      while (all.some(x => x.id === fresh)) fresh = `${slug(p.name) || 'bag'}-${n++}`;
      p.id = fresh;
    }
    try {
      await api.save('products', p);
      flash(id ? `${p.name} saved` : `${p.name} added to the shop`);
      location.href = 'products.html';
    } catch { /* writeDB already explained the problem */ }
  });

  app.addEventListener('click', async e => {
    if (!e.target.closest('#delete')) return;
    if (!await confirmBox(`Delete ${p.name}?`, 'It will disappear from the shop. Past orders keep their record of it.')) return;
    await api.remove('products', p.id);
    flash(`${p.name} deleted`);
    location.href = 'products.html';
  });

  renderColours();
  renderPreview();
})();
