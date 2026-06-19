<?php
// 1. CORE LAYOUT SEED CONFIGURATIONS & COMPLIANCE ACCESS GUARDS
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

// 2. FETCH ACTIVE CATEGORIES FOR SYSTEM DROPDOWNS AND PILL WRAPPERS
$cat_query = "SELECT * FROM categories ORDER BY name ASC";
$cat_result = $conn->query($cat_query);
$categories_list = [];
if ($cat_result && $cat_result->num_rows > 0) {
    while($c_row = $cat_result->fetch_assoc()) {
        $categories_list[] = $c_row;
    }
}

// 3. CAPTURE ACTIVE FILTER PARAMETERS FROM RECONSTRUCTED URL ROUTES
$where_clauses = [];
if (isset($_GET['cat']) && !empty($_GET['cat'])) {
    $cat_filter = $conn->real_escape_string($_GET['cat']);
    $where_clauses[] = "products.category_slug = '$cat_filter'";
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_filter = $conn->real_escape_string($_GET['search']);
    $where_clauses[] = "(products.name LIKE '%$search_filter%' OR products.sku LIKE '%$search_filter%')";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// 4. PREPARE CORE ORDER BY STRING SEQUENCING CONDITIONS
$sort = "products.id DESC"; 
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'name':       $sort = "products.name ASC"; break;
        case 'price-asc':  $sort = "products.price ASC"; break;
        case 'price-desc': $sort = "products.price DESC"; break;
        case 'stock-asc':  $sort = "products.stock ASC"; break;
        case 'new':        $sort = "products.id DESC"; break;
    }
}

// 5. FETCH THE FILTERED DATA RECORD PACKETS
$prod_query = "SELECT products.*, categories.name AS category_name 
               FROM products 
               LEFT JOIN categories ON products.category_slug = categories.slug 
               $where_sql 
               ORDER BY $sort";
$prod_result = $conn->query($prod_query);
$products_array = [];
if ($prod_result && $prod_result->num_rows > 0) {
    while ($p_row = $prod_result->fetch_assoc()) {
        $products_array[] = $p_row;
    }
}

// 6. COMPUTE COMPREHENSIVE LIVE SYNC AGGREGATE KPI METRICS
$total_products  = ($conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc())['total'] ?? 0;
$active_products = ($conn->query("SELECT COUNT(*) as active FROM products WHERE status = 'active' AND stock > 0")->fetch_assoc())['active'] ?? 0;
$low_products    = ($conn->query("SELECT COUNT(*) as low FROM products WHERE stock > 0 AND stock <= 5")->fetch_assoc())['low'] ?? 0;
$out_products    = ($conn->query("SELECT COUNT(*) as out_num FROM products WHERE stock = 0")->fetch_assoc())['out_num'] ?? 0;

// HELPER ENGINE OPERATIONS FOR DYNAMIC BADGING AND BAR CALCULATIONS
function getProductStatus($stock, $status) {
    if ($stock == 0) return ['cls' => 'pill-out', 'label' => 'Out of Stock'];
    if ($stock <= 5) return ['cls' => 'pill-low', 'label' => 'Low Stock'];
    if ($status === 'draft') return ['cls' => 'pill-draft', 'label' => 'Draft'];
    return ['cls' => 'pill-active', 'label' => 'Active'];
}
function getStockPct($stock) { return min(($stock / 70) * 100, 100); }
function getStockColor($stock) {
    if ($stock == 0) return 'var(--danger)';
    if ($stock <= 5) return 'var(--warning)';
    return 'var(--primary)';
}
function getBadgeDetails($badge) {
    $colors = ['new' => '#088178', 'sale' => '#e07b3d', 'trending' => '#c9a96e'];
    $labels = ['new' => 'New', 'sale' => 'Sale', 'trending' => 'Trending'];
    return isset($colors[$badge]) ? ['color' => $colors[$badge], 'label' => $labels[$badge]] : null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>All Products – Virtue & Verve Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./css/admin-products.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>
<div class="dim-overlay" id="dimOverlay"></div>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <a class="logo-mark" href="index.php">Virtue &amp; <span>Verve</span></a>
    <div class="logo-sub">Admin Dashboard</div>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Overview</div>
    <a class="nav-item" href="./index.php"><span class="nav-icon"><i class="fa-solid fa-grip"></i></span><span class="nav-label">Dashboard</span></a>
    <a class="nav-item" href="categories.php"><span class="nav-icon"><i class="fa-solid fa-layer-group"></i></span><span class="nav-label">Categories</span></a>
    <a class="nav-item active" href="products.php"><span class="nav-icon"><i class="fa-solid fa-box"></i></span><span class="nav-label">Products</span></a>
    <a class="nav-item" href="orders.php"><span class="nav-icon"><i class="fa-solid fa-cart-shopping"></i></span><span class="nav-label">Orders</span><span class="nav-badge red">12</span></a>
    <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-people-group"></i></span><span class="nav-label">Customers</span></a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Store</div>
    <a class="nav-item" href="../shop.php" target="_blank"><span class="nav-icon"><i class="fa-solid fa-store"></i></span><span class="nav-label">View Store</span></a>
    <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-star"></i></span><span class="nav-label">Reviews</span><span class="nav-badge">7</span></a>
    <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-gear"></i></span><span class="nav-label">Settings</span></a>
  </div>

  <div class="sidebar-footer">
    <div class="admin-profile">
      <div class="admin-avatar">VV</div>
      <div>
        <div class="admin-name">Virtue &amp; Verve</div>
        <div class="admin-role">Store Administrator</div>
      </div>
      <div class="admin-more">⋯</div>
    </div>
  </div>
</aside>

<div class="main">

  <header class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle" id="menuToggle">☰</button>
      <h1 class="page-heading">All <span>Products</span></h1>
    </div>
    <div class="topbar-right">
      <div class="search-bar">
        <span style="color:var(--light);font-size:0.9rem"><i class="fa-solid fa-magnifying-glass"></i></span>
        <input type="text" id="topSearch" placeholder="Search and hit enter…" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"/>
      </div>
      <div class="icon-btn" id="notifBtn" title="Notifications">
        <i class="fa-solid fa-bell"></i> <span class="notif-dot"></span>
      </div>
      <button class="add-btn" id="openAddModal">+ Add Product</button>
    </div>
  </header>

  <div class="content">

    <div class="kpi-row">
      <div class="kpi-mini" style="animation-delay:0.05s">
        <div class="kpi-mini-icon" style="background:var(--primary-faint)">🛍️</div>
        <div class="kpi-mini-info">
          <div class="kpi-mini-val"><?php echo $total_products; ?></div>
          <div class="kpi-mini-label">Total Products</div>
        </div>
        <div class="kpi-mini-trend trend-up">Live DB Status</div>
      </div>
      <div class="kpi-mini" style="animation-delay:0.1s">
        <div class="kpi-mini-icon" style="background:var(--success-light)">✅</div>
        <div class="kpi-mini-info">
          <div class="kpi-mini-val"><?php echo $active_products; ?></div>
          <div class="kpi-mini-label">Active Listings</div>
        </div>
        <div class="kpi-mini-trend trend-up">Visible to Shop</div>
      </div>
      <div class="kpi-mini" style="animation-delay:0.15s">
        <div class="kpi-mini-icon" style="background:var(--warning-light)">⚠️</div>
        <div class="kpi-mini-info">
          <div class="kpi-mini-val"><?php echo $low_products; ?></div>
          <div class="kpi-mini-label">Low Stock</div>
        </div>
        <div class="kpi-mini-trend trend-warn">Reorder soon</div>
      </div>
      <div class="kpi-mini" style="animation-delay:0.2s">
        <div class="kpi-mini-icon" style="background:var(--danger-light)">❌</div>
        <div class="kpi-mini-info">
          <div class="kpi-mini-val"><?php echo $out_products; ?></div>
          <div class="kpi-mini-label">Out of Stock</div>
        </div>
        <div class="kpi-mini-trend trend-down">Needs attention</div>
      </div>
    </div>

    <div class="products-toolbar">
      <div class="toolbar-left">
        <a href="products.php" class="filter-pill <?php echo !isset($_GET['cat']) ? 'active' : ''; ?>">All</a>
        <?php foreach ($categories_list as $cat): ?>
          <a href="products.php?cat=<?php echo htmlspecialchars($cat['slug']); ?>" 
             class="filter-pill <?php echo (isset($_GET['cat']) && $_GET['cat'] === $cat['slug']) ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($cat['name']); ?>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="toolbar-right">
        <select class="sort-select" id="sortSelect">
          <option value="default" <?php echo (isset($_GET['sort']) && $_GET['sort']=='default') ? 'selected':''; ?>>Sort: Default</option>
          <option value="name" <?php echo (isset($_GET['sort']) && $_GET['sort']=='name') ? 'selected':''; ?>>Name A–Z</option>
          <option value="price-asc" <?php echo (isset($_GET['sort']) && $_GET['sort']=='price-asc') ? 'selected':''; ?>>Price ↑</option>
          <option value="price-desc" <?php echo (isset($_GET['sort']) && $_GET['sort']=='price-desc') ? 'selected':''; ?>>Price ↓</option>
          <option value="stock-asc" <?php echo (isset($_GET['sort']) && $_GET['sort']=='stock-asc') ? 'selected':''; ?>>Stock ↑</option>
          <option value="new" <?php echo (isset($_GET['sort']) && $_GET['sort']=='new') ? 'selected':''; ?>>Newest First</option>
        </select>
        <div class="view-toggle">
          <button class="vbtn active" id="tableViewBtn" title="Table view">☰</button>
          <button class="vbtn" id="gridViewBtn" title="Grid view">⊞</button>
        </div>
      </div>
    </div>

    <div class="products-table-wrap" id="tableWrap">
      <?php if (empty($products_array)): ?>
        <div class="empty-state" id="emptyState">
          <div class="empty-icon">🔍</div>
          <h3 class="empty-title">No <em>products</em> found</h3>
          <p class="empty-sub">Try adjustments to your search queries or category filter tabs</p>
        </div>
      <?php else: ?>
        <table class="products-table">
          <thead>
            <tr>
              <th class="th-check"><div class="custom-cb" id="selectAll" title="Select all">✓</div></th>
              <th>Product</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Status</th>
              <th>Rating</th>
              <th>Sales</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="productsTableBody">
            <?php 
            $idx = 0;
            foreach ($products_array as $row): 
              $st = getProductStatus($row['stock'], $row['status']);
              $bd = getBadgeDetails($row['badge'] ?? '');
              $img_url = !empty($row['image']) && file_exists('./uploads/products/' . $row['image']) ? $row['image'] : 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=300&q=70';
            ?>
              <tr id="row-<?php echo $row['id']; ?>" style="animation-delay:<?php echo $idx * 0.03; ?>s">
                <td><div class="custom-cb row-cb-select" data-id="<?php echo $row['id']; ?>">✓</div></td>
                <td>
                  <div class="prod-cell">
                    <div class="prod-thumb"><img src="./uploads/products/<?php echo $img_url; ?>" alt="" loading="lazy"/></div>
                    <div class="prod-info">
                      <div class="prod-name">
                        <?php echo htmlspecialchars($row['name']); ?>
                        <?php if ($bd): ?>
                          <span style="background:<?php echo $bd['color']; ?>;color:white;font-size:0.58rem;padding:2px 8px;border-radius:100px;margin-left:6px;font-weight:600"><?php echo $bd['label']; ?></span>
                        <?php endif; ?>
                      </div>
                      <div class="prod-sku"><?php echo htmlspecialchars($row['sku'] ?? ''); ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="cat-pill"><?php echo htmlspecialchars($row['category_name'] ?? 'General'); ?></span>
                </td>
                <td class="price-cell">
                  <?php if (!empty($row['old_price']) && $row['old_price'] > 0): ?>
                    <span class="price-old">₦<?php echo number_format($row['old_price'], 0); ?></span>
                  <?php endif; ?>
                  ₦<?php echo number_format($row['price'], 0); ?>
                </td>
                <td>
                  <div class="stock-cell">
                    <div class="stock-num"><?php echo $row['stock']; ?> units</div>
                    <div class="stock-bar-track">
                      <div class="stock-bar-fill" style="width:<?php echo getStockPct($row['stock']); ?>%;background:<?php echo getStockColor($row['stock']); ?>"></div>
                    </div>
                  </div>
                </td>
                <td><span class="status-pill <?php echo $st['cls']; ?>"><span class="sdot"></span><?php echo $st['label']; ?></span></td>
                <td>
                  <div class="rating-cell">
                    <span class="stars-small">★</span>
                    <span class="rating-num"><?php echo number_format($row['rating'] ?? 0.0, 1); ?></span>
                    <span class="rating-cnt">(<?php echo $row['reviews'] ?? 0; ?>)</span>
                  </div>
                </td>
                <td style="font-weight:500;color:var(--dark)"><?php echo $row['sales'] ?? 0; ?> sold</td>
                <td>
                  <div class="row-actions">
                    <button type="button" class="row-btn edit-trigger-btn" title="Edit"
                            data-id="<?php echo $row['id']; ?>"
                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                            data-sku="<?php echo htmlspecialchars($row['sku'] ?? ''); ?>"
                            data-cat="<?php echo htmlspecialchars($row['category_slug'] ?? ''); ?>"
                            data-price="<?php echo $row['price']; ?>"
                            data-oldprice="<?php echo $row['old_price'] ?? ''; ?>"
                            data-stock="<?php echo $row['stock']; ?>"
                            data-desc="<?php echo htmlspecialchars($row['description'] ?? ''); ?>"
                            data-status="<?php echo htmlspecialchars($row['status']); ?>"
                            data-badge="<?php echo htmlspecialchars($row['badge'] ?? ''); ?>"
                            data-colors="<?php echo htmlspecialchars($row['colors'] ?? ''); ?>"
                            data-image="<?php echo htmlspecialchars($row['image'] ?? ''); ?>"
                            style="background:none; border:none; cursor:pointer;">✏️</button>
                    <div class="row-btn" title="View" onclick="showToast('Viewing on store…')">👁</div>
                    <a href="backend/product_delete.php?id=<?php echo $row['id']; ?>" class="row-btn del" title="Delete" onclick="return confirm('Delete this permanent listing?');">🗑️</a>
                  </div>
                </td>
              </tr>
            <?php 
            $idx++;
            endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="products-grid-view" id="gridWrap">
      <?php if (!empty($products_array)): ?>
        <?php 
        $gidx = 0;
        foreach ($products_array as $row): 
          $st = getProductStatus($row['stock'], $row['status']);
          $img_url = !empty($row['image']) && file_exists('../uploads/products/' . $row['image']) ? '../uploads/products/' . $row['image'] : 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=300&q=70';
        ?>
          <div class="grid-card" id="gc-<?php echo $row['id']; ?>" style="animation-delay:<?php echo $gidx * 0.04; ?>s">
            <div class="grid-img">
              <img src="<?php echo $img_url; ?>" alt="" loading="lazy"/>
              <div class="grid-status"><span class="status-pill <?php echo $st['cls']; ?>"><span class="sdot"></span><?php echo $st['label']; ?></span></div>
              <div class="grid-cb"><div class="custom-cb grid-cb-select" data-id="<?php echo $row['id']; ?>" style="background:white;border-color:var(--border)">✓</div></div>
              <div class="grid-acts">
                <button type="button" class="grid-act-btn edit-trigger-btn" title="Edit"
                        data-id="<?php echo $row['id']; ?>"
                        data-name="<?php echo htmlspecialchars($row['name']); ?>"
                        data-sku="<?php echo htmlspecialchars($row['sku'] ?? ''); ?>"
                        data-cat="<?php echo htmlspecialchars($row['category_slug'] ?? ''); ?>"
                        data-price="<?php echo $row['price']; ?>"
                        data-oldprice="<?php echo $row['old_price'] ?? ''; ?>"
                        data-stock="<?php echo $row['stock']; ?>"
                        data-desc="<?php echo htmlspecialchars($row['description'] ?? ''); ?>"
                        data-status="<?php echo htmlspecialchars($row['status']); ?>"
                        data-badge="<?php echo htmlspecialchars($row['badge'] ?? ''); ?>"
                        data-colors="<?php echo htmlspecialchars($row['colors'] ?? ''); ?>"
                        data-image="<?php echo htmlspecialchars($row['image'] ?? ''); ?>">✏️</button>
                <a href="backend/product_delete.php?id=<?php echo $row['id']; ?>" class="grid-act-btn" title="Delete" onclick="return confirm('Delete this permanent listing?');">🗑️</a>
              </div>
            </div>
            <div class="grid-info">
              <div class="grid-cat"><?php echo htmlspecialchars($row['category_name'] ?? 'General'); ?></div>
              <div class="grid-name"><?php echo htmlspecialchars($row['name']); ?></div>
              <div class="grid-row">
                <span class="grid-price">₦<?php echo number_format($row['price'], 0); ?></span>
                <span class="grid-stock" style="color:<?php echo getStockColor($row['stock']); ?>"><?php echo $row['stock']; ?> units</span>
              </div>
            </div>
          </div>
        <?php 
        $gidx++;
        endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="pagination-bar" id="paginationBar">
      <div class="page-info">Showing <strong>1–<?php echo count($products_array); ?></strong> of <strong><?php echo $total_products; ?></strong> products</div>
      <div class="page-btns">
        <button class="pg-btn nav">← Prev</button>
        <button class="pg-btn active">1</button>
        <button class="pg-btn nav">Next →</button>
      </div>
    </div>

  </div></div><div class="modal-overlay" id="addModalOverlay">
  <div class="modal">
    <form action="backend/product_save.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" id="productId" value="">
      <input type="hidden" name="existing_image" id="existingImage" value="">

      <div class="modal-head">
        <h2 class="modal-title" id="modalTitleText">Add New <em>Product</em></h2>
        <button type="button" class="modal-close" id="closeAddModal">✕</button>
      </div>
      <div class="modal-body">
        <div class="modal-grid">
          
          <div class="field-group modal-full">
            <label class="field-label">Product Name</label>
            <input class="field-input" name="name" id="newName" type="text" placeholder="e.g. Milano Structured Bag" required/>
          </div>

          <div class="field-group">
            <label class="field-label">Product SKU Identifier</label>
            <input class="field-input" name="sku" id="newSku" type="text" placeholder="e.g. VV-HB-102"/>
          </div>

          <div class="field-group">
            <label class="field-label">Category Assignment</label>
            <select class="field-select" name="category_slug" id="newCat">
              <?php foreach($categories_list as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat['slug']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field-group">
            <label class="field-label">Price (₦)</label>
            <input class="field-input" name="price" id="newPrice" type="number" placeholder="45000" required/>
          </div>

          <div class="field-group">
            <label class="field-label">Original / Old Price (₦)</label>
            <input class="field-input" name="old_price" id="newOldPrice" type="number" placeholder="Leave blank if no markdown disc."/>
          </div>

          <div class="field-group">
            <label class="field-label">Stock Quantity Count</label>
            <input class="field-input" name="stock" id="newStock" type="number" placeholder="50" required/>
          </div>

          <div class="field-group modal-full">
            <label class="field-label">Available Product Color Schemes</label>
            <div class="color-picker-wrapper" style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
              <input type="color" id="colorPickerInput" style="border: 1px solid var(--border); background: none; width: 50px; height: 40px; padding: 0; border-radius: 6px; cursor: pointer; box-sizing: border-box;" value="#c9a96e">
              <button type="button" id="addColorBtn" class="add-btn" style="padding: 0 16px; height: 40px; font-size: 0.85rem; border-radius: 6px; font-weight: 500; margin: 0;">+ Add Selected Color</button>
            </div>
            
            <input type="hidden" name="colors" id="newColors" value="">
            
            <div id="colorSwatchesContainer" style="display: flex; flex-wrap: wrap; gap: 10px; min-height: 44px; padding: 8px 12px; border: 1px dashed var(--border); border-radius: 6px; align-items: center; background: rgba(255,255,255,0.02); box-sizing: border-box;"></div>
          </div>

          <div class="field-group modal-full">
            <label class="field-label">Product Gallery Photos (Select one or multiple angles/colors)</label>
            <input class="field-input" name="images[]" id="newImages" type="file" accept="image/*" multiple style="padding: 8px; font-family: var(--font-sans);"/>
            <small style="color: var(--light); font-size: 0.75rem; display: block; margin-top: 4px;">Supported: JPG, JPEG, PNG, WEBP. You can highlight and upload multiple variants at once.</small>
          </div>

          <div class="field-group modal-full">
            <label class="field-label">Detailed Content Description</label>
            <textarea class="field-textarea" name="description" id="newDesc" placeholder="Write a short product description…"></textarea>
          </div>

          <div class="field-group">
            <label class="field-label">Inventory Status Mode</label>
            <select class="field-select" name="status" id="newStatus">
              <option value="active">Active — Publish immediately to storefront</option>
              <option value="draft">Draft — Private catalog asset storage</option>
            </select>
          </div>

          <div class="field-group">
            <label class="field-label">Marketing Accent Badge</label>
            <select class="field-select" name="badge" id="newBadge">
              <option value="">None — No badge overlays</option>
              <option value="new">New — Arrival label tag</option>
              <option value="sale">Sale — Discount highlight label</option>
              <option value="trending">Trending — Popular selection label</option>
            </select>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" id="cancelAdd">Cancel</button>
        <button type="submit" class="btn-save">Save Product Details</button>
      </div>
    </form>
  </div>
</div>

<div class="toast" id="toast"><span>✨</span><span id="toastMsg">Done!</span></div>

<script>
const modal = document.getElementById('addModalOverlay');
const dimOverlay = document.getElementById('dimOverlay');

// COLOR PICKER STATE CONFIGURATIONS
const colorPickerInput = document.getElementById('colorPickerInput');
const addColorBtn = document.getElementById('addColorBtn');
const hiddenColorsInput = document.getElementById('newColors');
const colorSwatchesContainer = document.getElementById('colorSwatchesContainer');
let selectedColorsArray = [];

function renderColorSwatches() {
  colorSwatchesContainer.innerHTML = '';
  if (selectedColorsArray.length === 0) {
    colorSwatchesContainer.innerHTML = '<span style="color: var(--light); font-size: 0.8rem; font-style: italic; padding-left: 2px;">No color variants added yet.</span>';
    hiddenColorsInput.value = '';
    return;
  }
  
  selectedColorsArray.forEach((hex, index) => {
    const swatch = document.createElement('div');
    swatch.style.position = 'relative';
    swatch.style.width = '28px';
    swatch.style.height = '28px';
    swatch.style.borderRadius = '50%';
    swatch.style.backgroundColor = hex;
    swatch.style.border = '2px solid rgba(255,255,255,0.8)';
    swatch.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    swatch.style.cursor = 'default';
    swatch.title = hex;
    
    const removeBadge = document.createElement('span');
    removeBadge.textContent = '✕';
    removeBadge.style.position = 'absolute';
    removeBadge.style.top = '-5px';
    removeBadge.style.right = '-5px';
    removeBadge.style.width = '14px';
    removeBadge.style.height = '14px';
    removeBadge.style.background = 'var(--danger)';
    removeBadge.style.color = 'white';
    removeBadge.style.fontSize = '8px';
    removeBadge.style.fontWeight = 'bold';
    removeBadge.style.borderRadius = '50%';
    removeBadge.style.display = 'flex';
    removeBadge.style.alignItems = 'center';
    removeBadge.style.justifyContent = 'center';
    removeBadge.style.border = '1px solid white';
    removeBadge.style.cursor = 'pointer';
    
    removeBadge.addEventListener('click', (e) => {
      e.stopPropagation();
      selectedColorsArray.splice(index, 1);
      updateColorsState();
    });
    
    swatch.appendChild(removeBadge);
    colorSwatchesContainer.appendChild(swatch);
  });
}

function updateColorsState() {
  hiddenColorsInput.value = selectedColorsArray.join(',');
  renderColorSwatches();
}

addColorBtn.addEventListener('click', () => {
  const hexValue = colorPickerInput.value.toLowerCase();
  if (!selectedColorsArray.includes(hexValue)) {
    selectedColorsArray.push(hexValue);
    updateColorsState();
  } else {
    showToast('Color already exists in this configuration array');
  }
});

// RESET AND EXPAND OPEN TRIGGER LOGIC ON NEW ENTRY
document.getElementById('openAddModal').addEventListener('click', () => {
  document.getElementById('productId').value = "";
  document.getElementById('existingImage').value = "";
  document.getElementById('newName').value = "";
  document.getElementById('newSku').value = "";
  document.getElementById('newPrice').value = "";
  document.getElementById('newOldPrice').value = "";
  document.getElementById('newStock').value = "";
  document.getElementById('newDesc').value = "";
  document.getElementById('newStatus').value = "active";
  document.getElementById('newBadge').value = "";
  document.getElementById('newImages').value = "";
  
  selectedColorsArray = [];
  updateColorsState();
  
  document.getElementById('modalTitleText').innerHTML = "Add New <em>Product</em>";
  modal.classList.add('open');
  dimOverlay.classList.add('visible');
});

// DISPATCH DATA PARSING RETRIEVAL ON EDITS
document.querySelectorAll('.edit-trigger-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('productId').value = btn.dataset.id;
    document.getElementById('newName').value = btn.dataset.name;
    document.getElementById('newSku').value = btn.dataset.sku;
    document.getElementById('newCat').value = btn.dataset.cat;
    document.getElementById('newPrice').value = btn.dataset.price;
    document.getElementById('newOldPrice').value = btn.dataset.oldprice;
    document.getElementById('newStock').value = btn.dataset.stock;
    document.getElementById('newDesc').value = btn.dataset.desc;
    document.getElementById('newStatus').value = btn.dataset.status;
    document.getElementById('newBadge').value = btn.dataset.badge;
    document.getElementById('existingImage').value = btn.dataset.image || "";
    
    // Parse the comma-separated hex codes string back into the array state
    const colorsStr = btn.dataset.colors || "";
    if (colorsStr.trim() !== "") {
      selectedColorsArray = colorsStr.split(',').map(c => c.trim().toLowerCase());
    } else {
      selectedColorsArray = [];
    }
    updateColorsState();
    
    document.getElementById('modalTitleText').innerHTML = "Edit <em>Product Listing</em>";
    modal.classList.add('open');
    dimOverlay.classList.add('visible');
  });
});

document.getElementById('closeAddModal').addEventListener('click', closeModalEngine);
document.getElementById('cancelAdd').addEventListener('click', closeModalEngine);
dimOverlay.addEventListener('click', closeModalEngine);

function closeModalEngine() {
  modal.classList.remove('open');
  dimOverlay.classList.remove('visible');
  document.getElementById('sidebar').classList.remove('open');
}

document.getElementById('topSearch').addEventListener('keypress', e => {
  if (e.key === 'Enter') {
    const url = new URL(window.location.href);
    url.searchParams.set('search', e.target.value.trim());
    window.location.href = url.href;
  }
});

document.getElementById('sortSelect').addEventListener('change', e => {
  const url = new URL(window.location.href);
  url.searchParams.set('sort', e.target.value);
  window.location.href = url.href;
});

document.getElementById('tableViewBtn').addEventListener('click', () => {
  document.getElementById('tableViewBtn').classList.add('active');
  document.getElementById('gridViewBtn').classList.remove('active');
  document.getElementById('tableWrap').style.display = 'block';
  document.getElementById('gridWrap').style.display = 'none';
});
document.getElementById('gridViewBtn').addEventListener('click', () => {
  document.getElementById('gridViewBtn').classList.add('active');
  document.getElementById('tableViewBtn').classList.remove('active');
  document.getElementById('tableWrap').style.display = 'none';
  document.getElementById('gridWrap').style.display = 'grid';
});

const cursor = document.getElementById('cursor');
const ring   = document.getElementById('cursorRing');
document.addEventListener('mousemove', e => {
  cursor.style.left = e.clientX + 'px'; cursor.style.top = e.clientY + 'px';
  setTimeout(() => { ring.style.left = e.clientX + 'px'; ring.style.top = e.clientY + 'px'; }, 80);
});
document.querySelectorAll('a,button,.nav-item,.kpi-mini,.grid-card,.row-btn,.custom-cb').forEach(el => {
  el.addEventListener('mouseenter', () => { cursor.style.transform='translate(-50%,-50%) scale(1.8)'; cursor.style.background='var(--gold)'; });
  el.addEventListener('mouseleave', () => { cursor.style.transform='translate(-50%,-50%) scale(1)'; cursor.style.background='var(--primary)'; });
});

document.getElementById('menuToggle').addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('open');
  dimOverlay.classList.toggle('visible');
});

function showToast(msg) {
  const t = document.getElementById('toast');
  document.getElementById('toastMsg').textContent = msg;
  t.classList.add('show');
  clearTimeout(showToast._t);
  showToast._t = setTimeout(() => t.classList.remove('show'), 2800);
}

const urlParams = new URLSearchParams(window.location.search);
if(urlParams.get('status') === 'success') { showToast('✅ Inventory changes committed successfully!'); }
if(urlParams.get('status') === 'deleted') { showToast('🗑️ Product entry successfully erased.'); }
if(urlParams.get('status') === 'error') { showToast('⚠️ Error: ' + urlParams.get('message')); }
</script>
</body>
</html>