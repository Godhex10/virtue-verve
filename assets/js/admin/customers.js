/* ==========================================================
   Virtue & Verve — admin customers and newsletter list
   Customers are worked out from the orders: one row per email address.
   ========================================================== */
(async () => {
  const orders = await api.list('orders');
  let subscribers = (await api.list('subscribers')).sort((a, b) => b.created_at.localeCompare(a.created_at));

  const byEmail = {};
  orders.forEach(o => {
    const key = o.email.toLowerCase();
    const c = byEmail[key] || (byEmail[key] = { name: `${o.first_name} ${o.last_name}`, email: o.email, phone: o.phone, orders: 0, spent: 0, last: o.created_at, town: '' });
    c.orders++;
    if (o.status !== 'cancelled') c.spent += o.total;
    if (o.created_at >= c.last) { c.last = o.created_at; c.town = o.address[o.address.length - 1] || ''; c.phone = o.phone || c.phone; }
  });
  const customers = Object.values(byEmail).sort((a, b) => b.spent - a.spent);
  let tab = 'customers', q = '';

  // Saves a list as a spreadsheet file
  function download(name, rows) {
    const csv = rows.map(r => r.map(v => `"${String(v ?? '').replace(/"/g, '""')}"`).join(',')).join('\r\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob(['﻿' + csv], { type: 'text/csv' }));
    a.download = name;
    a.click();
    URL.revokeObjectURL(a.href);
    toast('Spreadsheet downloaded', 'file-arrow-down');
  }

  setHead('Customers', '', `<button class="btn btn-ghost" id="export">${icon('file-arrow-down')}<span>Download list</span></button>`);
  app.innerHTML = `
    <div class="bar">
      <div class="tabs" id="tabs"></div>
      <span class="spacer"></span>
      <label class="search">${icon('magnifying-glass')}<input type="search" id="q" placeholder="Name or email" aria-label="Search"></label>
    </div>
    <div id="list"></div>`;

  function render() {
    const words = q.toLowerCase();
    $('#pageSub').textContent = `${customers.length} customers, ${subscribers.length} on the newsletter`;
    $('#tabs').innerHTML = [['customers', 'Customers', customers.length], ['newsletter', 'Newsletter', subscribers.length]]
      .map(([id, label, n]) => `<button class="tab${id === tab ? ' active' : ''}" data-tab="${id}">${label}<b>${n}</b></button>`).join('');

    if (tab === 'customers') {
      const found = customers.filter(c => !words || `${c.name} ${c.email}`.toLowerCase().includes(words));
      $('#list').innerHTML = found.length ? `<div class="table-wrap"><table>
        <thead><tr><th>Customer</th><th>Phone</th><th>Town</th><th class="num">Orders</th><th class="num">Spent</th><th>Last order</th></tr></thead>
        <tbody>${found.map(c => `
          <tr data-href="orders.html">
            <td><div class="cell"><span class="avatar">${esc(c.name[0])}</span><div><strong>${esc(c.name)}</strong><small>${esc(c.email)}</small></div></div></td>
            <td>${esc(c.phone || '—')}</td><td>${esc(c.town || '—')}</td>
            <td class="num">${c.orders}</td><td class="num"><strong>${money(c.spent)}</strong></td><td>${ago(c.last)}</td>
          </tr>`).join('')}</tbody></table></div>`
        : `<div class="card">${emptyState('users', 'No customers found', customers.length ? 'Nothing matches that search.' : 'Customers appear here after their first order.')}</div>`;
    } else {
      const found = subscribers.filter(s => !words || s.email.toLowerCase().includes(words));
      $('#list').innerHTML = found.length ? `<div class="table-wrap"><table>
        <thead><tr><th>Email</th><th>Joined</th><th></th></tr></thead>
        <tbody>${found.map(s => `
          <tr data-email="${esc(s.email)}"><td><strong>${esc(s.email)}</strong></td><td>${fmtDate(s.created_at)}</td>
          <td><div class="row-actions"><button class="icon-btn danger" data-remove title="Remove" aria-label="Remove ${esc(s.email)}">${icon('trash-can', 'regular')}</button></div></td></tr>`).join('')}</tbody></table></div>`
        : `<div class="card">${emptyState('envelope-open-text', 'No one here yet', 'People who join the Verve Club on the shop appear here.')}</div>`;
    }
  }

  $('#tabs').addEventListener('click', e => { const t = e.target.closest('[data-tab]'); if (t) { tab = t.dataset.tab; render(); } });
  $('#q').addEventListener('input', e => { q = e.target.value.trim(); render(); });
  $('#list').addEventListener('click', async e => {
    if (!e.target.closest('[data-remove]')) return;
    const email = e.target.closest('tr').dataset.email;
    if (!await confirmBox('Remove from the newsletter?', `${email} will stop receiving emails.`, 'Remove')) return;
    await api.remove('subscribers', email, 'email');
    subscribers = subscribers.filter(s => s.email !== email);
    render();
    toast('Removed from the newsletter', 'trash-can');
  });
  document.addEventListener('click', e => {
    if (!e.target.closest('#export')) return;
    if (tab === 'customers') download('virtue-and-verve-customers.csv', [['Name', 'Email', 'Phone', 'Town', 'Orders', 'Spent', 'Last order'],
      ...customers.map(c => [c.name, c.email, c.phone, c.town, c.orders, c.spent, fmtDate(c.last)])]);
    else download('virtue-and-verve-newsletter.csv', [['Email', 'Joined'], ...subscribers.map(s => [s.email, fmtDate(s.created_at)])]);
  });

  render();
})();
