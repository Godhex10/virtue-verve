    // ── CURSOR ──
    const cursor = document.getElementById('cursor');
    const ring   = document.getElementById('cursorRing');
    document.addEventListener('mousemove', e => {
      cursor.style.left = e.clientX + 'px';
      cursor.style.top  = e.clientY + 'px';
      setTimeout(() => { ring.style.left = e.clientX + 'px'; ring.style.top = e.clientY + 'px'; }, 80);
    });
    document.querySelectorAll('a,button').forEach(el => {
      el.addEventListener('mouseenter', () => { cursor.style.transform = 'translate(-50%,-50%) scale(2)'; cursor.style.background = 'var(--gold)'; });
      el.addEventListener('mouseleave', () => { cursor.style.transform = 'translate(-50%,-50%) scale(1)'; cursor.style.background = 'var(--primary)'; });
    });

    // ── NAV ──
    document.getElementById('hamburger').addEventListener('click', () => { document.getElementById('mobileNav').classList.add('open'); });
    document.getElementById('closeNav').addEventListener('click', () => { document.getElementById('mobileNav').classList.remove('open'); });

    // ── THUMBNAILS ──
    const mainImg = document.getElementById('mainImg');
    document.querySelectorAll('.thumb').forEach(t => {
      t.addEventListener('click', () => {
        document.querySelectorAll('.thumb').forEach(x => x.classList.remove('active'));
        t.classList.add('active');
        mainImg.style.opacity = '0';
        mainImg.style.transform = 'scale(0.97)';
        setTimeout(() => {
          mainImg.src = t.dataset.src;
          mainImg.style.opacity = '1';
          mainImg.style.transform = 'scale(1)';
        }, 200);
      });
    });
    mainImg.style.transition = 'opacity 0.25s ease, transform 0.25s ease';

    // ── ZOOM ──
    mainImg.addEventListener('click', () => { mainImg.classList.toggle('zoomed'); });

    // ── WISHLIST ──
    const wishBtn = document.getElementById('wishBtn');
    let wished = false;
    wishBtn.addEventListener('click', () => {
      wished = !wished;
      wishBtn.textContent = wished ? '♥' : '♡';
      wishBtn.classList.toggle('wished', wished);
      showToast(wished ? 'Added to wishlist ♥' : 'Removed from wishlist');
    });

    // ── QTY ──
    const qtyNum = document.getElementById('qtyNum');
    document.getElementById('qtyMinus').addEventListener('click', () => {
      if (+qtyNum.value > 1) { qtyNum.value = +qtyNum.value - 1; updateStickyPrice(); }
    });
    document.getElementById('qtyPlus').addEventListener('click', () => {
      if (+qtyNum.value < 10) { qtyNum.value = +qtyNum.value + 1; updateStickyPrice(); }
    });
    function updateStickyPrice() {
      document.querySelector('.sticky-price').textContent = '₦' + (45000 * +qtyNum.value).toLocaleString('en-NG');
    }

    // ── COLOR SWATCHES ──
    document.querySelectorAll('.color-swatch').forEach(s => {
      s.addEventListener('click', () => {
        document.querySelectorAll('.color-swatch').forEach(x => x.classList.remove('active'));
        s.classList.add('active');
        document.getElementById('selectedColor').textContent = '— ' + s.dataset.color;
      });
    });

    // ── CART ──
    let cartItems = 0;
    const cartCount = document.getElementById('cartCount');
    function addToCart(name) {
      cartItems++;
      cartCount.textContent = cartItems;
      cartCount.style.transform = 'scale(1.6)';
      setTimeout(() => { cartCount.style.transform = 'scale(1)'; }, 300);
      showToast(`"${name}" added to cart!`);
    }
    document.getElementById('addToCartBtn').addEventListener('click', () => {
      addToCart('Milano Structured Bag');
      const btn = document.getElementById('addToCartBtn');
      btn.textContent = '✓ Added!';
      btn.style.background = 'var(--primary-dark)';
      setTimeout(() => { btn.textContent = '🛍 Add to Cart'; btn.style.background = ''; }, 2000);
    });
    document.getElementById('buyNowBtn').addEventListener('click', () => {
      addToCart('Milano Structured Bag');
      showToast('Proceeding to checkout…');
    });
    document.getElementById('stickyAddBtn').addEventListener('click', () => { addToCart('Milano Structured Bag'); });

    // ── TOAST ──
    function showToast(msg) {
      const t = document.getElementById('toast');
      document.getElementById('toastMsg').textContent = msg;
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 2800);
    }

    // ── SHARE ──
    document.getElementById('copyLink').addEventListener('click', () => {
      navigator.clipboard?.writeText(window.location.href).catch(() => {});
      showToast('Link copied to clipboard!');
    });

    // ── STICKY BAR ──
    const stickyBar  = document.getElementById('stickyBar');
    const ctaSection = document.querySelector('.cta-section');
    const stickyObs  = new IntersectionObserver(entries => {
      stickyBar.classList.toggle('visible', !entries[0].isIntersecting);
    }, { threshold: 0 });
    stickyObs.observe(ctaSection);

    // ── ACCORDION ──
    document.querySelectorAll('.acc-header').forEach(h => {
      h.addEventListener('click', () => {
        const item = h.parentElement;
        const body = item.querySelector('.acc-body');
        const isOpen = item.classList.toggle('open');
        body.style.maxHeight = isOpen ? body.scrollHeight + 'px' : '0';
      });
    });
    // init open
    document.querySelectorAll('.acc-item.open .acc-body').forEach(b => {
      b.style.maxHeight = b.scrollHeight + 'px';
    });

    // ── SCROLL REVEAL ──
    const revealObs = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          revealObs.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    document.querySelectorAll('.review-card, .related-card').forEach(el => revealObs.observe(el));

    // ── RATING BARS ANIMATE ──
    const barObs = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.querySelectorAll('.bar-fill').forEach(fill => {
            fill.style.width = fill.dataset.width;
          });
          barObs.unobserve(e.target);
        }
      });
    }, { threshold: 0.3 });
    barObs.observe(document.getElementById('ratingBars'));