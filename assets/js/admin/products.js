/* ==========================================================
   Virtue & Verve — admin products (grid of bags)
   ========================================================== */
(async () => {
  let products = await api.list('products');
  const categories = (await api.list('categories')).filter(c => c.kind === 'category').sort((a, b) => a.sort - b.sort);
  const catLabel = id => (categories.find(c => c.id === id) || { label: '—' }).label;
  let q = '', cat = '', show = '';

  setHead('Products', '', `<a class="btn btn-primary" href="product.html">${icon('plus')}<span>Add a bag</span></a>`);
  app.innerHTML = `
    <div class="bar">
      <label class="search">${icon('magnifying-glass')}<input type="search" id="q" placeholder="Search bags" aria-label="Search bags"></label>
      <select class="input" id="cat" style="width:auto" aria-label="Category">
        <option value="">All categories</option>${categories.map(c => `<option value="${esc(c.id)}">${esc(c.label)}</option>`).join('')}
      </select>
      <select class="input" id="show" style="width:auto" aria-label="Show">
        <option value="">Everything</option><option value="active">On the shop</option><option value="hidden">Hidden</option>
        <option value="low">Low or out of stock</option>
      </select>
    </div>
    <div id="list"></div>`;

  const summary = () => { $('#pageSub').textContent = `${products.length} bags, ${products.filter(p => p.active).length} on the shop`; };

  function render() {
    const words = q.toLowerCase();
    const found = products.filter(p => (!cat || p.category_id === cat)
      && (!words || [p.name, p.kind, ...p.colours.map(c => c.name)].join(' ').toLowerCase().includes(words))
      && (!show || (show === 'active' && p.active) || (show === 'hidden' && !p.active) || (show === 'low' && p.colours.some(c => c.stock <= LOW_STOCK))))
      .sort((a, b) => a.sort - b.sort);

    summary();
    $('#list').innerHTML = found.length ? `<div class="tiles">${found.map(p => {
      const url = `product.html?id=${encodeURIComponent(p.id)}`;
      return `
      <article class="tile${p.active ? '' : ' off'}" data-id="${esc(p.id)}">
        <a class="tile-media" href="${url}" aria-label="Edit ${esc(p.name)}">
          ${p.tag ? `<span class="tile-tag${p.tag === 'New' ? ' new' : ''}">${esc(p.tag)}</span>` : ''}
          ${p.active ? '' : `<span class="tile-flag">${icon('eye-slash')}Hidden</span>`}
          <img src="${esc(asset(p.colours[0] && p.colours[0].image_url))}" alt="" loading="lazy">
        </a>
        <div class="tile-body">
          <div class="tile-row"><h3><a href="${url}">${esc(p.name)}</a></h3><b>${money(p.price)}</b></div>
          <small>${esc(p.kind)} · ${esc(catLabel(p.category_id))}</small>
          <div class="tile-row tile-meta">
            ${stockBadge(stockOf(p))}
            <span>${p.colours.map(c => `<span class="dot" style="--c:${esc(c.hex)}" title="${esc(c.name)}: ${c.stock} in stock"></span>`).join(' ')}</span>
          </div>
        </div>
        <div class="tile-foot">
          <label class="switch" title="Show or hide on the shop"><input type="checkbox" data-toggle ${p.active ? 'checked' : ''} aria-label="Show ${esc(p.name)} on the shop"><span></span></label>
          <div class="row-actions">
            <a class="icon-btn" href="${url}" title="Edit" aria-label="Edit ${esc(p.name)}">${icon('pen')}</a>
            <button class="icon-btn" data-copy title="Duplicate" aria-label="Duplicate ${esc(p.name)}">${icon('copy', 'regular')}</button>
            <button class="icon-btn danger" data-delete title="Delete" aria-label="Delete ${esc(p.name)}">${icon('trash-can', 'regular')}</button>
          </div>
        </div>
      </article>`;
    }).join('')}</div>`
      : `<div class="card">${emptyState('bag-shopping', 'No bags found', products.length ? 'Try a different search or filter.' : 'Add your first bag to get started.')}</div>`;
  }

  $('#q').addEventListener('input', e => { q = e.target.value.trim(); render(); });
  $('#cat').addEventListener('change', e => { cat = e.target.value; render(); });
  $('#show').addEventListener('change', e => { show = e.target.value; render(); });

  $('#list').addEventListener('change', async e => {
    if (!e.target.matches('[data-toggle]')) return;
    const p = products.find(x => x.id === e.target.closest('.tile').dataset.id);
    p.active = e.target.checked;
    await api.save('products', p);
    render();
    toast(p.active ? `${p.name} is now on the shop` : `${p.name} is hidden from the shop`, p.active ? 'eye' : 'eye-slash');
  });

  $('#list').addEventListener('click', async e => {
    const tile = e.target.closest('.tile');
    if (!tile) return;
    const p = products.find(x => x.id === tile.dataset.id);
    if (e.target.closest('[data-delete]')) {
      if (!await confirmBox(`Delete ${p.name}?`, 'It will disappear from the shop. Past orders keep their record of it.')) return;
      await api.remove('products', p.id);
      products = products.filter(x => x !== p);
      render();
      toast(`${p.name} deleted`, 'trash-can');
    } else if (e.target.closest('[data-copy]')) {
      let id = p.id + '-copy', n = 2;
      while (products.some(x => x.id === id)) id = `${p.id}-copy-${n++}`;
      const copy = { ...clone(p), id, name: p.name + ' (copy)', active: false, sort: products.length + 1, created_at: new Date().toISOString().slice(0, 10) };
      copy.colours.forEach(c => { c.id = uid(); });
      await api.save('products', copy);
      flash('Copy made. It stays hidden until you switch it on.');
      location.href = `product.html?id=${encodeURIComponent(id)}`;
    }
  });

  render();
})();
