/* ==========================================================
   Virtue & Verve — order confirmation
   Shows the order saved on this device by checkout.js.
   ========================================================== */

let order = null;
try { order = JSON.parse(localStorage.getItem('vv_order')); } catch { /* storage unavailable */ }

if (!order || !order.items) {
  $('#orderBody').hidden = true;
  $('#orderEmpty').hidden = false;
} else {
  const placed = new Date(order.placed).toLocaleDateString(undefined, { day: 'numeric', month: 'long', year: 'numeric' });
  $('#orderHello').innerHTML = `Thank you, <em>${esc(order.customer.first)}</em>`;
  $('#orderNumber').textContent = order.number;
  $('#orderDate').textContent = placed;

  $('#orderLines').innerHTML = order.items.map(i => {
    const p = product(i.id);
    const img = p && p.colors[i.color] ? photo(p, i.color) : '';
    return `<div class="mini-line">
      <span class="mini-img">${img}<i>${i.qty}</i></span>
      <span><strong>${esc(i.name)}</strong><small>${esc(i.kind)} · ${esc(i.colour)}</small></span>
      <b>${money(i.price * i.qty)}</b>
    </div>`;
  }).join('');
  $('#sumSubtotal').textContent = money(order.subtotal);
  $('#sumDelivery').textContent = order.delivery.price ? money(order.delivery.price) : 'Free';
  $('#sumTotal').textContent = money(order.total);

  $('#orderTo').innerHTML = [`${order.customer.first} ${order.customer.last}`, ...order.address].map(esc).join('<br>');
  $('#orderContact').innerHTML = [order.customer.email, order.customer.phone].filter(Boolean).map(esc).join('<br>');
  $('#orderDelivery').innerHTML = `${esc(order.delivery.label)}<br><small>${esc(order.delivery.note)}</small>`;
  $('#orderPayment').textContent = order.payment;
  if (order.note) { $('#orderNoteWrap').hidden = false; $('#orderNote').textContent = order.note; }

  // A little celebration once the tick has drawn
  setTimeout(() => {
    const r = $('#orderTick').getBoundingClientRect();
    burst(r.left + r.width / 2, r.top + r.height / 2, 18);
  }, 1100);
}
