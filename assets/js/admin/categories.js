/* ==========================================================
   Virtue & Verve — admin categories and edits
   A category holds bags directly ("Totes"). An edit gathers bags
   by a rule ("everything labelled New", "everything green").
   ========================================================== */
(async () => {
  let categories = (await api.list('categories')).sort((a, b) => a.sort - b.sort);
  const products = await api.list('products');
  const countIn = c => products.filter(p => inCategory(c, p)).length;
  const ruleText = c => c.kind === 'category' ? 'You choose the bags' : c.rule && c.rule.tag ? `Gathers bags labelled ${c.rule.tag}` : c.rule && c.rule.family ? `Gathers ${c.rule.family.toLowerCase()} bags` : 'Edit';

  setHead('Categories', 'How bags are grouped on the shop', `<button class="btn btn-primary" id="add">${icon('plus')}<span>Add a category</span></button>`);

  function render() {
    app.innerHTML = categories.length ? `<div class="tiles wide">${categories.map((c, i) => `
      <article class="tile${c.visible ? '' : ' off'}" data-id="${esc(c.id)}">
        <button class="tile-media" data-edit aria-label="Edit ${esc(c.label)}">
          <span class="tile-tag plain">${c.kind === 'category' ? 'Category' : 'Edit'}</span>
          ${c.visible ? '' : `<span class="tile-flag">${icon('eye-slash')}Hidden</span>`}
          <img src="${esc(asset(c.image_url))}" alt="" loading="lazy">
        </button>
        <div class="tile-body">
          <div class="tile-row"><h3>${esc(c.label)}</h3><b>${countIn(c)} ${countIn(c) === 1 ? 'bag' : 'bags'}</b></div>
          <small>${esc(ruleText(c))}${c.note ? ` · “${esc(c.note)}”` : ''}</small>
          ${c.intro ? `<p>${esc(c.intro)}</p>` : ''}
        </div>
        <div class="tile-foot">
          <label class="switch" title="Show or hide on the shop"><input type="checkbox" data-toggle ${c.visible ? 'checked' : ''} aria-label="Show ${esc(c.label)} on the shop"><span></span></label>
          <div class="row-actions">
            <button class="icon-btn" data-move="-1" title="Move earlier" aria-label="Move earlier" ${i === 0 ? 'disabled' : ''}>${icon('arrow-left')}</button>
            <button class="icon-btn" data-move="1" title="Move later" aria-label="Move later" ${i === categories.length - 1 ? 'disabled' : ''}>${icon('arrow-right')}</button>
            <button class="icon-btn" data-edit title="Edit" aria-label="Edit ${esc(c.label)}">${icon('pen')}</button>
            <button class="icon-btn danger" data-delete title="Delete" aria-label="Delete ${esc(c.label)}">${icon('trash-can', 'regular')}</button>
          </div>
        </div>
      </article>`).join('')}</div>
      <p class="hint" style="margin-top:14px">${icon('circle-info')} The order here is the order in the shop’s menu and on the home page.</p>`
      : `<div class="card">${emptyState('layer-group', 'No categories yet', 'Add one to start grouping your bags.')}</div>`;
  }

  async function saveOrder() {
    categories.forEach((c, i) => { c.sort = i + 1; });
    await api.replace('categories', categories);
  }

  function edit(existing) {
    const c = existing ? clone(existing) : { id: '', kind: 'category', label: '', singular: '', note: '', intro: '', image_url: '', rule: null, sort: categories.length + 1, visible: true };
    let photo = c.image_url;
    const ruleValue = c.rule ? (c.rule.tag ? `tag:${c.rule.tag}` : `family:${c.rule.family}`) : 'tag:New';
    const rules = [['tag:New', 'Bags labelled New'], ['tag:Bestseller', 'Bags labelled Bestseller'], ...Object.keys(FAMILIES).map(f => [`family:${f}`, `${f} bags`])];

    openSheet(existing ? `Edit ${c.label}` : 'Add a category', `
      <div class="fields">
        <div class="wide" style="max-width:130px" id="photoBox">${dropHTML(photo, 'id="catPhoto"')}</div>
        <label class="field wide">Name<input name="label" value="${esc(c.label)}" placeholder="Totes" required></label>
        <label class="field wide">Type
          <select name="kind" ${existing ? 'disabled' : ''}>
            <option value="category"${c.kind === 'category' ? ' selected' : ''}>Category — you put bags in it</option>
            <option value="edit"${c.kind === 'edit' ? ' selected' : ''}>Edit — gathers bags by a rule</option>
          </select>
        </label>
        <label class="field wide" data-for="edit">Which bags it gathers
          <select name="rule">${rules.map(([v, l]) => `<option value="${v}"${v === ruleValue ? ' selected' : ''}>${l}</option>`).join('')}</select>
        </label>
        <label class="field wide" data-for="category">One of them is called <small>(used in sentences)</small><input name="singular" value="${esc(c.singular || '')}" placeholder="tote"></label>
        <label class="field wide">Short line<input name="note" value="${esc(c.note || '')}" placeholder="Carry it all" maxlength="40"></label>
        <label class="field wide">Introduction <small>(shown on its page)</small><textarea name="intro" rows="4">${esc(c.intro || '')}</textarea></label>
      </div>`,
      async values => {
        const kind = existing ? c.kind : values.kind;
        if (!photo) { toast('Add a photo for this category', 'triangle-exclamation'); return false; }
        if (!existing) {
          c.id = slug(values.label);
          if (!c.id || categories.some(x => x.id === c.id)) { toast('That name is already in use', 'triangle-exclamation'); return false; }
        }
        const [ruleType, ruleVal] = (values.rule || '').split(':');
        Object.assign(c, { kind, label: values.label.trim(), singular: (values.singular || '').trim(), note: values.note.trim(), intro: values.intro.trim(),
          image_url: photo, rule: kind === 'edit' ? { [ruleType]: ruleVal } : null });
        try { await api.save('categories', c); } catch { return false; }
        categories = (await api.list('categories')).sort((a, b) => a.sort - b.sort);
        render();
        toast(existing ? `${c.label} saved` : `${c.label} added`);
      });

    const body = $('#sheetBody');
    const showFor = () => {
      const kind = existing ? c.kind : body.elements.kind.value;
      $$('[data-for]', body).forEach(el => { el.hidden = el.dataset.for !== kind; });
    };
    body.onchange = async e => {
      if (e.target.name === 'kind') showFor();
      if (e.target.id !== 'catPhoto') return;
      try { photo = await readPhoto(e.target.files[0]); $('#photoBox').innerHTML = dropHTML(photo, 'id="catPhoto"'); }
      catch (err) { toast(err.message, 'triangle-exclamation'); }
    };
    showFor();
  }

  document.addEventListener('click', e => { if (e.target.closest('#add')) edit(); });

  app.addEventListener('change', async e => {
    if (!e.target.matches('[data-toggle]')) return;
    const c = categories.find(x => x.id === e.target.closest('.tile').dataset.id);
    c.visible = e.target.checked;
    await api.save('categories', c);
    render();
    toast(c.visible ? `${c.label} is showing on the shop` : `${c.label} is hidden from the shop`, c.visible ? 'eye' : 'eye-slash');
  });

  app.addEventListener('click', async e => {
    const row = e.target.closest('.tile');
    if (!row) return;
    const i = categories.findIndex(x => x.id === row.dataset.id), c = categories[i];
    const move = e.target.closest('[data-move]');
    if (move) {
      const to = i + +move.dataset.move;
      [categories[i], categories[to]] = [categories[to], categories[i]];
      await saveOrder();
      render();
    } else if (e.target.closest('[data-edit]')) {
      edit(c);
    } else if (e.target.closest('[data-delete]')) {
      const inside = c.kind === 'category' ? countIn(c) : 0;
      if (inside) return toast(`Move the ${inside} ${inside === 1 ? 'bag' : 'bags'} in ${c.label} to another category first`, 'triangle-exclamation');
      if (!await confirmBox(`Delete ${c.label}?`, c.kind === 'edit' ? 'The bags in it stay on the shop.' : 'This category is empty, so nothing else changes.')) return;
      categories.splice(i, 1);
      await saveOrder();
      render();
      toast(`${c.label} deleted`, 'trash-can');
    }
  });

  render();
})();
