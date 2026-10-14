/* ==========================================================
   Virtue & Verve — checkout
   Collects details, saves the order on this device and moves to order.html.
   No payment is taken here: card payments hand over to a payment provider at launch.
   Delivery options depend on the customer's state (see ZONES in data.js).
   ========================================================== */

const form = $('#checkoutForm');
const picked = name => (form.elements[name] || {}).value;
const OUTSIDE = REGIONS[REGIONS.length - 1]; // 'Outside Nigeria'
const zoneNow = () => zoneOf(picked('region'));
const deliveryChoice = () => {
  const zone = zoneNow();
  return zone ? DELIVERY.find(d => d.id === picked('delivery') && d.zone === zone.id) : null;
};

if (!cart.length) {
  $('#checkoutBody').hidden = true;
  $('#checkoutEmpty').hidden = false;
} else {
  const radio = (name, o, i, extra) => `
    <label class="radio-card">
      <input type="radio" name="${name}" value="${o.id}" ${i ? '' : 'checked required'}>
      <span class="radio-dot"></span>
      <span class="radio-text"><strong>${o.icon ? icon(o.icon) : ''}${o.label}</strong><small>${o.note}</small></span>
      ${extra}
    </label>`;

  $('#regionSelect').innerHTML = '<option value="">Choose your state</option>'
    + REGIONS.map(r => `<option>${esc(r)}</option>`).join('');
  $('#paymentOptions').innerHTML = PAYMENTS.map((p, i) => radio('payment', p, i, '')).join('');
  try { form.elements.note.value = sessionStorage.getItem('vv_note') || ''; } catch { /* storage unavailable */ }

  // Only the options for the customer's zone are offered
  function renderDelivery() {
    const zone = zoneNow(), options = zone ? DELIVERY.filter(d => d.zone === zone.id) : [];
    const abroad = picked('region') === OUTSIDE, country = form.elements.country;
    $('#countryField').hidden = !abroad;
    country.required = abroad;
    if (!abroad) country.value = '';

    $('#deliveryOptions').innerHTML = !picked('region')
      ? `<p class="delivery-hint">${icon('location-dot')}Choose your state above to see delivery options and prices.</p>`
      : options.length
        ? `<p class="delivery-zone">${icon('truck-fast')}Delivering to <b>${esc(zone.label)}</b></p>`
          + options.map((d, i) => radio('delivery', d, i, `<b>${d.price ? money(d.price) : 'Free'}</b>`)).join('')
        : `<p class="delivery-hint">${icon('circle-info')}We don't deliver to ${esc(picked('region'))} yet. <a href="contact.html">Get in touch</a> and we'll see what we can do.</p>`;
  }

  const renderSummary = () => {
    const d = deliveryChoice(), total = subtotal() + (d ? d.price : 0);
    $('#sumLines').innerHTML = cart.map(i => {
      const p = product(i.id);
      return `<div class="mini-line">
        <span class="mini-img">${photo(p, i.color)}<i>${i.qty}</i></span>
        <span><strong>${p.name}</strong><small>${p.kind} · ${p.colors[i.color][0]}</small></span>
        <b>${money(p.price * i.qty)}</b>
      </div>`;
    }).join('');
    $('#sumSubtotal').textContent = money(subtotal());
    $('#sumDelivery').textContent = !d ? 'Choose your state' : d.price ? money(d.price) : 'Free';
    $('#sumTotal').textContent = money(total);
    $('#placeLabel').textContent = `Place order — ${money(total)}`;
  };

  form.addEventListener('change', e => {
    if (e.target.name === 'region') renderDelivery();
    renderSummary();
  });
  document.addEventListener('cart:change', () => cart.length ? renderSummary() : location.reload());
  renderDelivery();
  renderSummary();

  form.addEventListener('submit', async e => {
    e.preventDefault();
    const d = deliveryChoice();
    if (!form.checkValidity() || !d) {
      form.classList.add('tried'); // switches on the red outlines for missing fields
      const first = $(':invalid', form) || $('#deliveryOptions');
      first.scrollIntoView({ behavior: 'smooth', block: 'center' });
      if (first.focus) first.focus({ preventScroll: true });
      return toast(d ? 'Please fill in the highlighted fields' : 'Please choose your state and a delivery option', 'circle-exclamation');
    }

    const f = Object.fromEntries(new FormData(form)), zone = zoneNow();
    const order = {
      number: 'VV-' + String(Math.floor(100000 + Math.random() * 900000)),
      placed: new Date().toISOString(),
      items: cart.map(i => {
        const p = product(i.id);
        return { id: i.id, color: i.color, qty: i.qty, name: p.name, kind: p.kind, colour: p.colors[i.color][0], price: p.price };
      }),
      subtotal: subtotal(),
      delivery: { label: d.label, note: d.note, price: d.price, zone: zone.label },
      total: subtotal() + d.price,
      payment: PAYMENTS.find(p => p.id === f.payment).label,
      customer: { first: f.first, last: f.last, email: f.email, phone: f.phone },
      region: f.region,
      address: [f.address, f.address2, f.city, f.region === OUTSIDE ? '' : f.region, f.postcode, f.region === OUTSIDE ? f.country : 'Nigeria'].filter(Boolean),
      note: f.note
    };
    if (LIVE) {
      // The database works out prices, checks stock and gives the order its number
      const button = $('button[type="submit"]', form);
      button.disabled = true;
      $('#placeLabel').textContent = 'Placing your order…';
      const { data: placed, error } = await sb.rpc('place_order', { payload: {
        customer: { first_name: f.first, last_name: f.last, email: f.email, phone: f.phone },
        address: order.address, region: f.region, delivery_id: d.id, payment_method: order.payment, gift_note: f.note || '',
        items: cart.map(i => ({ colour_id: product(i.id).colors[i.color][5], qty: i.qty }))
      } });
      if (error) {
        button.disabled = false;
        renderSummary();
        return toast(error.message, 'circle-exclamation');
      }
      Object.assign(order, { number: placed.number, subtotal: Number(placed.subtotal), total: Number(placed.total) });
      order.delivery.price = Number(placed.delivery_price);
      try { localStorage.removeItem('vv_catalogue'); } catch { /* storage unavailable */ } // stock has changed
    } else sendToAdmin('orders', order);
    try {
      localStorage.setItem('vv_order', JSON.stringify(order));
      sessionStorage.removeItem('vv_note');
    } catch { /* storage unavailable */ }
    cart = [];
    store.set('vv_cart', cart);
    location.href = 'order.html';
  });
}
