<?php
// 1. Core security and database dependencies
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

// 2. Process Filters (?filter=all, active, hidden, nav)
$filter = $_GET['filter'] ?? 'all';
$where_clauses = ["1=1"];

if ($filter === 'active') {
  $where_clauses[] = "status = 'active'";
} elseif ($filter === 'hidden') {
  $where_clauses[] = "status = 'hidden'";
} elseif ($filter === 'nav') {
  $where_clauses[] = "show_in_nav = 1";
}
$where_sql = " WHERE " . implode(" AND ", $where_clauses);

// 3. Process Sorting (?sort=default, name, products-desc, products-asc, sales-desc)
$sort = $_GET['sort'] ?? 'default';
$order_sql = " ORDER BY id DESC"; // Default fallback

if ($sort === 'name') {
  $order_sql = " ORDER BY name ASC";
}

// 4. Run KPI Metric Queries for Top Row Widgets
$total_all = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'] ?? 0;
$total_active = $conn->query("SELECT COUNT(*) as total FROM categories WHERE status='active'")->fetch_assoc()['total'] ?? 0;
$total_hidden = $conn->query("SELECT COUNT(*) as total FROM categories WHERE status='hidden'")->fetch_assoc()['total'] ?? 0;
$total_nav = $conn->query("SELECT COUNT(*) as total FROM categories WHERE show_in_nav=1")->fetch_assoc()['total'] ?? 0;

// Gather sample count metrics (or link directly to your live product summaries)
$total_products_query = $conn->query("SELECT COUNT(*) as total FROM products");
$total_products = $total_products_query ? $total_products_query->fetch_assoc()['total'] : 0;

// 5. Fetch Active Filter Dataset
$sql = "SELECT * FROM categories" . $where_sql . $order_sql;
$result = $conn->query($sql);
$showing_count = $result ? $result->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>All Categories – Virtue & Verve Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="./css/admin-categories.css">
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
      <a class="nav-item active" href="./categories.php"><span class="nav-icon"><i class="fa-solid fa-layer-group"></i></span><span class="nav-label">Categories</span></a>
      <a class="nav-item" href="./products.php"><span class="nav-icon"><i class="fa-solid fa-box"></i></span><span class="nav-label">Products</span></a>
      <a class="nav-item" href="./orders.php"><span class="nav-icon"><i class="fa-solid fa-cart-shopping"></i></span><span class="nav-label">Orders</span></a>
      <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-people-group"></i></span><span class="nav-label">Customers</span></a>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-section-label">Store</div>
      <a class="nav-item" href="../shop.php" target="_blank"><span class="nav-icon"><i class="fa-solid fa-store"></i></span><span class="nav-label">View Store</span></a>
      <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-star"></i></span><span class="nav-label">Reviews</span></a>
      <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-gear"></i></span><span class="nav-label">Settings</span></a>
    </div>

    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="admin-avatar">VV</div>
        <div>
          <div class="admin-name">Virtue &amp; Verve</div>
          <div class="admin-role">Store Administrator</div>
        </div>
      </div>
    </div>
  </aside>

  <div class="main">

    <header class="topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle">☰</button>
        <h1 class="page-heading">All <span>Categories</span></h1>
      </div>
      <div class="topbar-right">
        <div class="search-bar">
          <span style="color:var(--light);font-size:0.9rem"><i class="fa-solid fa-magnifying-glass"></i></span>
          <input type="text" id="topSearch" placeholder="Search categories…" />
        </div>
        <div class="icon-btn" title="Notifications"><span><i class="fa-solid fa-bell" style="color: #088178;"></i></span></div>
        <button class="add-btn" id="openAddModal">+ Add Category</button>
      </div>
    </header>

    <div class="content">

      <div class="kpi-row">
        <div class="kpi-mini" style="animation-delay:0.05s">
          <div class="kpi-mini-icon" style="background: #e6f5f4"><i class="fa-solid fa-folder-open"></i></div>
          <div class="kpi-mini-info">
            <div class="kpi-mini-val"><?php echo $total_all; ?></div>
            <div class="kpi-mini-label">Total Categories</div>
          </div>
        </div>
        <div class="kpi-mini" style="animation-delay:0.1s">
          <div class="kpi-mini-icon" style="background: #e6f5f4"><i class="fa-solid fa-check"></i></div>
          <div class="kpi-mini-info">
            <div class="kpi-mini-val"><?php echo $total_active; ?></div>
            <div class="kpi-mini-label">Active Categories</div>
          </div>
        </div>
        <div class="kpi-mini" style="animation-delay:0.15s">
          <div class="kpi-mini-icon" style="background: #e6f5f4"><i class="fa-solid fa-box"></i></div>
          <div class="kpi-mini-info">
            <div class="kpi-mini-val"><?php echo $total_products; ?></div>
            <div class="kpi-mini-label">Total Products</div>
          </div>
        </div>
        <div class="kpi-mini" style="animation-delay:0.2s">
          <div class="kpi-mini-icon" style="background: #e6f5f4"><i class="fa-solid fa-eye"></i></div>
          <div class="kpi-mini-info">
            <div class="kpi-mini-val"><?php echo $total_hidden; ?></div>
            <div class="kpi-mini-label">Hidden</div>
          </div>
        </div>
      </div>

      <div class="categories-toolbar">
        <div class="toolbar-left">
          <a href="categories.php?filter=all&sort=<?php echo $sort; ?>" class="filter-pill <?php echo $filter === 'all' ? 'active' : ''; ?>">All <span class="pill-count">(<?php echo $total_all; ?>)</span></a>
          <a href="categories.php?filter=active&sort=<?php echo $sort; ?>" class="filter-pill <?php echo $filter === 'active' ? 'active' : ''; ?>">Active <span class="pill-count">(<?php echo $total_active; ?>)</span></a>
          <a href="categories.php?filter=hidden&sort=<?php echo $sort; ?>" class="filter-pill <?php echo $filter === 'hidden' ? 'active' : ''; ?>">Hidden <span class="pill-count">(<?php echo $total_hidden; ?>)</span></a>
          <a href="categories.php?filter=nav&sort=<?php echo $sort; ?>" class="filter-pill <?php echo $filter === 'nav' ? 'active' : ''; ?>">In Nav <span class="pill-count">(<?php echo $total_nav; ?>)</span></a>
        </div>
        <div class="toolbar-right">
          <select class="sort-select" id="sortSelect" onchange="location = 'categories.php?filter=<?php echo $filter; ?>&sort=' + this.value;">
            <option value="default" <?php echo $sort === 'default' ? 'selected' : ''; ?>>Sort: Default</option>
            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name A–Z</option>
          </select>
          <div class="view-toggle">
            <button class="vbtn active" id="tableViewBtn" title="Table view">☰</button>
            <button class="vbtn" id="gridViewBtn" title="Grid view">⊞</button>
          </div>
        </div>
      </div>

      <div class="categories-table-wrap" id="tableWrap">
        <table class="categories-table">
          <thead>
            <tr>
              <th class="th-check">
                <div class="custom-cb" id="selectAll" title="Select all">✓</div>
              </th>
              <th>Category</th>
              <th>Type</th>
              <th>Products</th>
              <th>Status</th>
              <th>Navigation</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): $i = 0; ?>
              <?php while ($row = $result->fetch_assoc()): $i++; ?>
                <tr style="animation-delay: <?php echo $i * 0.03; ?>s">
                  <td>
                    <div class="custom-cb">✓</div>
                  </td>
                  <td>
                    <div class="cat-cell">
                      <div class="cat-thumb-icon" style="background:var(--primary-faint); padding:0; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                        <?php if (!empty($row['image']) && file_exists('./uploads/categories/' . $row['image'])): ?>
                          <img src="./uploads/categories/<?php echo $row['image']; ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                          👜
                        <?php endif; ?>
                      </div>
                      <div class="cat-info">
                        <div class="cat-name"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="cat-slug">/categories/<?php echo htmlspecialchars($row['slug']); ?></div>
                      </div>
                    </div>
                  </td>
                  <td><span class="type-pill"><?php echo htmlspecialchars($row['type'] ?? 'Everyday'); ?></span></td>
                  <td>
                    <div class="count-cell">
                      <div class="count-num">0 products</div>
                      <div class="count-bar-track">
                        <div class="count-bar-fill" style="width:0%"></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="status-pill <?php echo $row['status'] === 'hidden' ? 'pill-hidden' : 'pill-active'; ?>">
                      <span class="sdot"></span><?php echo ucfirst($row['status']); ?>
                    </span>
                  </td>
                  <td style="font-size:0.8rem; color:<?php echo $row['show_in_nav'] ? 'var(--primary)' : 'var(--light)'; ?>">
                    <?php echo $row['show_in_nav'] ? '✓ In Menu' : '— Hidden'; ?>
                  </td>
                  <td>
                    <div class="row-actions">
                      <button type="button" class="row-btn edit-trigger-btn" title="Edit"
                        data-id="<?php echo $row['id']; ?>"
                        data-name="<?php echo htmlspecialchars($row['name']); ?>"
                        data-slug="<?php echo htmlspecialchars($row['slug']); ?>"
                        data-type="<?php echo htmlspecialchars($row['type'] ?? 'Everyday'); ?>"
                        data-desc="<?php echo htmlspecialchars($row['description'] ?? ''); ?>"
                        data-nav="<?php echo $row['show_in_nav']; ?>"
                        data-status="<?php echo $row['status']; ?>"
                        data-image="<?php echo htmlspecialchars($row['image'] ?? ''); ?>" style="background:none; border:none; cursor:pointer;"><i class="fa-solid fa-pen"></i></button>
                      <a href="./backend/category_delete.php?id=<?php echo $row['id']; ?>" class="row-btn del" title="Delete" onclick="return confirm('Are you sure you want to delete this category?');"><i class="fa-solid fa-trash"></i></a>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7">
                  <div class="empty-state" style="display:block">
                    <div class="empty-icon">🔍</div>
                    <h3 class="empty-title">No <em>categories</em> found</h3>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="categories-grid-view" id="gridWrap">
        <?php if ($showing_count > 0): mysqli_data_seek($result, 0);
          $i = 0; ?>
          <?php while ($row = $result->fetch_assoc()): $i++; ?>
            <div class="cat-grid-card" style="animation-delay: <?php echo $i * 0.04; ?>s">
              <div class="cat-grid-img">
                <div class="cat-grid-img-icon" style="background:var(--primary-faint); padding:0; overflow:hidden; display:flex; align-items:center; justify-content:center; width:100%; height:100%;">
                  <?php if (!empty($row['image']) && file_exists('./uploads/categories/' . $row['image'])): ?>
                    <img src="./uploads/categories/<?php echo $row['image']; ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                  <?php else: ?>
                    <span style="font-size: 2rem;">👜</span>
                  <?php endif; ?>
                </div>
                <div class="cat-grid-status">
                  <span class="status-pill <?php echo $row['status'] === 'hidden' ? 'pill-hidden' : 'pill-active'; ?>">
                    <span class="sdot"></span><?php echo ucfirst($row['status']); ?>
                  </span>
                </div>
                <div class="cat-grid-acts">
                  <button type="button" class="cat-grid-act-btn edit-trigger-btn" title="Edit"
                    data-id="<?php echo $row['id']; ?>"
                    data-name="<?php echo htmlspecialchars($row['name']); ?>"
                    data-slug="<?php echo htmlspecialchars($row['slug']); ?>"
                    data-type="<?php echo htmlspecialchars($row['type'] ?? 'Everyday'); ?>"
                    data-desc="<?php echo htmlspecialchars($row['description'] ?? ''); ?>"
                    data-nav="<?php echo $row['show_in_nav']; ?>"
                    data-status="<?php echo $row['status']; ?>"
                    data-image="<?php echo htmlspecialchars($row['image'] ?? ''); ?>"><i class="fa-solid fa-pen"></i></button>
                  <a href="./backend/category_delete.php?id=<?php echo $row['id']; ?>" class="cat-grid-act-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this category?');" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;"><i class="fa-solid fa-trash"></i></a>
                </div>
              </div>
              <div class="cat-grid-info">
                <div class="cat-grid-type"><?php echo htmlspecialchars($row['type'] ?? 'Everyday'); ?></div>
                <div class="cat-grid-name">👜 <?php echo htmlspecialchars($row['name']); ?></div>
                <div class="cat-grid-row">
                  <span class="cat-grid-count">0 products</span>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>

      <div class="pagination-bar" id="paginationBar">
        <div class="page-info">Showing <strong>1–<?php echo $showing_count; ?></strong> of <strong><?php echo $showing_count; ?></strong> categories</div>
      </div>

    </div>
  </div>
  <div class="modal-overlay" id="addModalOverlay">
    <div class="modal">
      <form action="./backend/category_save.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="categoryId" value="">
        <input type="hidden" name="existing_image" id="existingImage" value="">

        <div class="modal-head">
          <h2 class="modal-title" id="modalTitle">Add New <em>Category</em></h2>
          <button type="button" class="modal-close" id="closeAddModal">✕</button>
        </div>
        <div class="modal-body">
          <div class="modal-grid">

            <div class="field-group modal-full">
              <label class="field-label">Category Name</label>
              <input class="field-input" name="name" id="newName" type="text" placeholder="e.g. Evening Bags" required />
            </div>

            <div class="field-group">
              <label class="field-label">Slug / URL</label>
              <input class="field-input" name="slug" id="newSlug" type="text" placeholder="evening-bags" />
            </div>

            <div class="field-group">
              <label class="field-label">Type / Label</label>
              <select class="field-select" name="type" id="newType">
                <option value="Everyday">Everyday</option>
                <option value="Occasion">Special Occasion</option>
                <option value="Luxury">Luxury</option>
                <option value="Work">Work & Travel</option>
                <option value="Sale">Sale</option>
              </select>
            </div>

            <div class="field-group modal-full">
              <label class="field-label">Category Image / Thumbnail</label>
              <input class="field-input" name="image" id="newImage" type="file" accept="image/*" style="padding: 8px; font-family: var(--font-sans);" />
              <small style="color: var(--light); font-size: 0.75rem; display: block; margin-top: 4px;">Supported: JPG, JPEG, PNG, WEBP. Leave blank to keep existing image when editing.</small>
            </div>

            <div class="field-group modal-full">
              <label class="field-label">Description</label>
              <textarea class="field-textarea" name="description" id="newDesc" placeholder="Write a short category description…"></textarea>
            </div>

            <div class="field-group">
              <label class="field-label">Status Visibility</label>
              <select class="field-select" name="status" id="newStatus">
                <option value="active">Active — Visible immediately</option>
                <option value="hidden">Hidden — Private draft</option>
              </select>
            </div>

            <div class="field-group">
              <label class="field-label">Show in Navigation</label>
              <select class="field-select" name="show_in_nav" id="newNav">
                <option value="1">Yes — Show in menu</option>
                <option value="0">No — Hide from menu</option>
              </select>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-cancel" id="cancelAdd">Cancel</button>
          <button type="submit" class="btn-save">Save Category</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // ─── MINIMAL UI INTERACTION JAVASCRIPT ───────────────────────────────────────

    // View Layout Toggling Engine
    document.getElementById('tableViewBtn').addEventListener('click', () => {
      document.getElementById('tableViewBtn').classList.add('active');
      document.getElementById('gridViewBtn').classList.remove('active');
      document.getElementById('tableWrap').style.display = "block";
      document.getElementById('gridWrap').style.display = "none";
    });
    document.getElementById('gridViewBtn').addEventListener('click', () => {
      document.getElementById('gridViewBtn').classList.add('active');
      document.getElementById('tableViewBtn').classList.remove('active');
      document.getElementById('tableWrap').style.display = "none";
      document.getElementById('gridWrap').style.display = "grid";
    });

    // Modal Open / Reset Handlers
    const modal = document.getElementById('addModalOverlay');
    document.getElementById('openAddModal').addEventListener('click', () => {
      document.getElementById('categoryId').value = "";
      document.getElementById('newName').value = "";
      document.getElementById('newSlug').value = "";
      document.getElementById('newDesc').value = "";
      document.getElementById('newType').value = "Everyday";
      document.getElementById('newStatus').value = "active";
      document.getElementById('newNav').value = "1";
      document.getElementById('modalTitle').innerHTML = "Add New <em>Category</em>";
      modal.classList.add('open');
      document.getElementById('dimOverlay').classList.add('visible');
    });

    // Modal Close Handlers
    const closeModal = () => {
      modal.classList.remove('open');
      document.getElementById('dimOverlay').classList.remove('visible');
    };
    document.getElementById('closeAddModal').addEventListener('click', closeModal);
    document.getElementById('cancelAdd').addEventListener('click', closeModal);
    document.getElementById('dimOverlay').addEventListener('click', closeModal);

    // Map Edit Data Values straight to Form Fields upon Row/Card Click Actions
    // Find this block near the bottom of your file
    document.querySelectorAll('.edit-trigger-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('categoryId').value = btn.dataset.id;
        document.getElementById('newName').value = btn.dataset.name;
        document.getElementById('newSlug').value = btn.dataset.slug;
        document.getElementById('newDesc').value = btn.dataset.desc;
        document.getElementById('newType').value = btn.dataset.type;
        document.getElementById('newStatus').value = btn.dataset.status;
        document.getElementById('newNav').value = btn.dataset.nav;

        document.getElementById('existingImage').value = btn.dataset.image || ""; // ADD THIS LINE

        document.getElementById('modalTitle').innerHTML = "Edit <em>Category</em>";
        modal.classList.add('open');
        document.getElementById('dimOverlay').classList.add('visible');
      });
    });

    // Auto-generate safe lowercase slug string dynamically from user name inputs
    document.getElementById('newName').addEventListener('input', function() {
      const slugField = document.getElementById('newSlug');
      slugField.value = this.value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '');
    });

    // Sidebar Drawer Toggle for Small/Mobile Displays
    document.getElementById('menuToggle').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('open');
      document.getElementById('dimOverlay').classList.toggle('visible');
    });

    // Luxury Cursor Trace Following Effects
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
  </script>
</body>

</html>