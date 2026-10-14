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

  // ---------- Online payment ----------
  const box = $('#payBox');
  const keep = () => { try { localStorage.setItem('vv_order', JSON.stringify(order)); } catch { /* storage unavailable */ } };
  const state = (kind, html) => { box.hidden = false; box.className = `pay-box ${kind}`; box.innerHTML = html; };
  const paid = () => state('paid', `${icon('circle-check')}<div><strong>Payment received.</strong> Thank you — Paystack has emailed you a receipt.</div>`);
  const due = text => state('due', `${icon('credit-card')}<div><strong>Payment not completed yet.</strong> ${esc(text)}</div>
    <button class="btn btn-primary" id="payNow"><span>Pay ${money(order.total)} now</span>${icon('lock')}</button>`);

  const reference = new URLSearchParams(location.search).get('reference');
  if (reference && LIVE) {
    // Just back from Paystack: confirm with the server before saying it's paid
    state('checking', `${icon('spinner')}<div><strong>Confirming your payment…</strong></div>`);
    try { history.replaceState(null, '', 'order.html'); } catch { /* file:// in some browsers */ }
    fetch(`/api/pay/verify?reference=${encodeURIComponent(reference)}`)
      .then(res => res.json().then(body => ({ ok: res.ok, body })))
      .then(({ ok, body }) => {
        if (ok && body.paid) { order.paid = true; delete order.payError; keep(); paid(); }
        else due(body.error || 'The payment didn’t go through — no money was taken. You can try again.');
      })
      .catch(() => due('We couldn’t confirm the payment just now. If money left your account, please contact us with your order number.'));
  } else if (order.paid) paid();
  else if (order.payOnline) due(order.payError || 'Your order is saved. Pay now to confirm it.');

  box.addEventListener('click', async e => {
    const button = e.target.closest('#payNow');
    if (!button) return;
    button.disabled = true;
    const pay = await startPayment(order.number, order.customer.email);
    if (pay.url) { location.href = pay.url; return; }
    button.disabled = false;
    toast(pay.error, 'circle-exclamation');
  });
}
