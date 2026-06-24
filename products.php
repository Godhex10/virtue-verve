<?php
include './includes/db.php';

// 1. Capture and sanitize URL parameters
$selected_category = isset($_GET['cat']) ? trim($_GET['cat']) : 'all';
$sort_order        = isset($_GET['sort']) ? trim($_GET['sort']) : 'default';

// 2. Build product query
// KEY FIX: filter on p.category_slug directly — reliable, always populated.
// The old code filtered via c.slug through the JOIN, which breaks when category_id is NULL.
$query = "SELECT p.*, c.name AS category_name
          FROM products p
          LEFT JOIN categories c ON p.category_slug = c.slug
          WHERE p.status = 'active'";

$params = [];
$types  = "";

if ($selected_category !== 'all') {
    $query   .= " AND p.category_slug = ?";
    $params[] = $selected_category;
    $types   .= "s";
}

switch ($sort_order) {
    case 'price-asc':  $query .= " ORDER BY p.price ASC";       break;
    case 'price-desc': $query .= " ORDER BY p.price DESC";      break;
    case 'name-asc':   $query .= " ORDER BY p.name ASC";        break;
    case 'new':        $query .= " ORDER BY p.created_at DESC"; break;
    default:           $query .= " ORDER BY p.id DESC";         break;
}

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("<div style='padding:20px;background:#fff5f5;color:#c53030;border-left:4px solid #e53e3e;margin:20px;font-family:sans-serif;'><h3>Query Error</h3><p>" . htmlspecialchars($conn->error) . "</p></div>");
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Fetch all categories dynamically for sidebar (with live product counts)
$all_categories   = [];
$category_counts  = [];
$total_active_bags = 0;

$cat_query = "SELECT c.id, c.name, c.slug, COUNT(p.id) AS total
              FROM categories c
              LEFT JOIN products p ON c.slug = p.category_slug AND p.status = 'active'
              WHERE c.status = 'active'
              GROUP BY c.id, c.name, c.slug
              ORDER BY c.name ASC";
$cat_res = $conn->query($cat_query);
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $all_categories[]                  = $row;
        $category_counts[$row['slug']]     = $row['total'];
        $total_active_bags                += $row['total'];
    }
}

// 4. Get the display name for the active category (for hero title)
$active_cat_name = 'Collection';
if ($selected_category !== 'all') {
    foreach ($all_categories as $cat) {
        if ($cat['slug'] === $selected_category) {
            $active_cat_name = $cat['name'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($active_cat_name) ?> – Virtue & Verve</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --primary: #088178; --primary-light: #0aac9f; --primary-dark: #055e57;
      --primary-faint: #e6f5f4; --cream: #faf8f5; --dark: #1a1a1a;
      --mid: #4a4a4a; --light: #9a9a9a; --white: #ffffff;
      --gold: #c9a96e; --border: #ece9e4;
    }

    html { scroll-behavior: smooth; }
    body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--dark); overflow-x: hidden; }

    /* ── CURSOR ── */
    .cursor {
      width: 12px; height: 12px; background: var(--primary); border-radius: 50%;
      position: fixed; top: 0; left: 0; pointer-events: none; z-index: 9999;
      transition: transform 0.15s ease, background 0.2s; transform: translate(-50%, -50%);
    }
    .cursor-ring {
      width: 36px; height: 36px; border: 1.5px solid var(--primary); border-radius: 50%;
      position: fixed; top: 0; left: 0; pointer-events: none; z-index: 9998;
      transition: transform 0.35s ease, opacity 0.2s; transform: translate(-50%, -50%); opacity: 0.5;
    }

    /* ── NAV ── */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      padding: 20px 5%; display: flex; align-items: center; justify-content: space-between;
      background: rgba(250,248,245,0.95); backdrop-filter: blur(12px);
      box-shadow: 0 1px 30px rgba(8,129,120,0.08);
    }
    .logo { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; font-weight: 600; color: var(--primary); letter-spacing: 2px; text-decoration: none; }
    .logo span { color: var(--gold); }
    .nav-links { display: flex; gap: 36px; list-style: none; }
    .nav-links a { font-size: 0.8rem; font-weight: 500; text-transform: uppercase; letter-spacing: 2px; color: var(--mid); text-decoration: none; position: relative; transition: color 0.3s; }
    .nav-links a::after { content: ''; position: absolute; bottom: -3px; left: 0; width: 0; height: 1.5px; background: var(--primary); transition: width 0.3s; }
    .nav-links a:hover, .nav-links a.active { color: var(--primary); }
    .nav-links a:hover::after, .nav-links a.active::after { width: 100%; }
    .nav-cta { background: var(--primary); color: #fff !important; padding: 10px 24px !important; border-radius: 2px; transition: background 0.3s !important; }
    .nav-cta::after { display: none !important; }
    .nav-cta:hover { background: var(--primary-dark) !important; }
    .nav-right { display: flex; align-items: center; gap: 20px; }
    .cart-btn { position: relative; background: none; border: none; cursor: pointer; font-size: 1.3rem; color: var(--dark); transition: color 0.2s; }
    .cart-btn:hover { color: var(--primary); }
    .cart-count { position: absolute; top: -6px; right: -8px; background: var(--primary); color: white; font-size: 0.6rem; font-weight: 600; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; }
    .hamburger span { display: block; width: 24px; height: 2px; background: var(--dark); transition: 0.3s; }

    /* ── MOBILE NAV ── */
    .mobile-nav { display: none; position: fixed; inset: 0; z-index: 150; background: var(--cream); flex-direction: column; align-items: center; justify-content: center; gap: 32px; }
    .mobile-nav.open { display: flex; }
    .mobile-nav a { font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 300; color: var(--dark); text-decoration: none; transition: color 0.2s; }
    .mobile-nav a:hover { color: var(--primary); }
    .close-nav { position: absolute; top: 24px; right: 5%; font-size: 1.8rem; cursor: pointer; }

    /* ── PAGE HERO ── */
    .page-hero {
      padding: 140px 5% 60px;
      background: linear-gradient(135deg, #f0faf9 0%, var(--cream) 60%, #fff9f0 100%);
      position: relative; overflow: hidden; text-align: center;
    }
    .page-hero::before {
      content: ''; position: absolute; top: -100px; left: 50%; transform: translateX(-50%);
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
    .hero-breadcrumb a:hover { text-decoration: underline; }
    .hero-breadcrumb span { color: var(--light); }
    .page-hero h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(2.8rem, 6vw, 5rem); font-weight: 300; color: var(--dark); margin-bottom: 16px; animation: fadeUp 0.8s 0.2s both; }
    .page-hero h1 em { font-style: italic; color: var(--primary); }
    .page-hero p { color: var(--mid); font-size: 1rem; max-width: 480px; margin: 0 auto; animation: fadeUp 0.8s 0.35s both; }

    /* ── SEARCH INPUT ── */
    .search-wrapper { position: relative; margin-bottom: 24px; }
    .search-bar-input {
      width: 100%; padding: 12px 16px 12px 42px;
      border: 1.5px solid var(--border); border-radius: 4px;
      font-family: 'DM Sans', sans-serif; font-size: 0.88rem;
      color: var(--dark); background: var(--white); outline: none;
      transition: border-color 0.3s, box-shadow 0.3s;
    }
    .search-bar-input:focus { border-color: var(--primary); box-shadow: 0 4px 12px rgba(8,129,120,0.05); }
    .search-icon-svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--light); pointer-events: none; }

    /* ── SHOP LAYOUT ── */
    .shop-layout { display: grid; grid-template-columns: 260px 1fr; gap: 40px; padding: 60px 5% 100px; max-width: 1400px; margin: 0 auto; }
    .sidebar { position: sticky; top: 90px; height: fit-content; }
    .filter-section { margin-bottom: 36px; }
    .filter-title { font-size: 0.72rem; font-weight: 500; text-transform: uppercase; letter-spacing: 3px; color: var(--dark); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border); }
    .category-list { display: flex; flex-direction: column; gap: 4px; }
    .cat-btn {
      display: flex; align-items: center; justify-content: space-between;
      padding: 10px 14px; border-radius: 4px; cursor: pointer;
      font-size: 0.85rem; color: var(--mid); transition: background 0.2s, color 0.2s;
      border: none; background: none; width: 100%; text-align: left; text-decoration: none;
    }
    .cat-btn:hover, .cat-btn.active { background: var(--primary-faint); color: var(--primary); font-weight: 500; }
    .cat-count-badge { font-size: 0.7rem; background: rgba(0,0,0,0.07); padding: 2px 8px; border-radius: 100px; color: var(--mid); }

    /* ── TOOLBAR ── */
    .shop-toolbar { margin-bottom: 28px; }
    .result-count { font-size: 0.88rem; color: var(--light); }
    .result-count strong { color: var(--dark); font-weight: 500; }
    .sort-select { padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 4px; font-family: 'DM Sans', sans-serif; font-size: 0.85rem; color: var(--mid); background: var(--white); outline: none; cursor: pointer; transition: border-color 0.2s; }
    .sort-select:focus { border-color: var(--primary); }

    /* ── PRODUCTS GRID ── */
    .products-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .product-card { background: var(--white); border-radius: 6px; overflow: hidden; transition: transform 0.35s cubic-bezier(0.25,0.46,0.45,0.94), box-shadow 0.35s; position: relative; }
    .product-card:hover { transform: translateY(-8px); box-shadow: 0 20px 60px rgba(8,129,120,0.13); }
    .product-img { position: relative; overflow: hidden; aspect-ratio: 4/3; }
    .product-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.7s cubic-bezier(0.25,0.46,0.45,0.94); }
    .product-card:hover .product-img img { transform: scale(1.08); }
    .product-link { text-decoration: none; color: inherit; display: block; }
    .product-badge { position: absolute; top: 14px; left: 14px; font-size: 0.65rem; font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase; padding: 5px 12px; border-radius: 100px; z-index: 2; color: white; }
    .badge-new { background: var(--primary); }
    .badge-sale { background: #e07b3d; }
    .product-info { padding: 18px 18px 20px; }
    .product-category { font-size: 0.68rem; color: var(--primary); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 6px; }
    .product-name { font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; font-weight: 400; color: var(--dark); margin-bottom: 4px; }
    .product-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 12px; }
    .product-price { font-size: 1rem; font-weight: 500; color: var(--primary); }
    .product-price del { font-size: 0.82rem; color: var(--light); margin-right: 4px; }
    .add-to-cart { background: var(--primary); color: white; border: none; padding: 9px 18px; border-radius: 2px; font-size: 0.72rem; font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase; cursor: pointer; transition: background 0.2s; }
    .add-to-cart:hover { background: var(--primary-dark); }

    /* ── EMPTY STATE ── */
    .empty-state { grid-column: 1/-1; text-align: center; padding: 80px 20px; display: none; }
    .empty-icon { font-size: 3.5rem; margin-bottom: 16px; opacity: 0.7; }
    .empty-state h3 { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; font-weight: 300; margin-bottom: 8px; color: var(--dark); }
    .empty-state p { color: var(--light); font-size: 0.9rem; }
    .empty-state a { color: var(--primary); text-decoration: none; font-size: 0.85rem; display: inline-block; margin-top: 16px; border-bottom: 1px solid var(--primary); }

    /* ── FOOTER ── */
    footer { background: var(--dark); color: rgba(255,255,255,0.6); padding: 70px 5% 30px; margin-top: 60px; }
    .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 50px; margin-bottom: 50px; }
    .footer-logo { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; color: white; margin-bottom: 16px; }
    .footer-logo span { color: var(--gold); }
    .footer-desc { font-size: 0.85rem; line-height: 1.8; margin-bottom: 24px; }
    .footer-socials { display: flex; gap: 12px; }
    .social-btn { width: 36px; height: 36px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 0.9rem; transition: border-color 0.2s, background 0.2s; cursor: pointer; text-decoration: none; color: rgba(255,255,255,0.6); }
    .social-btn:hover { border-color: var(--primary); background: var(--primary); color: white; }
    .footer-col h4 { font-size: 0.72rem; font-weight: 500; letter-spacing: 3px; text-transform: uppercase; color: white; margin-bottom: 20px; }
    .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .footer-col ul a { font-size: 0.85rem; color: rgba(255,255,255,0.5); text-decoration: none; transition: color 0.2s; }
    .footer-col ul a:hover { color: var(--primary-light); }
    .newsletter-form { display: flex; gap: 8px; }
    .newsletter-form input { flex: 1; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); color: white; padding: 12px 16px; border-radius: 2px; font-size: 0.82rem; outline: none; }
    .newsletter-form input::placeholder { color: rgba(255,255,255,0.3); }
    .newsletter-form button { background: var(--primary); border: none; color: white; padding: 12px 20px; border-radius: 2px; cursor: pointer; transition: background 0.2s; }
    .newsletter-form button:hover { background: var(--primary-light); }
    .footer-bottom { border-top: 1px solid rgba(255,255,255,0.08); padding-top: 28px; display: flex; align-items: center; justify-content: space-between; }
    .footer-bottom p { font-size: 0.78rem; }
    .footer-bottom-links { display: flex; gap: 24px; }
    .footer-bottom-links a { font-size: 0.78rem; color: rgba(255,255,255,0.4); text-decoration: none; }

    /* ── RESPONSIVE ── */
    @media (max-width: 1100px) { .products-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 860px) {
      .shop-layout { grid-template-columns: 1fr; }
      .sidebar { position: static; }
      .nav-links { display: none; }
      .hamburger { display: flex; }
      .footer-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 540px) {
      .products-grid { grid-template-columns: 1fr; }
      .footer-grid { grid-template-columns: 1fr; }
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
    <a href="categories.php">Categories</a>
    <a href="products.php" style="color:var(--primary)">Shop Catalog</a>
  </div>

  <nav>
    <a href="index.php" class="logo">Virtue &amp; <span>Verve</span></a>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="categories.php">Categories</a></li>
      <li><a href="products.php" class="active">Collection</a></li>
      <li><a href="products.php" class="nav-cta">Shop Now</a></li>
    </ul>
    <div class="nav-right">
      <button class="cart-btn">🛒 <span class="cart-count">0</span></button>
      <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
    </div>
  </nav>

  <!-- PAGE HERO with breadcrumb + dynamic title -->
  <div class="page-hero">
    <div class="hero-breadcrumb">
      <a href="index.php">Home</a>
      <span>/</span>
      <a href="categories.php">Categories</a>
      <?php if ($selected_category !== 'all'): ?>
        <span>/</span>
        <span><?= htmlspecialchars($active_cat_name) ?></span>
      <?php endif; ?>
    </div>
    <h1>Our <em><?= htmlspecialchars($active_cat_name) ?></em></h1>
    <p>Discover premium bags crafted for every woman, every occasion, every story.</p>
  </div>

  <div class="shop-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">

      <!-- SEARCH BAR -->
      <div class="search-wrapper">
        <svg class="search-icon-svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" id="catalogSearch" class="search-bar-input" placeholder="Search products..." autocomplete="off"/>
      </div>

      <!-- DYNAMIC CATEGORY FILTER -->
      <div class="filter-section">
        <div class="filter-title">Categories</div>
        <div class="category-list">
          <a href="products.php?cat=all&sort=<?= urlencode($sort_order) ?>" class="cat-btn <?= $selected_category === 'all' ? 'active' : '' ?>">
            All Bags <span class="cat-count-badge"><?= $total_active_bags ?></span>
          </a>
          <?php foreach ($all_categories as $cat): ?>
            <a href="products.php?cat=<?= urlencode($cat['slug']) ?>&sort=<?= urlencode($sort_order) ?>"
               class="cat-btn <?= $selected_category === $cat['slug'] ? 'active' : '' ?>">
              <?= htmlspecialchars($cat['name']) ?>
              <span class="cat-count-badge"><?= $category_counts[$cat['slug']] ?? 0 ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

    </aside>

    <!-- MAIN PRODUCT AREA -->
    <main class="shop-main">
      <div class="shop-toolbar">
        <div style="display:flex;justify-content:space-between;align-items:center;width:100%;flex-wrap:wrap;gap:16px;">
          <p class="result-count">Showing <strong id="displayedCount"><?= count($products) ?></strong> items</p>
          <select class="sort-select" id="sortSelect" onchange="location=this.value;">
            <option value="products.php?cat=<?= urlencode($selected_category) ?>&sort=default"     <?= $sort_order==='default'    ? 'selected':'' ?>>Sort: Featured</option>
            <option value="products.php?cat=<?= urlencode($selected_category) ?>&sort=price-asc"  <?= $sort_order==='price-asc'  ? 'selected':'' ?>>Price: Low to High</option>
            <option value="products.php?cat=<?= urlencode($selected_category) ?>&sort=price-desc" <?= $sort_order==='price-desc' ? 'selected':'' ?>>Price: High to Low</option>
            <option value="products.php?cat=<?= urlencode($selected_category) ?>&sort=name-asc"   <?= $sort_order==='name-asc'   ? 'selected':'' ?>>Name: A–Z</option>
            <option value="products.php?cat=<?= urlencode($selected_category) ?>&sort=new"        <?= $sort_order==='new'        ? 'selected':'' ?>>Newest First</option>
          </select>
        </div>
      </div>

      <div class="products-grid" id="productsGrid">
        <?php if (!empty($products)): ?>
          <?php foreach ($products as $product): ?>
            <div class="product-card"
                 data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>"
                 data-desc="<?= htmlspecialchars(strtolower($product['description'] ?? '')) ?>">
              <!-- Clicking card goes to product-details.php -->
              <a href="product-details.php?slug=<?= urlencode($product['slug']) ?>" class="product-link">
                <div class="product-img">
                  <img src="./admin/uploads/products/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>"
                       alt="<?= htmlspecialchars($product['name']) ?>"
                       loading="lazy"
                       onerror="this.src='https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&q=80'"/>
                  <?php if (!empty($product['badge'])): ?>
                    <span class="product-badge badge-<?= htmlspecialchars($product['badge']) ?>"><?= htmlspecialchars(ucfirst($product['badge'])) ?></span>
                  <?php endif; ?>
                </div>
                <div class="product-info">
                  <p class="product-category"><?= htmlspecialchars($product['category_name'] ?? '') ?></p>
                  <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                  <div class="product-footer">
                    <span class="product-price">
                      <?php if (!empty($product['old_price'])): ?>
                        <del>₦<?= number_format($product['old_price'], 2) ?></del>
                      <?php endif; ?>
                      ₦<?= number_format($product['price'], 2) ?>
                    </span>
                    <button class="add-to-cart" onclick="event.preventDefault();">Add</button>
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <!-- Empty state shown when no DB results OR search yields nothing -->
        <div class="empty-state" id="emptyState" <?= !empty($products) ? 'style="display:none"' : '' ?>>
          <div class="empty-icon">🛍️</div>
          <h3>No Products Found</h3>
          <p><?= $selected_category !== 'all' ? 'No items in this category yet.' : 'No products available right now.' ?></p>
          <a href="categories.php">← Browse Categories</a>
        </div>
      </div>
    </main>
  </div>

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
          <li><a href="products.php">All Products</a></li>
          <?php foreach ($all_categories as $cat): ?>
            <li><a href="products.php?cat=<?= urlencode($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="#">Track Order</a></li>
          <li><a href="#">Returns &amp; Exchange</a></li>
          <li><a href="#">Contact Us</a></li>
          <li><a href="#">FAQs</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Newsletter</h4>
        <p style="font-size:0.83rem;margin-bottom:16px;line-height:1.7">Get style tips &amp; exclusive deals straight to your inbox.</p>
        <div class="newsletter-form">
          <input type="email" placeholder="your@email.com"/>
          <button>→</button>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 Virtue &amp; Verve. All rights reserved.</p>
      <div class="footer-bottom-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
      </div>
    </div>
  </footer>

  <script>
    // Custom cursor
    const cursor = document.getElementById('cursor');
    const ring   = document.getElementById('cursorRing');
    document.addEventListener('mousemove', e => {
      cursor.style.left = e.clientX + 'px'; cursor.style.top = e.clientY + 'px';
      setTimeout(() => { ring.style.left = e.clientX + 'px'; ring.style.top = e.clientY + 'px'; }, 80);
    });
    document.querySelectorAll('a, button, input').forEach(el => {
      el.addEventListener('mouseenter', () => { cursor.style.transform = 'translate(-50%,-50%) scale(1.8)'; cursor.style.background = 'var(--gold)'; });
      el.addEventListener('mouseleave', () => { cursor.style.transform = 'translate(-50%,-50%) scale(1)';   cursor.style.background = 'var(--primary)'; });
    });

    // Mobile nav
    document.getElementById('hamburger').addEventListener('click', () => document.getElementById('mobileNav').classList.add('open'));
    document.getElementById('closeNav').addEventListener('click',  () => document.getElementById('mobileNav').classList.remove('open'));

    // Live search filter
    const searchInput    = document.getElementById('catalogSearch');
    const cards          = document.querySelectorAll('.product-card');
    const countDisplay   = document.getElementById('displayedCount');
    const emptyState     = document.getElementById('emptyState');

    searchInput.addEventListener('input', function () {
      const term = this.value.toLowerCase().trim();
      let visible = 0;

      cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const desc = card.getAttribute('data-desc') || '';
        const match = name.includes(term) || desc.includes(term);
        card.style.display = match ? 'block' : 'none';
        if (match) visible++;
      });

      countDisplay.innerText = visible;
      emptyState.style.display = visible === 0 ? 'block' : 'none';
    });
  </script>
</body>
</html>