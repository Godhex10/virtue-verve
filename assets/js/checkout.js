/* ==========================================================
   Virtue & Verve — checkout
   Collects details, saves the order on this device and moves to order.html.
   No payment is taken here: card payments hand over to a payment provider at launch.
   ========================================================== */

const form = $('#checkoutForm');
const picked = name => (form.elements[name] || {}).value;
const deliveryChoice = () => DELIVERY.find(d => d.id === picked('delivery')) || DELIVERY[0];

if (!cart.length) {
  $('#checkoutBody').hidden = true;
  $('#checkoutEmpty').hidden = false;
} else {
  const radio = (name, o, i, extra) => `
    <label class="radio-card">
      <input type="radio" name="${name}" value="${o.id}" ${i ? '' : 'checked'}>
      <span class="radio-dot"></span>
      <span class="radio-text"><strong>${o.icon ? icon(o.icon) : ''}${o.label}</strong><small>${o.note}</small></span>
      ${extra}
    </label>`;
  $('#deliveryOptions').innerHTML = DELIVERY.map((d, i) => radio('delivery', d, i, `<b>${d.price ? money(d.price) : 'Free'}</b>`)).join('');
  $('#paymentOptions').innerHTML = PAYMENTS.map((p, i) => radio('payment', p, i, '')).join('');
  try { form.elements.note.value = sessionStorage.getItem('vv_note') || ''; } catch { /* storage unavailable */ }

  const renderSummary = () => {
    const d = deliveryChoice();
    $('#sumLines').innerHTML = cart.map(i => {
      const p = product(i.id);
      return `<div class="mini-line">
        <span class="mini-img">${photo(p, i.color)}<i>${i.qty}</i></span>
        <span><strong>${p.name}</strong><small>${p.kind} · ${p.colors[i.color][0]}</small></span>
        <b>${money(p.price * i.qty)}</b>
      </div>`;
    }).join('');
    $('#sumSubtotal').textContent = money(subtotal());
    $('#sumDelivery').textContent = d.price ? money(d.price) : 'Free';
    $('#sumTotal').textContent = money(subtotal() + d.price);
    $('#placeLabel').textContent = `Place order — ${money(subtotal() + d.price)}`;
  };
  form.addEventListener('change', renderSummary);
  document.addEventListener('cart:change', () => cart.length ? renderSummary() : location.reload());
  renderSummary();

  form.addEventListener('submit', e => {
    e.preventDefault();
    if (!form.checkValidity()) {
      form.classList.add('tried'); // switches on the red outlines for missing fields
      const first = $(':invalid', form);
      first.scrollIntoView({ behavior: 'smooth', block: 'center' });
      first.focus({ preventScroll: true });
      return toast('Please fill in the highlighted fields', 'circle-exclamation');
    }

    const f = Object.fromEntries(new FormData(form)), d = deliveryChoice();
    const order = {
      number: 'VV-' + String(Math.floor(100000 + Math.random() * 900000)),
      placed: new Date().toISOString(),
      items: cart.map(i => {
        const p = product(i.id);
        return { id: i.id, color: i.color, qty: i.qty, name: p.name, kind: p.kind, colour: p.colors[i.color][0], price: p.price };
      }),
      subtotal: subtotal(),
      delivery: { label: d.label, note: d.note, price: d.price },
      total: subtotal() + d.price,
      payment: PAYMENTS.find(p => p.id === f.payment).label,
      customer: { first: f.first, last: f.last, email: f.email, phone: f.phone },
      address: [f.address, f.address2, f.city, f.region, f.postcode, f.country].filter(Boolean),
      note: f.note
    };
    try {
      localStorage.setItem('vv_order', JSON.stringify(order));
      sessionStorage.removeItem('vv_note');
    } catch { /* storage unavailable */ }
    cart = [];
    store.set('vv_cart', cart);
    location.href = 'order.html';
  });
}
