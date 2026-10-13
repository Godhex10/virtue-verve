/* ==========================================================
   Virtue & Verve — admin dashboard
   ========================================================== */
(async () => {
  const [orders, products, messages] = await Promise.all([api.list('orders'), api.list('products'), api.list('messages')]);
  const now = Date.now(), DAYS = 14;
  const live = orders.filter(o => o.status !== 'cancelled');
  const within = (list, from, to = 0) => list.filter(o => { const age = now - new Date(o.created_at); return age < from * DAY && age >= to * DAY; });
  const sum = list => list.reduce((t, o) => t + o.total, 0);

  const recent = within(live, DAYS), before = within(live, DAYS * 2, DAYS);
  const revenue = sum(recent), prior = sum(before);
  const change = prior ? Math.round((revenue - prior) / prior * 100) : null;
  const waiting = orders.filter(o => ['new', 'paid', 'packed'].includes(o.status));
  const lowStock = products.flatMap(p => p.colours.filter(c => c.stock <= LOW_STOCK).map(c => ({ p, c }))).sort((a, b) => a.c.stock - b.c.stock);

  const hour = new Date().getHours();
  setHead(hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening',
    new Date().toLocaleDateString(undefined, { weekday: 'long', day: 'numeric', month: 'long' }),
    `<a class="btn btn-primary" href="product.html">${icon('plus')}<span>Add a bag</span></a>`);

  // ---------- Sales per day ----------
  const start = new Date(); start.setHours(0, 0, 0, 0);
  const days = Array.from({ length: DAYS }, (_, i) => {
    const from = start.getTime() - (DAYS - 1 - i) * DAY;
    const list = live.filter(o => { const t = new Date(o.created_at).getTime(); return t >= from && t < from + DAY; });
    const date = new Date(from);
    return { value: sum(list), orders: list.length, short: date.toLocaleDateString(undefined, { day: 'numeric', month: 'short' }),
      long: date.toLocaleDateString(undefined, { weekday: 'long', day: 'numeric', month: 'long' }) };
  });

  // ---------- Best sellers (units sold in the period) ----------
  const sold = {};
  recent.forEach(o => o.items.forEach(i => { sold[i.product_id] = (sold[i.product_id] || 0) + i.qty; }));
  const top = Object.entries(sold).map(([id, units]) => ({ p: products.find(p => p.id === id), units }))
    .filter(t => t.p).sort((a, b) => b.units - a.units).slice(0, 5);
  const topMax = top.length ? top[0].units : 1;

  const stat = (ic, label, value, note, hero = '') => `
    <div class="stat${hero}">
      <span class="stat-icon">${icon(ic)}</span>
      <p class="stat-label">${label}</p>
      <p class="stat-value">${value}</p>
      <p class="stat-note">${note}</p>
    </div>`;
  const delta = change === null ? 'No sales in the 14 days before'
    : `<b class="${change >= 0 ? 'up' : 'down'}">${icon(change >= 0 ? 'arrow-trend-up' : 'arrow-trend-down')} ${change >= 0 ? '+' : ''}${change}%</b> vs the 14 days before`;

  app.innerHTML = `
    <div class="stats">
      ${stat('sack-dollar', 'Sales, last 14 days', compact(revenue), delta, ' hero')}
      ${stat('receipt', 'Orders, last 14 days', recent.length, recent.length ? `${money(Math.round(revenue / recent.length))} average order` : 'No orders yet')}
      ${stat('box-open', 'Orders to deal with', waiting.length, waiting.length ? `${orders.filter(o => o.status === 'new').length} new, ${orders.filter(o => o.status === 'paid').length} paid, ${orders.filter(o => o.status === 'packed').length} packed` : 'All caught up')}
      ${stat('triangle-exclamation', 'Low or out of stock', lowStock.length, lowStock.length ? `${lowStock.filter(l => l.c.stock <= 0).length} sold out` : 'Everything is well stocked')}
    </div>

    <div class="grid-2">
      <div class="stack">
        <div class="card">
          <div class="card-head">
            <div><h2>Sales per day</h2><small>Last 14 days, cancelled orders left out</small></div>
            <button class="btn btn-ghost btn-sm" id="chartToggle">${icon('table')}<span>View as table</span></button>
          </div>
          <div class="chart" id="chart"></div>
          <div id="chartTable" hidden>
            <table><thead><tr><th>Day</th><th class="num">Orders</th><th class="num">Sales</th></tr></thead>
            <tbody>${days.map(d => `<tr><td>${d.long}</td><td class="num">${d.orders}</td><td class="num">${money(d.value)}</td></tr>`).join('')}</tbody></table>
          </div>
        </div>

        <div class="card">
          <div class="card-head"><h2>Latest orders</h2><a class="link" href="orders.html">See all ${icon('arrow-right')}</a></div>
          ${orders.length ? `<table>
            <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th class="num">Total</th></tr></thead>
            <tbody>${[...orders].sort((a, b) => b.created_at.localeCompare(a.created_at)).slice(0, 6).map(o => `
              <tr data-href="order.html?id=${o.id}">
                <td><strong>${esc(o.number)}</strong><small>${ago(o.created_at)}</small></td>
                <td>${esc(o.first_name)} ${esc(o.last_name)}</td>
                <td>${statusBadge(o.status)}</td>
                <td class="num">${money(o.total)}</td>
              </tr>`).join('')}</tbody></table>` : emptyState('receipt', 'No orders yet', 'New orders will appear here.')}
        </div>
      </div>

      <div class="stack">
        <div class="card">
          <div class="card-head"><div><h2>Best sellers</h2><small>Bags sold, last 14 days</small></div></div>
          ${top.length ? `<ol class="rank">${top.map(t => `
            <li>
              <span class="thumb"><img src="${esc(asset(t.p.colours[0] && t.p.colours[0].image_url))}" alt=""></span>
              <div><strong>${esc(t.p.name)}</strong><div class="meter"><span style="width:${t.units / topMax * 100}%"></span></div></div>
              <span>${t.units} sold</span>
            </li>`).join('')}</ol>` : emptyState('bag-shopping', 'Nothing sold yet', 'Your most popular bags will show here.')}
        </div>

        <div class="card">
          <div class="card-head"><h2>Stock to top up</h2><a class="link" href="products.html">Products ${icon('arrow-right')}</a></div>
          ${lowStock.length ? `<table><tbody>${lowStock.slice(0, 6).map(({ p, c }) => `
            <tr data-href="product.html?id=${encodeURIComponent(p.id)}">
              <td><div class="cell"><span class="thumb"><img src="${esc(asset(c.image_url))}" alt=""></span><div><strong>${esc(p.name)}</strong><small>${esc(c.name)}</small></div></div></td>
              <td class="num">${stockBadge(c.stock)}</td>
            </tr>`).join('')}</tbody></table>` : emptyState('circle-check', 'All stocked up', 'Nothing is running low.')}
        </div>

        ${messages.some(m => !m.is_read) ? `<div class="card">
          <div class="card-head"><h2>Unread messages</h2><a class="link" href="messages.html">Inbox ${icon('arrow-right')}</a></div>
          <table><tbody>${messages.filter(m => !m.is_read).slice(0, 3).map(m => `
            <tr data-href="messages.html?id=${m.id}"><td><strong>${esc(m.name)}</strong><small>${esc(m.topic || 'Message')} · ${ago(m.created_at)}</small></td></tr>`).join('')}</tbody></table>
        </div>` : ''}
      </div>
    </div>`;

  // ---------- Draw the bar chart ----------
  const chart = $('#chart'), W = 680, H = 240, pad = { l: 46, r: 6, t: 12, b: 28 };
  const iw = W - pad.l - pad.r, ih = H - pad.t - pad.b, band = iw / DAYS, bw = band - 6;
  const peak = Math.max(...days.map(d => d.value), 1);
  // A round step that gives at most four gridlines, whatever the currency's scale
  const unit = 10 ** Math.floor(Math.log10(peak / 4));
  const step = [1, 2, 2.5, 5, 10].map(m => m * unit).find(s => peak / s <= 4);
  const top4 = Math.ceil(peak / step) * step, y = v => pad.t + ih - v / top4 * ih, base = pad.t + ih;
  const ticks = Array.from({ length: top4 / step + 1 }, (_, i) => i * step);

  chart.innerHTML = `<svg viewBox="0 0 ${W} ${H}" role="img" aria-label="Sales per day for the last 14 days">
    ${ticks.map(t => `<line class="grid-line" x1="${pad.l}" x2="${W - pad.r}" y1="${y(t)}" y2="${y(t)}"/>
      <text class="axis-text" x="${pad.l - 8}" y="${y(t) + 4}" text-anchor="end">${compact(t)}</text>`).join('')}
    ${days.map((d, i) => {
      const x = pad.l + i * band + 3, h = base - y(d.value), r = Math.min(4, h);
      // rounded at the top only, so every bar sits flat on the baseline
      const shape = h > 0 ? `M${x},${base}V${y(d.value) + r}Q${x},${y(d.value)} ${x + r},${y(d.value)}H${x + bw - r}Q${x + bw},${y(d.value)} ${x + bw},${y(d.value) + r}V${base}Z` : '';
      return `<rect class="band" data-i="${i}" x="${pad.l + i * band}" y="${pad.t}" width="${band}" height="${ih}"/><path class="bar-mark" d="${shape}"/>
        ${i % 2 === (DAYS - 1) % 2 ? `<text class="axis-text" x="${x + bw / 2}" y="${H - 8}" text-anchor="middle">${d.short}</text>` : ''}`;
    }).join('')}
  </svg><div class="tip" id="tip"></div>`;

  const tip = $('#tip');
  chart.addEventListener('mousemove', e => {
    const hit = e.target.closest('.band');
    if (!hit) return tip.classList.remove('on');
    const d = days[+hit.dataset.i], box = chart.getBoundingClientRect(), r = hit.getBoundingClientRect();
    const barTop = pad.t + (y(d.value) - pad.t); // in chart units
    tip.innerHTML = `<b>${money(d.value)}</b><span>${d.orders} ${d.orders === 1 ? 'order' : 'orders'} · ${d.long}</span>`;
    tip.style.left = Math.min(box.width - 90, Math.max(90, r.left - box.left + r.width / 2)) + 'px';
    tip.style.top = barTop / H * box.height + 'px';
    tip.classList.add('on');
  });
  chart.addEventListener('mouseleave', () => tip.classList.remove('on'));

  $('#chartToggle').addEventListener('click', e => {
    const showTable = $('#chartTable').hidden;
    $('#chartTable').hidden = !showTable;
    chart.hidden = showTable;
    e.currentTarget.innerHTML = showTable ? `${icon('chart-column')}<span>View as chart</span>` : `${icon('table')}<span>View as table</span>`;
  });
})();
