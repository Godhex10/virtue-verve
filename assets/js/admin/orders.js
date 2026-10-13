/* ==========================================================
   Virtue & Verve — admin orders list
   ========================================================== */
(async () => {
  const orders = (await api.list('orders')).sort((a, b) => b.created_at.localeCompare(a.created_at));
  const tabs = [{ id: 'all', label: 'All' }, ...STATUSES, { id: 'cancelled', label: 'Cancelled' }];
  let tab = new URLSearchParams(location.search).get('status') || 'all', q = '';

  setHead('Orders', `${orders.length} in total`);
  app.innerHTML = `
    <div class="bar">
      <div class="tabs" id="tabs"></div>
      <span class="spacer"></span>
      <label class="search">${icon('magnifying-glass')}<input type="search" id="q" placeholder="Order number, name or email" aria-label="Search orders"></label>
    </div>
    <div id="list"></div>`;

  function render() {
    $('#tabs').innerHTML = tabs.map(t => {
      const n = t.id === 'all' ? orders.length : orders.filter(o => o.status === t.id).length;
      return `<button class="tab${t.id === tab ? ' active' : ''}" data-tab="${t.id}">${t.label}<b>${n}</b></button>`;
    }).join('');

    const words = q.toLowerCase();
    const found = orders.filter(o => (tab === 'all' || o.status === tab)
      && (!words || [o.number, o.first_name, o.last_name, o.email].join(' ').toLowerCase().includes(words)));

    $('#list').innerHTML = found.length ? `<div class="table-wrap"><table>
      <thead><tr><th>Order</th><th>Placed</th><th>Customer</th><th>Items</th><th>Payment</th><th>Status</th><th class="num">Total</th></tr></thead>
      <tbody>${found.map(o => `
        <tr data-href="order.html?id=${o.id}">
          <td><strong>${esc(o.number)}</strong></td>
          <td>${fmtDate(o.created_at)}<small>${ago(o.created_at)}</small></td>
          <td>${esc(o.first_name)} ${esc(o.last_name)}<small>${esc(o.email)}</small></td>
          <td>${o.items.reduce((n, i) => n + i.qty, 0)}<small>${esc(o.items.map(i => i.name).join(', '))}</small></td>
          <td>${esc(o.payment_method)}<small>${esc(o.delivery_label)}</small></td>
          <td>${statusBadge(o.status)}</td>
          <td class="num"><strong>${money(o.total)}</strong></td>
        </tr>`).join('')}</tbody></table></div>`
      : `<div class="card">${emptyState('receipt', 'No orders here', q ? 'Nothing matches that search.' : 'Orders with this status will appear here.')}</div>`;
  }

  $('#tabs').addEventListener('click', e => { const t = e.target.closest('[data-tab]'); if (t) { tab = t.dataset.tab; render(); } });
  $('#q').addEventListener('input', e => { q = e.target.value.trim(); render(); });
  render();
})();
