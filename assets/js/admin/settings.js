/* ==========================================================
   Virtue & Verve — admin settings
   ========================================================== */
(async () => {
  const settings = await api.settings();
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
      </div>

      <div class="stack">
        <form class="card" id="deliveryForm">
          <div class="card-head"><div><h2>Delivery options</h2><small>What customers choose from at checkout</small></div>
            <button type="button" class="btn btn-ghost btn-sm" id="addDelivery">${icon('plus')}Add option</button></div>
          <div class="lines" id="delivery"></div>
          <div class="form-foot"><button class="btn btn-primary" type="submit">${icon('check')}Save delivery</button></div>
        </form>

        <div class="card">
          <div class="card-head"><div><h2>Preview mode</h2><small>Shows the gold “Preview” notes on the shop</small></div></div>
          <label class="switch"><input type="checkbox" id="preview" ${settings.preview ? 'checked' : ''}><span></span>The shop is still a preview</label>
          <p class="hint">Switch this off at launch, once payments and the database are connected.</p>
        </div>

        <div class="card">
          <div class="card-head"><div><h2>Sample data</h2><small>The orders, messages and customers this preview started with</small></div></div>
          <p class="preview-note" style="margin-bottom:14px">${icon('circle-info')}<span><b>Preview.</b> Everything in this admin is saved in this browser only, until the database is connected.</span></p>
          <div class="form-foot" style="margin-top:0">
            <button class="btn btn-ghost" id="clearSample">${icon('broom')}Clear sample orders</button>
            <button class="btn btn-danger" id="reset">${icon('rotate-left')}Reset everything</button>
          </div>
        </div>
      </div>
    </div>`;

  const renderLines = () => {
    $('#lines').innerHTML = lines.map((text, i) => `
      <div class="line-row one" data-i="${i}">
        <input class="input" value="${esc(text)}" maxlength="80" aria-label="Announcement line ${i + 1}" required>
        <button type="button" class="icon-btn danger" data-remove aria-label="Remove line" ${lines.length < 2 ? 'disabled' : ''}>${icon('trash-can', 'regular')}</button>
      </div>`).join('');
  };
  const renderDelivery = () => {
    $('#delivery').innerHTML = delivery.map((d, i) => `
      <div class="line-row" data-i="${i}">
        <input class="input" data-f="label" value="${esc(d.label)}" placeholder="Standard delivery" aria-label="Name" required>
        <input class="input" data-f="note" value="${esc(d.note)}" placeholder="3 – 5 working days" aria-label="Timing">
        <input class="input" data-f="price" type="number" min="0" step="0.01" value="${esc(d.price)}" aria-label="Price (0 for free)" required>
        <button type="button" class="icon-btn danger" data-remove aria-label="Remove option" ${delivery.length < 2 ? 'disabled' : ''}>${icon('trash-can', 'regular')}</button>
      </div>`).join('');
  };

  // Keep what's typed when a row is added or removed
  $('#lines').addEventListener('input', e => { lines[+e.target.closest('.line-row').dataset.i] = e.target.value; });
  $('#delivery').addEventListener('input', e => {
    const d = delivery[+e.target.closest('.line-row').dataset.i], f = e.target.dataset.f;
    d[f] = f === 'price' ? Math.max(0, Number(e.target.value) || 0) : e.target.value;
  });
  $('#lines').addEventListener('click', e => { if (e.target.closest('[data-remove]')) { lines.splice(+e.target.closest('.line-row').dataset.i, 1); renderLines(); } });
  $('#delivery').addEventListener('click', e => { if (e.target.closest('[data-remove]')) { delivery.splice(+e.target.closest('.line-row').dataset.i, 1); renderDelivery(); } });
  $('#addLine').addEventListener('click', () => { lines.push(''); renderLines(); $('#lines .line-row:last-child input').focus(); });
  $('#addDelivery').addEventListener('click', () => { delivery.push({ id: uid(), label: '', note: '', price: 0, active: true }); renderDelivery(); $('#delivery .line-row:last-child input').focus(); });

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
    delivery.forEach((d, i) => { d.sort = i + 1; d.label = d.label.trim(); });
    await api.replace('delivery_options', delivery);
    toast('Delivery options saved');
  });
  $('#preview').addEventListener('change', async e => {
    await api.saveSettings({ preview: e.target.checked });
    toast(e.target.checked ? 'Preview notes are showing on the shop' : 'Preview notes are hidden on the shop', e.target.checked ? 'eye' : 'eye-slash');
  });

  $('#clearSample').addEventListener('click', async () => {
    if (!await confirmBox('Clear the sample data?', 'Removes the made-up orders, messages and newsletter sign-ups. Anything real stays.', 'Clear')) return;
    for (const table of ['orders', 'messages', 'subscribers']) await api.replace(table, (await api.list(table)).filter(r => !r.sample));
    flash('Sample data cleared');
    location.reload();
  });
  $('#reset').addEventListener('click', async () => {
    if (!await confirmBox('Reset everything?', 'Puts the bags, categories, orders and settings back to how this preview started. Your changes will be lost.', 'Reset')) return;
    await api.reset();
    flash('Everything is back to the starting data');
    location.reload();
  });

  renderLines();
  renderDelivery();
})();
