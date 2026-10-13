/* ==========================================================
   Virtue & Verve — cart page
   ========================================================== */

const linesBox = $('#cartLines'), giftNote = $('#giftNote');

function renderCartPage() {
  $('#cartWrap').hidden = !cart.length;
  $('#cartEmpty').hidden = !!cart.length;
  const n = cartCount();
  $('#cartHeading').textContent = n ? `${n} ${n === 1 ? 'item' : 'items'} in your bag` : 'Your bag is empty';

  linesBox.innerHTML = cart.map((i, index) => {
    const p = product(i.id), url = productURL(p, i.color);
    return `<div class="cart-line" data-index="${index}">
      <a class="cart-img" href="${url}">${photo(p, i.color)}</a>
      <div class="cart-what">
        <h3><a href="${url}">${p.name}</a></h3>
        <small>${p.kind} · ${p.colors[i.color][0]}</small>
        <span class="cart-each">${money(p.price)} each</span>
        <button class="remove">${icon('trash-can', 'regular')} Remove</button>
      </div>
      ${qtyHTML(i.qty)}
      <strong class="cart-total">${money(p.price * i.qty)}</strong>
    </div>`;
  }).join('');

  $('#sumSubtotal').textContent = money(subtotal());
  $('#sumTotal').textContent = money(subtotal());
}

bindLines(linesBox);
document.addEventListener('cart:change', renderCartPage);

// The gift note travels with the order to checkout
try { giftNote.value = sessionStorage.getItem('vv_note') || ''; } catch { /* storage unavailable */ }
giftNote.addEventListener('input', () => {
  try { sessionStorage.setItem('vv_note', giftNote.value); } catch { /* storage unavailable */ }
});

// Suggestions: bags that aren't already in the cart
const moreGrid = $('#cartMore');
moreGrid.innerHTML = PRODUCTS.filter(p => !cart.some(i => i.id === p.id)).slice(0, 4)
  .map((p, i) => cardHTML(p, `data-reveal style="--d:${i * .08}s"`)).join('');
bindGrid(moreGrid);
observeReveals();

renderCartPage();
