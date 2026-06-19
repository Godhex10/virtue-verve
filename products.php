<?php
// Initialize database connection and start active session
include './includes/db.php'; // Change this to your actual DB connection/config file

// 1. Capture and sanitize URL filtering parameters
$selected_category = isset($_GET['cat']) ? trim($_GET['cat']) : 'all';
$sort_order = isset($_GET['sort']) ? trim($_GET['sort']) : 'default';

// 2. Formulate the dynamic SQL query with prepared statement parameters
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";

$params = [];
$types = "";

if ($selected_category !== 'all') {
    $query .= " AND c.slug = ?";
    $params[] = $selected_category;
    $types .= "s";
}

// Apply chosen sort criteria
switch ($sort_order) {
    case 'price-asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price-desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name-asc':
        $query .= " ORDER BY p.name ASC";
        break;
    case 'new':
        $query .= " ORDER BY p.created_at DESC";
        break;
    default:
        // FIX: Removed 'p.featured' since it does not exist in your schema
        $query .= " ORDER BY p.id DESC";
        break;
}

// Execute secure prepared statement
$stmt = $conn->prepare($query);

// Diagnostic Check: If the query fails due to any other database typos, this will tell you exactly what column is broken
if ($stmt === false) {
    die("<div style='padding: 20px; background: #fff5f5; color: #c53030; border-left: 4px solid #e53e3e; margin: 20px; font-family: sans-serif;'>" .
        "<h3>Database Query Error</h3>" .
        "<p>" . htmlspecialchars($conn->error) . "</p>" .
        "</div>");
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all categories dynamically for the sidebar
$all_categories = [];
$cat_sidebar_query = "SELECT c.id, c.name, c.slug FROM categories c ORDER BY c.name ASC";
$cat_sidebar_res = $conn->query($cat_sidebar_query);
if ($cat_sidebar_res) {
    while ($row = $cat_sidebar_res->fetch_assoc()) {
        $all_categories[] = $row;
    }
}

// Fetch item tallies for categories to update counts automatically
$count_query = "SELECT c.slug, COUNT(p.id) as total 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' 
                GROUP BY c.id";
$counts_result = $conn->query($count_query);
$category_counts = [];
$total_active_bags = 0;

if ($counts_result) {
    while ($row = $counts_result->fetch_assoc()) {
        $category_counts[$row['slug']] = $row['total'];
        $total_active_bags += $row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Shop Our Collection – Virtue & Verve</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
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
    .cart-btn {
      position: relative; background: none; border: none;
      cursor: pointer; font-size: 1.3rem; color: var(--dark);
      transition: color 0.2s;
    }
    .cart-btn:hover { color: var(--primary); }
    .cart-count {
      position: absolute; top: -6px; right: -8px;
      background: var(--primary); color: white;
      font-size: 0.6rem; font-weight: 600;
      width: 18px; height: 18px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
    }
    .hamburger { display: flex; flex-direction: column; gap: 5px; cursor: pointer; }
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
      padding: 140px 5% 70px;
      background: linear-gradient(135deg, #f0faf9 0%, var(--cream) 60%, #fff9f0 100%);
      position: relative; overflow: hidden;
      text-align: center;
    }
    .hero-breadcrumb {
      display: flex; align-items: center; justify-content: center;
      gap: 8px; font-size: 0.75rem; color: var(--light);
      text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px;
    }
    .hero-breadcrumb a { color: var(--primary); text-decoration: none; }
    .hero-breadcrumb a:hover { text-decoration: underline; }
    .hero-breadcrumb span { color: var(--light); }
    .page-hero h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(2.8rem, 6vw, 5rem); font-weight: 300;
      color: var(--dark); margin-bottom: 16px;
    }
    .page-hero h1 em { font-style: italic; color: var(--primary); }
    .page-hero p { color: var(--mid); font-size: 1rem; max-width: 480px; margin: 0 auto; }

    /* ── SEARCH INPUT COMPONENT ── */
    .search-wrapper {
      position: relative;
      margin-bottom: 24px;
    }
    .search-bar-input {
      width: 100%;
      padding: 12px 16px 12px 42px;
      border: 1.5px solid var(--border);
      border-radius: 4px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.88rem;
      color: var(--dark);
      background: var(--white);
      outline: none;
      transition: border-color 0.3s, box-shadow 0.3s;
    }
    .search-bar-input:focus {
      border-color: var(--primary);
      box-shadow: 0 4px 12px rgba(8, 129, 120, 0.05);
    }
    .search-icon-svg {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--light);
      pointer-events: none;
    }

    /* ── SHOP LAYOUT ── */
    .shop-layout {
      display: grid; grid-template-columns: 260px 1fr;
      gap: 40px; padding: 60px 5% 100px; max-width: 1400px; margin: 0 auto;
    }
    .sidebar { position: sticky; top: 90px; height: fit-content; }
    .filter-section { margin-bottom: 36px; }
    .filter-title {
      font-size: 0.72rem; font-weight: 500;
      text-transform: uppercase; letter-spacing: 3px;
      color: var(--dark); margin-bottom: 16px;
      padding-bottom: 10px; border-bottom: 1px solid var(--border);
    }
    .category-list { display: flex; flex-direction: column; gap: 4px; }
    .cat-btn {
      display: flex; align-items: center; justify-content: space-between;
      padding: 10px 14px; border-radius: 4px; cursor: pointer;
      font-size: 0.85rem; color: var(--mid); transition: background 0.2s, color 0.2s;
      border: none; background: none; width: 100%; text-align: left;
      text-decoration: none;
    }
    .cat-btn:hover, .cat-btn.active { background: var(--primary-faint); color: var(--primary); font-weight: 500; }
    .cat-count-badge { font-size: 0.7rem; background: rgba(0,0,0,0.07); padding: 2px 8px; border-radius: 100px; color: var(--mid); }

    /* ── PRODUCTS GRID ── */
    .products-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .product-card {
      background: var(--white); border-radius: 6px; overflow: hidden;
      transition: transform 0.35s cubic-bezier(0.25,0.46,0.45,0.94), box-shadow 0.35s;
      position: relative;
    }
    .product-card:hover { transform: translateY(-8px); box-shadow: 0 20px 60px rgba(8,129,120,0.13); }
    .product-img { position: relative; overflow: hidden; aspect-ratio: 4/3; }
    .product-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.7s cubic-bezier(0.25,0.46,0.45,0.94); }
    .product-card:hover .product-img img { transform: scale(1.08); }
    .product-link { text-decoration: none; color: inherit; display: block; }
    
    .product-badge {
      position: absolute; top: 14px; left: 14px; font-size: 0.65rem; font-weight: 500;
      letter-spacing: 1.5px; text-transform: uppercase; padding: 5px 12px; border-radius: 100px; z-index: 2;
      color: white;
    }
    .badge-new { background: var(--primary); }
    .badge-sale { background: #e07b3d; }

    .product-info { padding: 18px 18px 20px; }
    .product-category { font-size: 0.68rem; color: var(--primary); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 6px; }
    .product-name { font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; font-weight: 400; color: var(--dark); margin-bottom: 4px; }
    .product-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 12px; }
    .product-price { font-size: 1rem; font-weight: 500; color: var(--primary); }
    .product-price del { font-size: 0.82rem; color: var(--light); margin-right: 4px; }
    
    .add-to-cart {
      background: var(--primary); color: white; border: none; padding: 9px 18px; border-radius: 2px;
      font-size: 0.72rem; font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase; cursor: pointer;
    }

    /* ── FOOTER ── */
    footer { background: var(--dark); color: rgba(255,255,255,0.6); padding: 70px 5% 30px; margin-top: 60px;}
    .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 50px; }
    .footer-logo { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; color: white; }
    .footer-logo span { color: var(--gold); }

    @media (max-width: 1100px) { .products-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 860px) { .shop-layout { grid-template-columns: 1fr; } }
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
      <button class="cart-btn">
         🛒 <span class="cart-count">0</span>
      </button>
      <div class="hamburger" id="hamburger">
        <span></span><span></span><span></span>
      </div>
    </div>
  </nav>

  <div class="page-hero">
    <div class="hero-breadcrumb">
      <a href="index.php">Home</a>
      <span>/</span>
      <a href="categories.php">Categories</a>
      <?php if ($selected_category !== 'all'): ?>
        <span>/</span>
        <span><?= htmlspecialchars(ucwords(str_replace('-', ' ', $selected_category))) ?></span>
      <?php endif; ?>
    </div>
    <h1>Our <em><?= $selected_category !== 'all' ? htmlspecialchars(ucwords(str_replace('-', ' ', $selected_category))) : 'Collection' ?></em></h1>
    <p>Discover premium bags crafted for every woman, every occasion, every story.</p>
  </div>

  <div class="shop-layout">

    <aside class="sidebar">
      
      <div class="search-wrapper">
        <svg class="search-icon-svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" id="catalogSearch" class="search-bar-input" placeholder="Search our collection..."/>
      </div>

      <div class="filter-section">
        <div class="filter-title">Categories</div>
        <div class="category-list">
          <a href="products.php?cat=all&sort=<?= urlencode($sort_order) ?>" class="cat-btn <?= $selected_category === 'all' ? 'active' : '' ?>">
            All Bags <span class="cat-count-badge"><?= $total_active_bags ?></span>
          </a>
          <a href="products.php?cat=handbag&sort=<?= urlencode($sort_order) ?>" class="cat-btn <?= $selected_category === 'handbag' ? 'active' : '' ?>">
            Handbags <span class="cat-count-badge"><?= $category_counts['handbag'] ?? 0 ?></span>
          </a>
          <a href="products.php?cat=tote&sort=<?= urlencode($sort_order) ?>" class="cat-btn <?= $selected_category === 'tote' ? 'active' : '' ?>">
            Tote Bags <span class="cat-count-badge"><?= $category_counts['tote'] ?? 0 ?></span>
          </a>
          <a href="products.php?cat=clutch&sort=<?= urlencode($sort_order) ?>" class="cat-btn <?= $selected_category === 'clutch' ? 'active' : '' ?>">
            Clutches <span class="cat-count-badge"><?= $category_counts['clutch'] ?? 0 ?></span>
          </a>
          <a href="products.php?cat=backpack&sort=<?= urlencode($sort_order) ?>" class="cat-btn <?= $selected_category === 'backpack' ? 'active' : '' ?>">
            Backpacks <span class="cat-count-badge"><?= $category_counts['backpack'] ?? 0 ?></span>
          </a>
        </div>
      </div>
    </aside>

    <main class="shop-main">
      <div class="shop-toolbar">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 16px;">
          <p class="result-count">Showing <strong id="displayedCount"><?= count($products) ?></strong> items</p>
          
          <select class="sort-select" id="sortSelect" onchange="location = this.value;">
            <option value="products.php?cat=<?= urlencode($selected_category) ?>&sort=default" <?= $sort_order === 'default' ? 'selected' : '' ?>>Sort: Featured</option>
            <option value="products.php?cat=<?= urlencode($selected_category) ?>&sort=price-asc" <?= $sort_order === 'price-asc' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="products.php?cat=<?= urlencode($selected_category) ?>&sort=price-desc" <?= $sort_order === 'price-desc' ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="products.php?cat=<?= urlencode($selected_category) ?>&sort=name-asc" <?= $sort_order === 'name-asc' ? 'selected' : '' ?>>Name: A–Z</option>
            <option value="products.php?cat=<?= urlencode($selected_category) ?>&sort=new" <?= $sort_order === 'new' ? 'selected' : '' ?>>Newest First</option>
          </select>
        </div>
      </div>

      <div class="products-grid" id="productsGrid">
        <?php if (!empty($products)): ?>
          <?php foreach ($products as $product): ?>
            <div class="product-card" data-name="<?= htmlspecialchars(strtolower($product['name'])) ?>" data-desc="<?= htmlspecialchars(strtolower($product['description'] ?? '')) ?>">
              <a href="product-details.php?slug=<?= urlencode($product['slug']) ?>" class="product-link">
                <div class="product-img">
                  <img src="admin/uploads/products/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>"/>
                  <?php if (!empty($product['badge'])): ?>
                    <span class="product-badge badge-<?= htmlspecialchars($product['badge']) ?>"><?= htmlspecialchars($product['badge']) ?></span>
                  <?php endif; ?>
                </div>
                <div class="product-info">
                  <p class="product-category"><?= htmlspecialchars($product['category_name']) ?></p>
                  <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                  <div class="product-footer">
                    <span class="product-price">
                      <?php if (!empty($product['old_price'])): ?>
                        <del>₦<?= number_format($product['old_price'], 2) ?></del>
                      <?php endif; ?>
                      ₦<?= number_format($product['price'], 2) ?>
                    </span>
                    <button class="add-to-cart">Add</button>
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="grid-column: 1/-1; text-align: center; padding: 40px var(--white); color: var(--light);">
             <p>No premium items found in this category.</p>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <footer>
    <div class="footer-grid">
      <div>
        <div class="footer-logo">Virtue &amp; <span>Verve</span></div>
        <p>Premium, stylish bags crafted for your unique style narrative.</p>
      </div>
    </div>
  </footer>

  <script>
    const cursor = document.getElementById('cursor');
    const ring = document.getElementById('cursorRing');
    document.addEventListener('mousemove', (e) => {
      cursor.style.left = e.clientX + 'px'; cursor.style.top = e.clientY + 'px';
      ring.style.left = e.clientX + 'px'; ring.style.top = e.clientY + 'px';
    });

    const searchInput = document.getElementById('catalogSearch');
    const cards = document.querySelectorAll('.product-card');
    const countDisplay = document.getElementById('displayedCount');

    searchInput.addEventListener('input', (e) => {
      const searchTerm = e.target.value.toLowerCase().trim();
      let visibleCount = 0;

      cards.forEach(card => {
        const name = card.getAttribute('data-name');
        const desc = card.getAttribute('data-desc');
        
        if (name.includes(searchTerm) || desc.includes(searchTerm)) {
          card.style.display = 'block';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });
      countDisplay.innerText = visibleCount;
    });
  </script>
</body>
</html>