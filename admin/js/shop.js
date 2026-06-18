    // ── DATA ──
    const products = [
      { id:1, name:'Milano Structured Bag', category:'handbag', catLabel:'Handbag', price:45000, oldPrice:null, badge:'new', img:'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500&q=80', desc:'A structured, sophisticated handbag with a magnetic snap closure and adjustable shoulder strap. Perfect for work and weekend outings.', rating:5, reviews:62, tags:['new','luxury'] },
      { id:2, name:'Canvas Weekend Tote', category:'tote', catLabel:'Tote Bag', price:16500, oldPrice:22000, badge:'sale', img:'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?w=500&q=80', desc:'Spacious canvas tote with inner pockets and durable handles. Ideal for the market, beach, or a weekend trip.', rating:4, reviews:38, tags:['sale','casual'] },
      { id:3, name:'Velvet Night Clutch', category:'clutch', catLabel:'Clutch', price:18000, oldPrice:null, badge:'trending', img:'https://images.unsplash.com/photo-1575032617751-6ddec2089882?w=500&q=80', desc:'Luxurious velvet clutch with gold-tone hardware. The perfect evening companion for dinners, events, and occasions.', rating:5, reviews:45, tags:['trending','evening'] },
      { id:4, name:'Cognac Leather Satchel', category:'handbag', catLabel:'Handbag', price:62000, oldPrice:null, badge:'new', img:'https://images.unsplash.com/photo-1591561954557-26941169b49e?w=500&q=80', desc:'Premium cognac leather satchel with twin top handles and detachable strap. A timeless investment piece.', rating:5, reviews:29, tags:['new','luxury'] },
      { id:5, name:'City Crossbody', category:'crossbody', catLabel:'Crossbody', price:21000, oldPrice:28000, badge:'sale', img:'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?w=500&q=80', desc:'Compact and stylish crossbody bag with multiple compartments. Great for city adventures and daily errands.', rating:4, reviews:71, tags:['sale','casual'] },
      { id:6, name:'Minimal Leather Tote', category:'tote', catLabel:'Tote Bag', price:38000, oldPrice:null, badge:null, img:'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=500&q=80', desc:'Clean-lined leather tote with open top and interior zip pocket. Understated luxury at its finest.', rating:5, reviews:54, tags:['luxury'] },
      { id:7, name:'Urban Backpack', category:'backpack', catLabel:'Backpack', price:29000, oldPrice:null, badge:'new', img:'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&q=80', desc:'Sleek urban backpack with padded back panel and multiple pockets. Blends function and style effortlessly.', rating:4, reviews:33, tags:['new','casual'] },
      { id:8, name:'Bamboo Handle Bag', category:'handbag', catLabel:'Handbag', price:24000, oldPrice:null, badge:'trending', img:'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?w=500&q=80', desc:'Distinctive bamboo handle bag with a woven rattan body. A statement piece for the fashion-forward woman.', rating:5, reviews:19, tags:['trending'] },
      { id:9, name:'Pearl Mini Clutch', category:'clutch', catLabel:'Clutch', price:14000, oldPrice:null, badge:null, img:'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=500&q=80', desc:'Delicate mini clutch adorned with pearl details. Light enough for evenings, chic enough to remember.', rating:4, reviews:27, tags:['evening'] },
      { id:10, name:'Laptop Work Tote', category:'tote', catLabel:'Tote Bag', price:33000, oldPrice:40000, badge:'sale', img:'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&q=80', desc:'Professional tote with padded laptop sleeve and organizational pockets. Style meets productivity.', rating:5, reviews:88, tags:['sale','casual'] },
      { id:11, name:'Croc-Effect Mini Bag', category:'handbag', catLabel:'Handbag', price:19000, oldPrice:null, badge:'trending', img:'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?w=500&q=80', desc:'Eye-catching croc-embossed mini bag with chain strap. Tiny but mighty in the style department.', rating:4, reviews:41, tags:['trending'] },
      { id:12, name:'Vintage Flap Bag', category:'handbag', catLabel:'Handbag', price:52000, oldPrice:null, badge:'new', img:'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500&q=80', desc:'Vintage-inspired flap bag with quilted texture and chain strap. A nod to classic elegance with a modern twist.', rating:5, reviews:36, tags:['new','luxury'] },
      { id:13, name:'Straw Beach Tote', category:'tote', catLabel:'Tote Bag', price:12000, oldPrice:null, badge:null, img:'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?w=500&q=80', desc:'Lightweight woven straw tote with cotton lining. Your perfect companion for sunny beach days.', rating:4, reviews:22, tags:['casual'] },
      { id:14, name:'Evening Satin Clutch', category:'clutch', catLabel:'Clutch', price:16000, oldPrice:20000, badge:'sale', img:'https://images.unsplash.com/photo-1575032617751-6ddec2089882?w=500&q=80', desc:'Shimmering satin clutch with a sleek push-lock closure. Elevate any evening look effortlessly.', rating:5, reviews:48, tags:['sale','evening'] },
      { id:15, name:'Hiker Pro Backpack', category:'backpack', catLabel:'Backpack', price:35000, oldPrice:null, badge:null, img:'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&q=80', desc:'Rugged yet refined backpack with water-resistant lining and ergonomic straps. For the adventurous woman.', rating:4, reviews:15, tags:['casual'] },
      { id:16, name:'Chain Crossbody', category:'crossbody', catLabel:'Crossbody', price:27000, oldPrice:null, badge:'trending', img:'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?w=500&q=80', desc:'Chic chain-strap crossbody with a compact silhouette. The perfect day-to-night bag.', rating:5, reviews:57, tags:['trending','evening'] },
      { id:17, name:'Shopper Canvas Bag', category:'tote', catLabel:'Tote Bag', price:9500, oldPrice:null, badge:null, img:'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=500&q=80', desc:'Big, bold canvas shopper with colorful prints and double handles. Carry life in style.', rating:4, reviews:34, tags:['casual'] },
      { id:18, name:'Gold Clutch Minaudière', category:'clutch', catLabel:'Clutch', price:22000, oldPrice:null, badge:'new', img:'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=500&q=80', desc:'Solid-tone gold minaudière with geometric shape and wrist strap. Pure luxury in the palm of your hand.', rating:5, reviews:31, tags:['new','luxury','evening'] },
      { id:19, name:'Mini Backpack', category:'backpack', catLabel:'Backpack', price:18500, oldPrice:25000, badge:'sale', img:'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&q=80', desc:'Adorable mini backpack with adjustable straps and zippered pockets. Cute, compact, and practical.', rating:4, reviews:44, tags:['sale','casual'] },
      { id:20, name:'Tan Shoulder Bag', category:'handbag', catLabel:'Handbag', price:31000, oldPrice:null, badge:null, img:'https://images.unsplash.com/photo-1591561954557-26941169b49e?w=500&q=80', desc:'Classic tan shoulder bag with soft pebbled leather and a zip-top closure. A wardrobe staple.', rating:5, reviews:60, tags:['luxury'] },
      { id:21, name:'Wristlet Phone Bag', category:'crossbody', catLabel:'Crossbody', price:8500, oldPrice:12000, badge:'sale', img:'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?w=500&q=80', desc:'Compact wristlet that holds your essentials — phone, cards, cash — and looks great doing it.', rating:4, reviews:79, tags:['sale','casual'] },
      { id:22, name:'Luxury Croc Tote', category:'tote', catLabel:'Tote Bag', price:75000, oldPrice:null, badge:'new', img:'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=500&q=80', desc:'Statement croc-embossed luxury tote in bold color. For the woman who commands every room she walks into.', rating:5, reviews:18, tags:['new','luxury'] },
      { id:23, name:'Raffia Basket Bag', category:'handbag', catLabel:'Handbag', price:13000, oldPrice:null, badge:'trending', img:'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500&q=80', desc:'Handwoven raffia basket bag with leather trim handles. Rustic charm meets contemporary cool.', rating:4, reviews:26, tags:['trending','casual'] },
      { id:24, name:'Sport Crossbody', category:'crossbody', catLabel:'Crossbody', price:15500, oldPrice:null, badge:null, img:'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?w=500&q=80', desc:'Sporty nylon crossbody with reflective strip and adjustable strap. Function and fashion, side by side.', rating:4, reviews:37, tags:['casual'] },
    ];

    let cart = 0;
    let activeCategory = 'all';
    let activeTags = [];
    let maxPrice = 100000;
    let sortOrder = 'default';
    let isListView = false;
    let wishlist = new Set();

    const grid = document.getElementById('productsGrid');
    const cartCount = document.getElementById('cartCount');
    const productCount = document.getElementById('productCount');
    const activeFiltersBar = document.getElementById('activeFilters');

    // ── RENDER ──
    function getFiltered() {
      let list = [...products];
      if (activeCategory !== 'all') list = list.filter(p => p.category === activeCategory);
      if (activeTags.length) list = list.filter(p => activeTags.every(t => p.tags.includes(t)));
      list = list.filter(p => p.price <= maxPrice);
      if (sortOrder === 'price-asc') list.sort((a,b) => a.price - b.price);
      else if (sortOrder === 'price-desc') list.sort((a,b) => b.price - a.price);
      else if (sortOrder === 'name-asc') list.sort((a,b) => a.name.localeCompare(b.name));
      else if (sortOrder === 'new') list.sort((a,b) => (b.badge === 'new') - (a.badge === 'new'));
      return list;
    }

    function badgeHTML(b) {
      if (!b) return '';
      const map = { new:'badge-new', sale:'badge-sale', trending:'badge-trending', soldout:'badge-soldout' };
      const labels = { new:'New', sale:'Sale', trending:'Trending', soldout:'Sold Out' };
      return `<span class="product-badge ${map[b]}">${labels[b]}</span>`;
    }

    function starsHTML(r) {
      return '★'.repeat(r) + '☆'.repeat(5-r);
    }

    function fmtPrice(p) {
      return '₦' + p.toLocaleString('en-NG');
    }

    function render() {
      const filtered = getFiltered();
      productCount.textContent = filtered.length;

      if (!filtered.length) {
        grid.innerHTML = `<div class="empty-state">
          <div class="empty-icon">👜</div>
          <h3>No bags found</h3>
          <p>Try adjusting your filters</p>
          <button class="add-to-cart" onclick="clearAll()">Clear Filters</button>
        </div>`;
        return;
      }

      grid.innerHTML = filtered.map((p, i) => `
        <div class="product-card" style="animation-delay:${i * 0.06}s" data-id="${p.id}">
          <div class="product-img">
            ${badgeHTML(p.badge)}
            <img src="${p.img}" alt="${p.name}" loading="lazy"/>
            <div class="product-img-overlay"></div>
            <div class="product-actions">
              <button class="action-btn ${wishlist.has(p.id) ? 'wished' : ''}" onclick="toggleWish(${p.id},this)" title="Wishlist">
                ${wishlist.has(p.id) ? '♥' : '♡'}
              </button>
              <button class="action-btn" onclick="openModal(${p.id})" title="Quick View">👁</button>
            </div>
            <button class="quick-view-btn" onclick="openModal(${p.id})">Quick View</button>
          </div>
          <div class="product-info">
            <p class="product-category">${p.catLabel}</p>
            <h3 class="product-name">${p.name}</h3>
            <p class="product-desc">${p.desc}</p>
            <div class="product-rating">
              <span class="stars">${starsHTML(p.rating)}</span>
              <span class="rating-count">(${p.reviews})</span>
            </div>
            <div class="product-footer">
              <span class="product-price">
                ${p.oldPrice ? `<del>${fmtPrice(p.oldPrice)}</del>` : ''}
                ${fmtPrice(p.price)}
              </span>
              <button class="add-to-cart ${p.badge === 'soldout' ? 'disabled' : ''}"
                onclick="${p.badge !== 'soldout' ? `addToCart('${p.name}')` : ''}">
                ${p.badge === 'soldout' ? 'Sold Out' : 'Add to Cart'}
              </button>
            </div>
          </div>
        </div>
      `).join('');

      updateActiveFiltersBar();
    }

    // ── FILTER BAR ──
    function updateActiveFiltersBar() {
      let chips = [];
      if (activeCategory !== 'all') {
        chips.push(`<span class="active-filter-chip">${activeCategory} <span class="chip-remove" onclick="setCategory('all')">✕</span></span>`);
      }
      activeTags.forEach(t => {
        chips.push(`<span class="active-filter-chip">${t} <span class="chip-remove" onclick="removeTag('${t}')">✕</span></span>`);
      });
      if (maxPrice < 100000) {
        chips.push(`<span class="active-filter-chip">Under ${fmtPrice(maxPrice)} <span class="chip-remove" onclick="resetPrice()">✕</span></span>`);
      }
      activeFiltersBar.innerHTML = chips.join('');
    }

    // ── CATEGORY ──
    function setCategory(cat) {
      activeCategory = cat;
      document.querySelectorAll('.cat-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.cat === cat);
      });
      render();
    }
    document.querySelectorAll('.cat-btn').forEach(b => {
      b.addEventListener('click', () => setCategory(b.dataset.cat));
    });

    // ── PRICE ──
    const slider = document.getElementById('priceSlider');
    const priceVal = document.getElementById('priceVal');
    slider.addEventListener('input', () => {
      maxPrice = +slider.value;
      priceVal.textContent = fmtPrice(maxPrice);
      const pct = ((maxPrice - 5000) / (100000 - 5000)) * 100;
      slider.style.background = `linear-gradient(to right, var(--primary) 0%, var(--primary) ${pct}%, var(--border) ${pct}%)`;
      render();
    });
    function resetPrice() {
      maxPrice = 100000; slider.value = 100000; priceVal.textContent = '₦100,000';
      slider.style.background = `linear-gradient(to right, var(--primary) 0%, var(--primary) 100%, var(--border) 100%)`;
      render();
    }

    // ── TAGS ──
    document.querySelectorAll('.tag-pill').forEach(p => {
      p.addEventListener('click', () => {
        const tag = p.dataset.tag;
        if (activeTags.includes(tag)) { activeTags = activeTags.filter(t => t !== tag); p.classList.remove('active'); }
        else { activeTags.push(tag); p.classList.add('active'); }
        render();
      });
    });
    function removeTag(tag) {
      activeTags = activeTags.filter(t => t !== tag);
      document.querySelectorAll('.tag-pill').forEach(p => { if (p.dataset.tag === tag) p.classList.remove('active'); });
      render();
    }

    // ── SORT ──
    document.getElementById('sortSelect').addEventListener('change', e => {
      sortOrder = e.target.value; render();
    });

    // ── VIEW TOGGLE ──
    document.getElementById('gridViewBtn').addEventListener('click', () => {
      isListView = false; grid.classList.remove('list-view');
      document.getElementById('gridViewBtn').classList.add('active');
      document.getElementById('listViewBtn').classList.remove('active');
    });
    document.getElementById('listViewBtn').addEventListener('click', () => {
      isListView = true; grid.classList.add('list-view');
      document.getElementById('listViewBtn').classList.add('active');
      document.getElementById('gridViewBtn').classList.remove('active');
    });

    // ── CLEAR ──
    function clearAll() {
      activeCategory = 'all'; activeTags = []; maxPrice = 100000; sortOrder = 'default';
      document.querySelectorAll('.cat-btn').forEach(b => b.classList.toggle('active', b.dataset.cat === 'all'));
      document.querySelectorAll('.tag-pill').forEach(p => p.classList.remove('active'));
      document.getElementById('sortSelect').value = 'default';
      resetPrice(); render();
    }
    document.getElementById('clearFilters').addEventListener('click', clearAll);

    // ── CART ──
    function addToCart(name) {
      cart++; cartCount.textContent = cart;
      cartCount.style.transform = 'scale(1.5)';
      setTimeout(() => cartCount.style.transform = 'scale(1)', 300);
      showToast(`"${name}" added to cart!`);
    }

    // ── WISHLIST ──
    function toggleWish(id, btn) {
      if (wishlist.has(id)) { wishlist.delete(id); btn.innerHTML = '♡'; btn.classList.remove('wished'); }
      else { wishlist.add(id); btn.innerHTML = '♥'; btn.classList.add('wished'); showToast('Added to wishlist ♥'); }
    }

    // ── TOAST ──
    function showToast(msg) {
      const t = document.getElementById('toast');
      document.getElementById('toastMsg').textContent = msg;
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 2800);
    }

    // ── MODAL ──
    function openModal(id) {
      const p = products.find(x => x.id === id);
      document.getElementById('modalImg').src = p.img;
      document.getElementById('modalCat').textContent = p.catLabel;
      document.getElementById('modalName').textContent = p.name;
      document.getElementById('modalPrice').textContent = (p.oldPrice ? `₦${p.oldPrice.toLocaleString()} → ` : '') + fmtPrice(p.price);
      document.getElementById('modalDesc').textContent = p.desc;
      document.getElementById('modalAddToCart').onclick = () => { addToCart(p.name); closeModal(); };
      document.getElementById('modalOverlay').classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function closeModal() {
      document.getElementById('modalOverlay').classList.remove('open');
      document.body.style.overflow = '';
    }
    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.getElementById('modalOverlay').addEventListener('click', e => {
      if (e.target === document.getElementById('modalOverlay')) closeModal();
    });

    // ── FILTER COLLAPSE ──
    document.querySelectorAll('.filter-title').forEach(title => {
      const key = title.dataset.filter;
      const body = document.getElementById('filter-' + key);
      body.style.maxHeight = body.scrollHeight + 'px';
      title.addEventListener('click', () => {
        const collapsed = body.classList.toggle('collapsed');
        title.classList.toggle('collapsed', collapsed);
        if (!collapsed) body.style.maxHeight = body.scrollHeight + 'px';
      });
    });

    // ── NAV ──
    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('mobileNav').classList.add('open');
    });
    document.getElementById('closeNav').addEventListener('click', () => {
      document.getElementById('mobileNav').classList.remove('open');
    });

    // ── SIDEBAR TOGGLE (mobile) ──
    document.getElementById('sidebarToggle').addEventListener('click', () => {
      document.getElementById('sidebarBody').classList.toggle('open');
    });

    // ── CURSOR ──
    const cursor = document.getElementById('cursor');
    const ring = document.getElementById('cursorRing');
    document.addEventListener('mousemove', e => {
      cursor.style.left = e.clientX + 'px';
      cursor.style.top = e.clientY + 'px';
      setTimeout(() => { ring.style.left = e.clientX + 'px'; ring.style.top = e.clientY + 'px'; }, 80);
    });
    document.querySelectorAll('a,button').forEach(el => {
      el.addEventListener('mouseenter', () => { cursor.style.transform = 'translate(-50%,-50%) scale(1.8)'; cursor.style.background = 'var(--gold)'; });
      el.addEventListener('mouseleave', () => { cursor.style.transform = 'translate(-50%,-50%) scale(1)'; cursor.style.background = 'var(--primary)'; });
    });

    // ── INIT ──
    render();