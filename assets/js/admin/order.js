/* ==========================================================
   Virtue & Verve — admin single order (order.html?id=<order id>)
   ========================================================== */
(async () => {
  const order = await api.get('orders', new URLSearchParams(location.search).get('id'));
  if (!order) {
    setHead('Order not found');
    app.innerHTML = `<div class="card">${emptyState('receipt', 'We can’t find that order', 'It may have been removed.')}<p style="text-align:center"><a class="btn btn-primary" href="orders.html">Back to orders</a></p></div>`;
    return;
  }

  async function save(message) {
    await api.save('orders', order);
    render();
    toast(message);
  }

  // Put cancelled items back on the shelf
  async function restock() {
    const products = await api.list('products');
    for (const item of order.items) {
      const p = products.find(x => x.id === item.product_id), c = p && p.colours.find(x => x.id === item.colour_id);
      if (c) { c.stock += item.qty; await api.save('products', p); }
    }
  }

  function render() {
    const at = STATUSES.findIndex(s => s.id === order.status), cancelled = order.status === 'cancelled';
    const next = !cancelled && STATUSES[at + 1];

    setHead(order.number, `Placed ${fmtDate(order.created_at, true)}`, `
      <a class="btn btn-ghost" href="orders.html">${icon('arrow-left')}<span>Orders</span></a>
      <button class="btn btn-ghost" id="print">${icon('print')}<span>Packing slip</span></button>`);

    app.innerHTML = `
      <div class="card" style="margin-bottom:18px">
        <div class="card-head">
          <div><h2>Order status</h2><small>${cancelled ? 'This order was cancelled' : next ? `Next: ${next.label.toLowerCase()}` : 'This order is complete'}</small></div>
          <div class="row-actions no-print">
            ${!cancelled && at < 3 ? `<button class="btn btn-danger btn-sm" id="cancel">${icon('ban')}Cancel order</button>` : ''}
            ${next ? `<button class="btn btn-primary" id="advance">${icon(next.icon)}${STATUSES[at].next}</button>` : ''}
            ${cancelled ? statusBadge('cancelled') : ''}
          </div>
        </div>
        ${cancelled ? '' : `<ol class="steps">${STATUSES.map((s, i) =>
          `<li class="step${i < at ? ' done' : i === at ? ' now' : ''}">${icon(i < at ? 'check' : s.icon)}${s.label}</li>`).join('')}</ol>`}
      </div>

      <div class="grid-2">
        <div class="card">
          <div class="card-head"><h2>Items</h2><small>${order.items.reduce((n, i) => n + i.qty, 0)} in this order</small></div>
          <table>
            <thead><tr><th>Bag</th><th class="num">Price</th><th class="num">Qty</th><th class="num">Total</th></tr></thead>
            <tbody>${order.items.map(i => `
              <tr>
                <td><div class="cell"><span class="thumb"><img src="${esc(asset(i.image_url))}" alt=""></span><div><strong>${esc(i.name)}</strong><small>${esc(i.kind)} · ${esc(i.colour)}</small></div></div></td>
                <td class="num">${money(i.price)}</td><td class="num">${i.qty}</td><td class="num"><strong>${money(i.price * i.qty)}</strong></td>
              </tr>`).join('')}</tbody>
          </table>
          <div style="max-width:300px;margin:14px 0 0 auto">
            <div class="sum-row"><span>Subtotal</span><b>${money(order.subtotal)}</b></div>
            <div class="sum-row"><span>${esc(order.delivery_label)}</span><b>${order.delivery_price ? money(order.delivery_price) : 'Free'}</b></div>
            <div class="sum-row total"><span>Total</span><b>${money(order.total)}</b></div>
          </div>
          ${order.gift_note ? `<p class="eyebrow" style="margin-top:18px">${icon('gift')} Gift note to handwrite</p><p class="note">${esc(order.gift_note)}</p>` : ''}
        </div>

        <div class="stack">
          <div class="card">
            <div class="card-head"><h2>Customer</h2></div>
            <dl class="info">
              <dt>Name</dt><dd>${esc(order.first_name)} ${esc(order.last_name)}</dd>
              <dt>Email</dt><dd><a class="link" href="mailto:${esc(order.email)}?subject=${encodeURIComponent('Your Virtue & Verve order ' + order.number)}">${esc(order.email)}</a></dd>
              <dt>Phone</dt><dd>${esc(order.phone || '—')}</dd>
              <dt>Deliver to</dt><dd>${order.address.map(esc).join('<br>')}</dd>
              <dt>Delivery</dt><dd>${esc(order.delivery_label)}<br><small style="color:var(--muted)">${esc(order.delivery_note || '')}</small></dd>
              <dt>Payment</dt><dd>${esc(order.payment_method)}</dd>
            </dl>
          </div>
          <div class="card no-print">
            <div class="card-head"><h2>Private note</h2><small>Only you see this</small></div>
            <label class="field"><textarea id="adminNote" rows="3" placeholder="e.g. Customer asked for delivery after 4pm" aria-label="Private note">${esc(order.admin_note || '')}</textarea></label>
            <div class="form-foot"><button class="btn btn-ghost btn-sm" id="saveNote">Save note</button></div>
          </div>
        </div>
      </div>`;
  }

  app.addEventListener('click', async e => {
    if (e.target.closest('#advance')) {
      const next = STATUSES[STATUSES.findIndex(s => s.id === order.status) + 1];
      order.status = next.id;
      save(`Order marked as ${next.label.toLowerCase()}`);
    } else if (e.target.closest('#cancel')) {
      if (!await confirmBox('Cancel this order?', 'The items go back into stock. This can’t be undone.', 'Cancel order')) return;
      order.status = 'cancelled';
      await restock();
      save('Order cancelled and items restocked');
    } else if (e.target.closest('#saveNote')) {
      order.admin_note = $('#adminNote').value.trim();
      save('Note saved');
    }
  });
  document.addEventListener('click', e => { if (e.target.closest('#print')) print(); });

  render();
})();
