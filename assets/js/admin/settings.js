/* ==========================================================
   Virtue & Verve — admin settings
   ========================================================== */
(async () => {
  const settings = await api.settings();
  let zones = (await api.list('delivery_zones')).sort((a, b) => a.sort - b.sort);
  let delivery = (await api.list('delivery_options')).sort((a, b) => a.sort - b.sort);
  let lines = [...(settings.announcement || [])];

  setHead('Settings', 'Store details, delivery and the announcement bar');
  app.innerHTML = `
    <div class="grid-even">
      <div class="stack">
        <form class="card" id="storeForm">
          <div class="card-head"><div><h2>Store details</h2><small>Shown on the contact page and in the footer</small></div></div>
          <div class="fields">
            <label class="field">Email<input name="email" type="email" value="${esc(settings.email)}" required></label>
            <label class="field">WhatsApp<input name="whatsapp" value="${esc(settings.whatsapp)}"></label>
            <label class="field">Instagram<input name="instagram" value="${esc(settings.instagram)}"></label>
            <label class="field">When you reply<input name="hours" value="${esc(settings.hours)}"></label>
            <label class="field">Currency symbol<input name="currency" value="${esc(settings.currency)}" maxlength="4" required></label>
            <label class="field">Exchange window (days)<input name="return_days" type="number" min="0" step="1" value="${esc(settings.return_days)}" required></label>
          </div>
          <div class="form-foot"><button class="btn btn-primary" type="submit">${icon('check')}Save details</button></div>
        </form>

        <form class="card" id="barForm">
          <div class="card-head"><div><h2>Announcement bar</h2><small>The scrolling line at the very top of the shop</small></div>
            <button type="button" class="btn btn-ghost btn-sm" id="addLine">${icon('plus')}Add line</button></div>
          <div class="lines" id="lines"></div>
          <div class="form-foot"><button class="btn btn-primary" type="submit">${icon('check')}Save announcement</button></div>
        </form>

        <div class="card">
          <div class="card-head"><div><h2>Preview mode</h2><small>Shows the gold “Preview” notes on the shop</small></div></div>
          <label class="switch"><input type="checkbox" id="preview" ${settings.preview ? 'checked' : ''}><span></span>The shop is still a preview</label>
          <p class="hint">Switch this off at launch, once payments and the database are connected.</p>
        </div>
      </div>

      <div class="stack">
        <form class="card" id="deliveryForm">
          <div class="card-head"><div><h2>Delivery</h2><small>Each zone covers some states and has its own options and prices</small></div>
            <button type="button" class="btn btn-ghost btn-sm" id="addZone">${icon('plus')}Add zone</button></div>
          <div id="zones"></div>
          <div class="form-foot"><button class="btn btn-primary" type="submit">${icon('check')}Save delivery</button></div>
        </form>

        ${LIVE ? '' : `<div class="card">
          <div class="card-head"><div><h2>Sample data</h2><small>The orders, messages and customers this preview started with</small></div></div>
          <p class="preview-note" style="margin-bottom:14px">${icon('circle-info')}<span><b>Preview.</b> Everything in this admin is saved in this browser only, until the database is connected.</span></p>
          <div class="form-foot" style="margin-top:0">
            <button class="btn btn-ghost" id="clearSample">${icon('broom')}Clear sample orders</button>
            <button class="btn btn-danger" id="reset">${icon('rotate-left')}Reset everything</button>
          </div>
        </div>`}
      </div>
    </div>`;

  // ---------- Announcement lines ----------
  const renderLines = () => {
    $('#lines').innerHTML = lines.map((text, i) => `
      <div class="line-row one" data-i="${i}">
        <input class="input" value="${esc(text)}" maxlength="80" aria-label="Announcement line ${i + 1}" required>
        <button type="button" class="icon-btn danger" data-remove aria-label="Remove line" ${lines.length < 2 ? 'disabled' : ''}>${icon('trash-can', 'regular')}</button>
      </div>`).join('');
  };
  $('#lines').addEventListener('input', e => { lines[+e.target.closest('.line-row').dataset.i] = e.target.value; });
  $('#lines').addEventListener('click', e => { if (e.target.closest('[data-remove]')) { lines.splice(+e.target.closest('.line-row').dataset.i, 1); renderLines(); } });
  $('#addLine').addEventListener('click', () => { lines.push(''); renderLines(); $('#lines .line-row:last-child input').focus(); });

  // ---------- Delivery zones and their options ----------
  const areaText = z => z.regions.length === 1 ? z.regions[0]
    : z.regions.length <= 3 ? z.regions.join(', ') : `${z.regions.length} states`;

  function renderZones() {
    const covered = new Set(zones.flatMap(z => z.regions));
    const missing = REGIONS.filter(r => !covered.has(r));
    $('#zones').innerHTML = zones.map(z => `
      <div class="zone" data-zone="${esc(z.id)}">
        <div class="zone-head">
          <div><h3>${esc(z.label)}</h3><small>${icon('location-dot')}${esc(areaText(z)) || 'No states yet'}</small></div>
          <div class="row-actions">
            <button type="button" class="btn btn-ghost btn-sm" data-edit-zone>${icon('map-location-dot')}Areas</button>
            <button type="button" class="icon-btn danger" data-remove-zone title="Remove zone" aria-label="Remove ${esc(z.label)}" ${zones.length < 2 ? 'disabled' : ''}>${icon('trash-can', 'regular')}</button>
          </div>
        </div>
        <div class="lines">
          ${delivery.map((d, i) => d.zone !== z.id ? '' : `
            <div class="line-row" data-i="${i}">
              <input class="input" data-f="label" value="${esc(d.label)}" placeholder="Standard delivery" aria-label="Option name" required>
              <input class="input" data-f="note" value="${esc(d.note)}" placeholder="3 – 5 working days" aria-label="Timing">
              <input class="input" data-f="price" type="number" min="0" step="50" value="${esc(d.price)}" aria-label="Price, 0 for free" required>
              <button type="button" class="icon-btn danger" data-remove-option aria-label="Remove option">${icon('trash-can', 'regular')}</button>
            </div>`).join('')}
        </div>
        <button type="button" class="link zone-add" data-add-option>${icon('plus')} Add an option for ${esc(z.label)}</button>
      </div>`).join('')
      + (missing.length ? `<p class="preview-note">${icon('triangle-exclamation')}<span><b>Not delivered to:</b> ${missing.map(esc).join(', ')}. Customers there can’t check out until you add these states to a zone.</span></p>` : '');
  }

  function editZone(z) {
    const isNew = !z;
    z = z ? clone(z) : { id: uid(), label: '', regions: [], sort: zones.length + 1 };
    const owner = r => zones.find(x => x.id !== z.id && x.regions.includes(r));
    openSheet(isNew ? 'Add a delivery zone' : `Areas in ${z.label}`, `
      <label class="field">Zone name<input name="label" value="${esc(z.label)}" placeholder="South-West" required></label>
      <p class="hint" style="margin:12px 0 8px">Tick the states this zone covers. A state can only be in one zone, so ticking it here moves it out of any other.</p>
      <div class="regions">${REGIONS.map(r => {
        const other = owner(r);
        return `<label class="check"><input type="checkbox" name="r" value="${esc(r)}" ${z.regions.includes(r) ? 'checked' : ''}><span>${esc(r)}${other ? `<small>${esc(other.label)}</small>` : ''}</span></label>`;
      }).join('')}</div>`,
      (values, formEl) => {
        const chosen = $$('input[name="r"]:checked', formEl).map(i => i.value);
        z.label = values.label.trim();
        z.regions = chosen;
        zones.forEach(x => { if (x.id !== z.id) x.regions = x.regions.filter(r => !chosen.includes(r)); });
        if (isNew) zones.push(z); else zones[zones.findIndex(x => x.id === z.id)] = z;
        renderZones();
        toast('Zone updated — press Save delivery to keep it', 'circle-info');
      });
  }

  // Keep what's typed when rows are added or removed
  $('#zones').addEventListener('input', e => {
    const row = e.target.closest('.line-row');
    if (!row) return;
    const d = delivery[+row.dataset.i], f = e.target.dataset.f;
    d[f] = f === 'price' ? Math.max(0, Number(e.target.value) || 0) : e.target.value;
  });
  $('#zones').addEventListener('click', async e => {
    const box = e.target.closest('.zone');
    if (!box) return;
    const z = zones.find(x => x.id === box.dataset.zone);
    if (e.target.closest('[data-edit-zone]')) editZone(z);
    else if (e.target.closest('[data-add-option]')) {
      delivery.push({ id: uid(), zone: z.id, label: '', note: '', price: 0, active: true });
      renderZones();
      $(`.zone[data-zone="${CSS.escape(z.id)}"] .line-row:last-child input`).focus();
    } else if (e.target.closest('[data-remove-option]')) {
      delivery.splice(+e.target.closest('.line-row').dataset.i, 1);
      renderZones();
    } else if (e.target.closest('[data-remove-zone]')) {
      if (!await confirmBox(`Remove ${z.label}?`, 'Its delivery options go too, and its states will need to join another zone.', 'Remove')) return;
      zones = zones.filter(x => x !== z);
      delivery = delivery.filter(d => d.zone !== z.id);
      renderZones();
    }
  });
  $('#addZone').addEventListener('click', () => editZone());

  // ---------- Saving ----------
  $('#storeForm').addEventListener('submit', async e => {
    e.preventDefault();
    const v = Object.fromEntries(new FormData(e.target));
    await api.saveSettings({ ...v, return_days: Number(v.return_days) });
    toast('Store details saved');
  });
  $('#barForm').addEventListener('submit', async e => {
    e.preventDefault();
    lines = lines.map(l => l.trim()).filter(Boolean);
    await api.saveSettings({ announcement: lines });
    renderLines();
    toast('Announcement saved');
  });
  $('#deliveryForm').addEventListener('submit', async e => {
    e.preventDefault();
    if (!e.target.reportValidity()) return;
    const empty = zones.find(z => !delivery.some(d => d.zone === z.id));
    if (empty) return toast(`Add at least one option for ${empty.label}`, 'triangle-exclamation');
    zones.forEach((z, i) => { z.sort = i + 1; });
    delivery.forEach((d, i) => { d.sort = i + 1; d.label = d.label.trim(); });
    await api.replace('delivery_zones', zones);
    await api.replace('delivery_options', delivery);
    toast('Delivery saved');
  });
  $('#preview').addEventListener('change', async e => {
    await api.saveSettings({ preview: e.target.checked });
    toast(e.target.checked ? 'Preview notes are showing on the shop' : 'Preview notes are hidden on the shop', e.target.checked ? 'eye' : 'eye-slash');
  });

  if (!LIVE) $('#clearSample').addEventListener('click', async () => {
    if (!await confirmBox('Clear the sample data?', 'Removes the made-up orders, messages and newsletter sign-ups. Anything real stays.', 'Clear')) return;
    for (const table of ['orders', 'messages', 'subscribers']) await api.replace(table, (await api.list(table)).filter(r => !r.sample));
    flash('Sample data cleared');
    location.reload();
  });
  if (!LIVE) $('#reset').addEventListener('click', async () => {
    if (!await confirmBox('Reset everything?', 'Puts the bags, categories, orders and settings back to how this preview started. Your changes will be lost.', 'Reset')) return;
    await api.reset();
    flash('Everything is back to the starting data');
    location.reload();
  });

  renderLines();
  renderZones();
})();
