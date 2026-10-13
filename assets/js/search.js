/* ==========================================================
   Virtue & Verve — search results (search.html?q=<words>)
   ========================================================== */

const term = (new URLSearchParams(location.search).get('q') || '').trim();
const results = searchProducts(term);
const resultGrid = $('#grid');

$('#pageSearch').value = term;
$('#searchPills').innerHTML = COLLECTIONS.map(c => `<a class="pill" href="category.html?c=${c.id}">${c.label}</a>`).join('');

if (term) {
  document.title = `“${term}” — Search — Virtue & Verve`;
  $('#searchTitle').innerHTML = `Results for <em>“${esc(term)}”</em>`;
  $('#searchCount').textContent = results.length
    ? `${results.length} ${results.length === 1 ? 'bag' : 'bags'} found`
    : 'Nothing matched — try a colour, a shape or a name.';
}

if (results.length) {
  resultGrid.innerHTML = results.map((p, i) => cardHTML(p, `style="animation-delay:${Math.min(i, 8) * .05}s"`)).join('');
  $$('.card', resultGrid).forEach(c => c.classList.add('pop'));
  $('#searchMore').hidden = true;
} else {
  // Nothing to show yet: offer the bestsellers instead of a blank page
  $('#moreTitle').innerHTML = term ? 'While you’re here, <em>our most loved</em>' : 'Not sure where to start? <em>Our most loved</em>';
  resultGrid.innerHTML = PRODUCTS.filter(p => p.tag === 'Bestseller').concat(PRODUCTS.filter(p => p.tag === 'New')).slice(0, 4)
    .map((p, i) => cardHTML(p, `data-reveal style="--d:${i * .08}s"`)).join('');
  observeReveals();
}
bindGrid(resultGrid);
