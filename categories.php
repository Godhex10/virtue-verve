<?php
// Establish connection to database
include './includes/db.php';

// Fetch active categories along with live item counts from products table
$categories_data = [];
if (isset($conn)) {
    $cat_query = "SELECT c.id, c.name, c.slug, c.image, COUNT(p.id) AS product_count 
                  FROM categories c 
                  LEFT JOIN products p ON c.slug = p.category_slug 
                  GROUP BY c.id, c.name, c.slug, c.image 
                  ORDER BY c.name ASC";
                  
    try {
        $res = $conn->query($cat_query);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $categories_data[] = $row;
            }
        }
    } catch (Exception $e) {
        // Fail-safe default fallback
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Categories – Virtue & Verve</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght=0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --primary: #088178;
      --primary-light: #0aac9f;
      --primary-dark: #055e57;
      --primary-faint: #e6f5f4;
      --cream: #faf8f5;
      --dark: #1a1a1a;
      --mid: #4a4a4a;
      --light: #9a9a9a;
      --white: #ffffff;
      --gold: #c9a96e;
      --border: #ece9e4;
    }

    html { scroll-behavior: smooth; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--cream);
      color: var(--dark);
      overflow-x: hidden;
    }

    /* ── CURSOR ── */
    .cursor {
      width: 12px; height: 12px;
      background: var(--primary); border-radius: 50%;
      position: fixed; top: 0; left: 0;
      pointer-events: none; z-index: 9999;
      transition: transform 0.15s ease, background 0.2s;
      transform: translate(-50%, -50%);
    }
    .cursor-ring {
      width: 36px; height: 36px;
      border: 1.5px solid var(--primary); border-radius: 50%;
      position: fixed; top: 0; left: 0;
      pointer-events: none; z-index: 9998;
      transition: transform 0.35s ease, opacity 0.2s;
      transform: translate(-50%, -50%); opacity: 0.5;
    }

    /* ── NAV ── */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      padding: 20px 5%;
      display: flex; align-items: center; justify-content: space-between;
      background: rgba(250,248,245,0.95);
      backdrop-filter: blur(12px);
      box-shadow: 0 1px 30px rgba(8,129,120,0.08);
    }
    .logo {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.8rem; font-weight: 600;
      color: var(--primary); letter-spacing: 2px; text-decoration: none;
    }
    .logo span { color: var(--gold); }
    .nav-links { display: flex; gap: 36px; list-style: none; }
    .nav-links a {
      font-size: 0.8rem; font-weight: 500;
      text-transform: uppercase; letter-spacing: 2px;
      color: var(--mid); text-decoration: none;
      position: relative; transition: color 0.3s;
    }
    .nav-links a::after {
      content: ''; position: absolute; bottom: -3px; left: 0;
      width: 0; height: 1.5px; background: var(--primary); transition: width 0.3s;
    }
    .nav-links a:hover, .nav-links a.active { color: var(--primary); }
    .nav-links a:hover::after, .nav-links a.active::after { width: 100%; }
    .nav-cta {
      background: var(--primary); color: #fff !important;
      padding: 10px 24px !important; border-radius: 2px;
      transition: background 0.3s !important;
    }
    .nav-cta::after { display: none !important; }
    .nav-cta:hover { background: var(--primary-dark) !important; }
    
    .nav-right { display: flex; align-items: center; gap: 20px; }
    .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; }
    .hamburger span { display: block; width: 24px; height: 2px; background: var(--dark); transition: 0.3s; }

    /* ── MOBILE NAV ── */
    .mobile-nav {
      display: none; position: fixed; inset: 0; z-index: 150;
      background: var(--cream); flex-direction: column;
      align-items: center; justify-content: center; gap: 32px;
    }
    .mobile-nav.open { display: flex; }
    .mobile-nav a {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem; font-weight: 300;
      color: var(--dark); text-decoration: none; transition: color 0.2s;
    }
    .mobile-nav a:hover { color: var(--primary); }
    .close-nav { position: absolute; top: 24px; right: 5%; font-size: 1.8rem; cursor: pointer; }

    /* ── PAGE HERO ── */
    .page-hero {
      padding: 140px 5% 40px;
      background: linear-gradient(135deg, #f0faf9 0%, var(--cream) 60%, #fff9f0 100%);
      position: relative; overflow: hidden;
      text-align: center;
    }
    .page-hero::before {
      content: '';
      position: absolute; top: -100px; left: 50%; transform: translateX(-50%);
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(8,129,120,0.07) 0%, transparent 70%);
      border-radius: 50%;
    }
    .hero-breadcrumb {
      display: flex; align-items: center; justify-content: center;
      gap: 8px; font-size: 0.75rem; color: var(--light);
      text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px;
      animation: fadeUp 0.7s 0.1s both;
    }
    .hero-breadcrumb a { color: var(--primary); text-decoration: none; }
    .hero-breadcrumb span { color: var(--light); }
    .page-hero h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(2.8rem, 6vw, 4.5rem); font-weight: 300;
      color: var(--dark); margin-bottom: 16px;
      animation: fadeUp 0.8s 0.2s both;
    }
    .page-hero h1 em { font-style: italic; color: var(--primary); }
    .page-hero p {
      color: var(--mid); font-size: 1rem; max-width: 480px; margin: 0 auto;
      animation: fadeUp 0.8s 0.35s both;
    }

    /* ── LUXURY SEARCH BAR CONTAINER ── */
    .search-wrapper {
      max-width: 580px; margin: 30px auto 10px; padding: 0 5%;
      animation: fadeUp 0.8s 0.45s both;
    }
    .search-box {
      position: relative; display: flex; align-items: center;
      background: var(--white); border: 1.5px solid var(--border);
      border-radius: 4px; padding: 6px 20px;
      transition: border-color 0.3s, box-shadow 0.3s;
    }
    .search-box:focus-within {
      border-color: var(--primary);
      box-shadow: 0 10px 30px rgba(8,129,120,0.06);
    }
    .search-icon { font-size: 1.05rem; color: var(--light); margin-right: 14px; }
    .search-input {
      width: 100%; border: none; background: none; outline: none;
      font-family: 'DM Sans', sans-serif; font-size: 0.92rem;
      color: var(--dark); padding: 10px 0;
    }
    .search-input::placeholder { color: var(--light); font-weight: 400; opacity: 0.8; }

    /* ── MAIN LAYOUT CONTENT ── */
    .categories-section {
      padding: 30px 5% 100px; max-width: 1300px; margin: 0 auto;
    }
    .toolbar-info {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 32px; border-bottom: 1px solid var(--border);
      padding-bottom: 16px;
    }
    .results-count { font-size: 0.88rem; color: var(--light); }
    .results-count strong { color: var(--dark); font-weight: 500; }

    /* ── CATEGORIES GRID CONTAINER ── */
    .categories-grid {
      display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px;
    }

    /* ── COMPACT REUSABLE LUXURY CARD EFFECT ── */
    .category-card {
      background: var(--white); border-radius: 6px; overflow: hidden;
      position: relative; box-shadow: 0 4px 20px rgba(0,0,0,0.02);
      text-decoration: none; display: block;
      opacity: 0; transform: translateY(30px);
      animation: cardIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    }
    .category-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 50px rgba(8,129,120,0.12);
    }
    @keyframes cardIn { to { opacity: 1; transform: translateY(0); } }

    .card-media {
      position: relative; overflow: hidden; aspect-ratio: 16/11; background: var(--border);
    }
    .card-media img {
      width: 100%; height: 100%; object-fit: cover;
      transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .category-card:hover .card-media img { transform: scale(1.06); }
    
    /* Overlay design pattern */
    .card-media-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(to top, rgba(26,26,26,0.5) 0%, rgba(26,26,26,0.1) 50%, transparent 100%);
      opacity: 0.85; transition: opacity 0.3s;
    }
    .category-card:hover .card-media-overlay { opacity: 0.95; }

    .card-content {
      padding: 24px; display: flex; align-items: center; justify-content: space-between;
      border-top: 1px solid var(--border); background: var(--white);
    }
    .card-title-group h3 {
      font-family: 'Cormorant Garamond', serif; font-size: 1.45rem;
      font-weight: 400; color: var(--dark); transition: color 0.2s;
    }
    .category-card:hover .card-title-group h3 { color: var(--primary); }
    .card-meta-text {
      font-size: 0.78rem; color: var(--light); margin-top: 2px;
      text-transform: uppercase; letter-spacing: 1px;
    }

    .card-arrow-action {
      width: 40px; height: 40px; border-radius: 50%;
      background: var(--primary-faint); color: var(--primary);
      display: flex; align-items: center; justify-content: center;
      font-size: 0.9rem; transition: background 0.3s, color 0.3s, transform 0.3s;
    }
    .category-card:hover .card-arrow-action {
      background: var(--primary); color: var(--white);
      transform: translateX(3px);
    }

    /* ── EMPTY STATE SYSTEM ── */
    .empty-state {
      text-align: center; padding: 80px 20px; grid-column: 1 / -1; display: none;
    }
    .empty-icon { font-size: 3.5rem; margin-bottom: 16px; opacity: 0.8; }
    .empty-state h3 {
      font-family: 'Cormorant Garamond', serif; font-size: 1.8rem;
      font-weight: 300; margin-bottom: 8px; color: var(--dark);
    }
    .empty-state p { color: var(--light); font-size: 0.9rem; }

    /* ── FOOTER STYLE ── */
    footer {
      background: var(--dark); color: rgba(255,255,255,0.6);
      padding: 70px 5% 30px; margin-top: auto;
    }
    .footer-grid {
      display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 50px; margin-bottom: 50px;
    }
    .footer-logo {
      font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; color: white; margin-bottom: 16px;
    }
    .footer-logo span { color: var(--gold); }
    .footer-desc { font-size: 0.85rem; line-height: 1.8; margin-bottom: 24px; }
    .footer-socials { display: flex; gap: 12px; }
    .social-btn {
      width: 36px; height: 36px; border-radius: 50%;
      border: 1px solid rgba(255,255,255,0.15); display: flex;
      align-items: center; justify-content: center; font-size: 0.9rem;
      transition: border-color 0.2s, background 0.2s; cursor: pointer;
      text-decoration: none; color: rgba(255,255,255,0.6);
    }
    .social-btn:hover { border-color: var(--primary); background: var(--primary); color: white; }
    .footer-col h4 {
      font-size: 0.72rem; font-weight: 500; letter-spacing: 3px;
      text-transform: uppercase; color: white; margin-bottom: 20px;
    }
    .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .footer-col ul a {
      font-size: 0.85rem; color: rgba(255,255,255,0.5); text-decoration: none; transition: color 0.2s;
    }
    .footer-col ul a:hover { color: var(--primary-light); }
    .newsletter-form { display: flex; gap: 8px; }
    .newsletter-form input {
      flex: 1; background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.12); color: white;
      padding: 12px 16px; border-radius: 2px; font-size: 0.82rem; outline: none;
    }
    .newsletter-form input::placeholder { color: rgba(255,255,255,0.3); }
    .newsletter-form button {
      background: var(--primary); border: none; color: white;
      padding: 12px 20px; border-radius: 2px; cursor: pointer; transition: background 0.2s;
    }
    .newsletter-form button:hover { background: var(--primary-light); }
    .footer-bottom {
      border-top: 1px solid rgba(255,255,255,0.08);
      padding-top: 28px; display: flex; align-items: center; justify-content: space-between;
    }
    .footer-bottom p { font-size: 0.78rem; }
    .footer-bottom-links { display: flex; gap: 24px; }
    .footer-bottom-links a { font-size: 0.78rem; color: rgba(255,255,255,0.4); text-decoration: none; }

    /* ── RESPONSIVE ADAPTABILITY ── */
    @media (max-width: 1024px) {
      .categories-grid { grid-template-columns: repeat(2, 1fr); gap: 24px; }
    }
    @media (max-width: 768px) {
      .nav-links { display: none; }
      .hamburger { display: flex; }
      .footer-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 540px) {
      .categories-grid { grid-template-columns: 1fr; gap: 20px; }
      .footer-grid { grid-template-columns: 1fr; }
      .footer-bottom { flex-direction: column; gap: 16px; text-align: center; }
    }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
  </style>
</head>
<body>

  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <div class="mobile-nav" id="mobileNav">
    <span class="close-nav" id="closeNav">✕</span>
    <a href="index.php">Home</a>
    <a href="categories.php" style="color:var(--primary)">Categories</a>
    <a href="products.php">Shop Catalog</a>
  </div>

  <nav>
    <a href="index.php" class="logo">Virtue &amp; <span>Verve</span></a>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="categories.php" class="active">Categories</a></li>
      <li><a href="products.php">Collection</a></li>
      <li><a href="products.php" class="nav-cta">Shop Now</a></li>
    </ul>
    <div class="nav-right">
      <div class="hamburger" id="hamburger">
        <span></span><span></span><span></span>
      </div>
    </div>
  </nav>

  <div class="page-hero">
    <div class="hero-breadcrumb">
      <a href="index.php">Home</a>
      <span>/</span>
      <span>Categories</span>
    </div>
    <h1>Shop by <em>Style</em></h1>
    <p>Explore our curated collections designed meticulously to balance daily versatility with timeless luxury aesthetics.</p>
    
    <div class="search-wrapper">
      <div class="search-box">
        <span class="search-icon">🔍</span>
        <input type="text" id="categorySearch" class="search-input" placeholder="Search categories (e.g. Handbags, Totes...)" autocomplete="off"/>
      </div>
    </div>
  </div>

  <main class="categories-section">
    <div class="toolbar-info">
      <p class="results-count">Showing <strong id="visibleCount"><?php echo count($categories_data); ?></strong> collection groupings</p>
    </div>

    <div class="categories-grid" id="categoriesGrid">
      <?php 
      if (!empty($categories_data)): 
        foreach ($categories_data as $index => $cat): 
          // Validate visual source image placeholder
          $fallback_img = 'https://images.unsplash.com/photo-1591561954557-26941169b49e?w=600&q=80';
          $cat_image = !empty($cat['image']) ? './admin/uploads/categories/' . $cat['image'] : $fallback_img;
          $cat_name = $cat['name'];
          $cat_slug = $cat['slug'];
          $count = intval($cat['product_count']);
          
          // Connect to the future customer products page with filtering criteria route parameter 
          $target_url = "products.php?cat=" . urlencode($cat_slug);
      ?>
          <a href="<?php echo $target_url; ?>" class="category-card" data-name="<?php echo htmlspecialchars(strtolower($cat_name)); ?>" style="animation-delay: <?php echo ($index * 0.05); ?>s;">
            <div class="card-media">
              <img src="<?php echo htmlspecialchars($cat_image); ?>" alt="<?php echo htmlspecialchars($cat_name); ?>" loading="lazy"/>
              <div class="card-media-overlay"></div>
            </div>
            <div class="card-content">
              <div class="card-title-group">
                <h3><?php echo htmlspecialchars($cat_name); ?></h3>
                <p class="card-meta-text"><?php echo number_format($count); ?> <?php echo ($count === 1) ? 'Product' : 'Products'; ?></p>
              </div>
              <div class="card-arrow-action">→</div>
            </div>
          </a>
      <?php 
        endforeach; 
      endif; 
      ?>

      <div class="empty-state" id="emptyState">
        <div class="empty-icon">📂</div>
        <h3>No Categories Found</h3>
        <p>We couldn't find any collection styles matching your precise query. Try searching another choice.</p>
      </div>
    </div>
  </main>

  <footer>
    <div class="footer-grid">
      <div>
        <div class="footer-logo">Virtue &amp; <span>Verve</span></div>
        <p class="footer-desc">Your go-to destination for premium, stylish bags at unbeatable prices. Fashion that speaks for itself.</p>
        <div class="footer-socials">
          <a class="social-btn" href="#">💬</a>
          <a class="social-btn" href="#">📸</a>
          <a class="social-btn" href="#">🎥</a>
          <a class="social-btn" href="#">✉️</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Shop</h4>
        <ul>
          <li><a href="products.php">New Arrivals</a></li>
          <li><a href="products.php">Handbags</a></li>
          <li><a href="products.php">Tote Bags</a></li>
          <li><a href="products.php">Clutches</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="#">Track Order</a></li>
          <li><a href="#">Returns & Exchange</a></li>
          <li><a href="#">Contact Us</a></li>
          <li><a href="#">FAQs</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Newsletter</h4>
        <p style="font-size:0.83rem;margin-bottom:16px;line-height:1.7">Get style tips & exclusive deals straight to your inbox.</p>
        <div class="newsletter-form">
          <input type="email" placeholder="your@email.com"/>
          <button>→</button>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 Virtue & Verve. All rights reserved.</p>
      <div class="footer-bottom-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
      </div>
    </div>
  </footer>

  <script>
    // 1. Premium Ambient Mouse Target Elements Tracking Follower
    const cursor = document.getElementById('cursor');
    const ring = document.getElementById('cursorRing');
    document.addEventListener('mousemove', e => {
      cursor.style.left = e.clientX + 'px';
      cursor.style.top = e.clientY + 'px';
      setTimeout(() => {
        ring.style.left = e.clientX + 'px';
        ring.style.top = e.clientY + 'px';
      }, 80);
    });
    document.querySelectorAll('a, button, .category-card, input').forEach(el => {
      el.addEventListener('mouseenter', () => {
        cursor.style.transform = 'translate(-50%,-50%) scale(1.8)';
        cursor.style.background = 'var(--gold)';
      });
      el.addEventListener('mouseleave', () => {
        cursor.style.transform = 'translate(-50%,-50%) scale(1)';
        cursor.style.background = 'var(--primary)';
      });
    });

    // 2. Responsive Mobile Sidebar Menu Toggle Handler
    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('mobileNav').classList.add('open');
    });
    document.getElementById('closeNav').addEventListener('click', () => {
      document.getElementById('mobileNav').classList.remove('open');
    });

    // 3. Instant Live Javascript Filtering Subsystem
    const searchInput = document.getElementById('categorySearch');
    const categoryCards = document.querySelectorAll('.category-card');
    const visibleCountText = document.getElementById('visibleCount');
    const emptyState = document.getElementById('emptyState');

    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      let matchCounter = 0;

      categoryCards.forEach(card => {
        const targetName = card.getAttribute('data-name');
        if (targetName.includes(query)) {
          card.style.display = 'block';
          // Restart subtle entrance transition safely
          card.style.animation = 'none';
          card.offsetHeight; 
          card.style.animation = 'cardIn 0.4s cubic-bezier(0.25,0.46,0.45,0.94) forwards';
          matchCounter++;
        } else {
          card.style.display = 'none';
        }
      });

      // Update calculations display total tally instantly
      visibleCountText.textContent = matchCounter;

      // Handle empty conditional interface view toggles
      if (matchCounter === 0) {
        emptyState.style.display = 'block';
      } else {
        emptyState.style.display = 'none';
      }
    });
  </script>
</body>
</html>