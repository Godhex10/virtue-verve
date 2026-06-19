<?php
// ── HARDCODED SAMPLE ORDERS DATA ─────────────────────────────────────────────
$orders = [
    [
        'id' => 1090,
        'oid' => '#VV-1090',
        'customer' => ['name' => 'Adaeze Okafor', 'email' => 'ada@email.com', 'av' => '👩🏾'],
        'items' => [
            ['name' => 'Milano Structured Bag', 'img' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=100&q=60', 'price' => 45000, 'qty' => 1, 'color' => 'Black']
        ],
        'amount' => 45000,
        'status' => 'delivered',
        'payment' => 'paid',
        'date' => 'Jun 14, 2026',
        'city' => 'Lagos'
    ],
    [
        'id' => 1089,
        'oid' => '#VV-1089',
        'customer' => ['name' => 'Fatima Aliyu', 'email' => 'fatima@email.com', 'av' => '👩🏽'],
        'items' => [
            ['name' => 'Canvas Weekend Tote', 'img' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=100&q=60', 'price' => 16500, 'qty' => 1, 'color' => 'Tan']
        ],
        'amount' => 16500,
        'status' => 'processing',
        'payment' => 'paid',
        'date' => 'Jun 12, 2026',
        'city' => 'Abuja'
    ],
    [
        'id' => 1088,
        'oid' => '#VV-1088',
        'customer' => ['name' => 'Blessing Eze', 'email' => 'blessing@email.com', 'av' => '👩🏿'],
        'items' => [
            ['name' => 'Velvet Night Clutch', 'img' => 'https://images.unsplash.com/photo-1575032617751-6ddec2089882?w=100&q=60', 'price' => 18000, 'qty' => 2, 'color' => 'Teal']
        ],
        'amount' => 36000,
        'status' => 'pending',
        'payment' => 'pending',
        'date' => 'Jun 10, 2026',
        'city' => 'Port Harcourt'
    ]
];

// Helper dictionaries mapping status/payment keys to their respective CSS classes
$status_cfg = [
    'delivered'  => ['cls' => 'pill-delivered',  'label' => 'Delivered'],
    'processing' => ['cls' => 'pill-processing', 'label' => 'Processing'],
    'pending'    => ['cls' => 'pill-pending',    'label' => 'Pending'],
    'shipped'    => ['cls' => 'pill-shipped',    'label' => 'Shipped'],
    'cancelled'  => ['cls' => 'pill-cancelled',  'label' => 'Cancelled'],
    'refunded'   => ['cls' => 'pill-refunded',   'label' => 'Refunded'],
];

$pay_cfg = [
    'paid'     => ['cls' => 'pay-paid',     'label' => 'Paid'],
    'pending'  => ['cls' => 'pay-pending',  'label' => 'Unpaid'],
    'failed'   => ['cls' => 'pay-failed',   'label' => 'Failed'],
    'refunded' => ['cls' => 'pay-refunded', 'label' => 'Refunded'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Orders – Virtue & Verve Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./css/admin-orders.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>
<div class="dim-overlay" id="dimOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <a class="logo-mark" href="index.html">Virtue &amp; <span>Verve</span></a>
      <div class="logo-sub">Admin Dashboard</div>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-section-label">Overview</div>
      <a class="nav-item" href="./index.html"><span class="nav-icon"><i class="fa-solid fa-grip"></i></span><span class="nav-label">Dashboard</span></a>
      <a class="nav-item" href="./admin-categories.html"><span class="nav-icon"><i class="fa-solid fa-layer-group"></i></span><span class="nav-label">Categories</span></a>
      <a class="nav-item" href="./admin-products.html"><span class="nav-icon"><i class="fa-solid fa-box"></i></span><span class="nav-label">Products</span></a>
      <a class="nav-item active" href="./admin-orders.html"><span class="nav-icon"><i class="fa-solid fa-cart-shopping"></i></span><span class="nav-label">Orders</span><span class="nav-badge red"><?php echo count($orders); ?></span></a>
      <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-people-group"></i></span><span class="nav-label">Customers</span></a>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-section-label">Store</div>
      <a class="nav-item" href="shop.html" target="_blank"><span class="nav-icon"><i class="fa-solid fa-store"></i></span><span class="nav-label">View Store</span></a>
      <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-star"></i></span><span class="nav-label">Reviews</span><span class="nav-badge">7</span></a>
      <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-gear"></i></span><span class="nav-label">Settings</span></a>
    </div>

    <div class="sidebar-footer">
      <div class="admin-profile" onclick="showToast('Profile settings coming soon!')">
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
      <h1 class="page-heading">All <span>Orders</span></h1>
    </div>
    <div class="topbar-right">
      <div class="search-bar">
        <span style="color:var(--light);font-size:.9rem">🔍</span>
        <input type="text" id="topSearch" placeholder="Search by order ID, customer…"/>
      </div>
      <div class="icon-btn" title="Notifications"><span>🔔</span><span class="notif-dot"></span></div>
      <button class="export-btn" onclick="showToast('📥 Exporting orders as CSV…')">📥 Export</button>
    </div>
  </header>

  <div class="content">

    <div class="kpi-row">
      <div class="kpi-card" style="--kpi-c:var(--primary);animation-delay:.05s">
        <div class="kpi-head">
          <div class="kpi-icon" style="background:var(--primary-faint)">📦</div>
          <span class="kpi-trend trend-up">↑ 24%</span>
        </div>
        <div class="kpi-val" id="kpiTotal"><?php echo count($orders); ?></div>
        <div class="kpi-label">Total Orders</div>
      </div>
      <div class="kpi-card" style="--kpi-c:var(--warning);animation-delay:.1s">
        <div class="kpi-head">
          <div class="kpi-icon" style="background:var(--warning-light)">⏳</div>
          <span class="kpi-trend trend-warn">Needs action</span>
        </div>
        <div class="kpi-val" id="kpiPending">
          <?php echo count(array_filter($orders, function($o) { return $o['status'] === 'pending'; })); ?>
        </div>
        <div class="kpi-label">Pending</div>
      </div>
      <div class="kpi-card" style="--kpi-c:var(--info);animation-delay:.15s">
        <div class="kpi-head">
          <div class="kpi-icon" style="background:var(--info-light)">🔄</div>
          <span class="kpi-trend trend-up">Active</span>
        </div>
        <div class="kpi-val" id="kpiProcessing">
          <?php echo count(array_filter($orders, function($o) { return $o['status'] === 'processing'; })); ?>
        </div>
        <div class="kpi-label">Processing</div>
      </div>
      <div class="kpi-card" style="--kpi-c:var(--success);animation-delay:.2s">
        <div class="kpi-head">
          <div class="kpi-icon" style="background:var(--success-light)">✅</div>
          <span class="kpi-trend trend-up">↑ 18%</span>
        </div>
        <div class="kpi-val" id="kpiDelivered">
          <?php echo count(array_filter($orders, function($o) { return $o['status'] === 'delivered'; })); ?>
        </div>
        <div class="kpi-label">Delivered</div>
      </div>
      <div class="kpi-card" style="--kpi-c:var(--danger);animation-delay:.25s">
        <div class="kpi-head">
          <div class="kpi-icon" style="background:var(--danger-light)">❌</div>
          <span class="kpi-trend trend-down">↓ 3%</span>
        </div>
        <div class="kpi-val" id="kpiCancelled">
          <?php echo count(array_filter($orders, function($o) { return $o['status'] === 'cancelled'; })); ?>
        </div>
        <div class="kpi-label">Cancelled</div>
      </div>
    </div>

    <div class="tabs-bar">
      <button class="tab active" data-status="all">All Orders <span class="tab-count" id="tc-all"><?php echo count($orders); ?></span></button>
      <button class="tab" data-status="pending">Pending <span class="tab-count" id="tc-pending"><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'pending'; })); ?></span></button>
      <button class="tab" data-status="processing">Processing <span class="tab-count" id="tc-processing"><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'processing'; })); ?></span></button>
      <button class="tab" data-status="shipped">Shipped <span class="tab-count" id="tc-shipped"><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'shipped'; })); ?></span></button>
      <button class="tab" data-status="delivered">Delivered <span class="tab-count" id="tc-delivered"><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'delivered'; })); ?></span></button>
      <button class="tab" data-status="cancelled">Cancelled <span class="tab-count" id="tc-cancelled"><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'cancelled'; })); ?></span></button>
      <button class="tab" data-status="refunded">Refunded <span class="tab-count" id="tc-refunded"><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'refunded'; })); ?></span></button>
    </div>

    <div class="toolbar">
      <div class="toolbar-left">
        <select class="filter-select" id="payFilter">
          <option value="all">All Payments</option>
          <option value="paid">Paid</option>
          <option value="pending">Unpaid</option>
          <option value="refunded">Refunded</option>
        </select>
        <select class="filter-select" id="cityFilter">
          <option value="all">All Cities</option>
          <option value="Lagos">Lagos</option>
          <option value="Abuja">Abuja</option>
          <option value="Port Harcourt">Port Harcourt</option>
          <option value="Ibadan">Ibadan</option>
          <option value="Kano">Kano</option>
        </select>
        <input class="date-input" type="date" id="dateFrom" title="From date"/>
        <input class="date-input" type="date" id="dateTo" title="To date"/>
      </div>
      <div class="toolbar-right">
        <select class="sort-select" id="sortSel">
          <option value="newest">Newest First</option>
          <option value="oldest">Oldest First</option>
          <option value="amount-desc">Amount ↓</option>
          <option value="amount-asc">Amount ↑</option>
        </select>
      </div>
    </div>

    <div class="table-wrap" style="overflow-x: auto; max-width: 100%;">
      <table class="orders-table">
        <thead>
          <tr>
            <th class="th-check"><div class="custom-cb" id="selectAll">✓</div></th>
            <th class="th-sort" data-col="id">Order ID ↕</th>
            <th>Customer</th>
            <th>Items</th>
            <th class="th-sort" data-col="amount">Amount ↕</th>
            <th>Status</th>
            <th>Payment</th>
            <th class="th-sort" data-col="date">Date ↕</th>
            <th>City</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="ordersBody">
          <?php foreach ($orders as $index => $o): 
            $sc = isset($status_cfg[$o['status']]) ? $status_cfg[$o['status']] : ['cls' => 'pill-pending', 'label' => 'Pending'];
            $pc = isset($pay_cfg[$o['payment']]) ? $pay_cfg[$o['payment']] : ['cls' => 'pay-pending', 'label' => 'Unpaid'];
            
            $thumbs_html = '';
            foreach (array_slice($o['items'], 0, 2) as $it) {
                $thumbs_html .= '<div class="item-thumb"><img src="' . htmlspecialchars($it['img']) . '" alt="' . htmlspecialchars($it['name']) . '" loading="lazy"/></div>';
            }
            $extra_html = count($o['items']) > 2 ? '<div class="item-more">+' . (count($o['items']) - 2) . '</div>' : '';
          ?>
          <tr id="row-<?php echo $o['id']; ?>" style="animation-delay: <?php echo $index * 0.04; ?>s">
            <td><div class="custom-cb">✓</div></td>
            <td>
              <div class="order-id-cell">
                <div class="oid"><?php echo htmlspecialchars($o['oid']); ?></div>
                <div class="odate"><?php echo htmlspecialchars($o['date']); ?></div>
              </div>
            </td>
            <td>
              <div class="cust-cell">
                <div class="cust-av"><?php echo htmlspecialchars($o['customer']['av']); ?></div>
                <div>
                  <div class="cust-name"><?php echo htmlspecialchars($o['customer']['name']); ?></div>
                  <div class="cust-email"><?php echo htmlspecialchars($o['customer']['email']); ?></div>
                </div>
              </div>
            </td>
            <td>
              <div class="items-cell" style="display:flex;align-items:center">
                <?php echo $thumbs_html; ?>
                <?php echo $extra_html; ?>
                <span style="font-size:.72rem;color:var(--light);margin-left:10px"><?php echo count($o['items']); ?> item<?php echo count($o['items']) > 1 ? 's' : ''; ?></span>
              </div>
            </td>
            <td>
              <div class="amt-cell">
                <div class="amt">₦<?php echo number_format($o['amount'], 0, '.', ','); ?></div>
              </div>
            </td>
            <td><span class="status-pill <?php echo $sc['cls']; ?>"><span class="sdot"></span><?php echo $sc['label']; ?></span></td>
            <td><span class="pay-pill <?php echo $pc['cls']; ?>"><?php echo $pc['label']; ?></span></td>
            <td style="white-space:nowrap;font-size:.8rem"><?php echo htmlspecialchars($o['date']); ?></td>
            <td style="font-size:.8rem"><?php echo htmlspecialchars($o['city']); ?></td>
            <td>
              <div class="row-actions">
                <div class="row-btn" title="View">👁</div>
                <div class="row-btn" title="Print Invoice">🖨️</div>
                <div class="row-btn danger" title="Cancel">❌</div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      
      <?php if (empty($orders)): ?>
      <div class="empty-state" id="emptyState">
        <div class="empty-icon">📭</div>
        <h3 class="empty-title">No <em>orders</em> found</h3>
        <p class="empty-sub">Try adjusting your filters or search query</p>
      </div>
      <?php endif; ?>
    </div>

    <div class="pagination-bar">
      <div class="page-info">Showing <strong>1–<?php echo count($orders); ?></strong> of <strong><?php echo count($orders); ?></strong> orders</div>
      <div class="page-btns" id="pageBtns">
        <button class="pg-btn" disabled style="opacity:.4">← Prev</button>
        <button class="pg-btn active">1</button>
        <button class="pg-btn" disabled style="opacity:.4">Next →</button>
      </div>
    </div>

  </div>
</div>

<div class="bulk-bar" id="bulkBar">
  <span class="bulk-count"><strong id="bulkCount">0</strong> selected</span>
  <div class="bulk-actions">
    <button class="bulk-btn">Mark Processing</button>
    <button class="bulk-btn">Mark Shipped</button>
    <button class="bulk-btn">Mark Delivered</button>
    <button class="bulk-btn danger">Cancel</button>
  </div>
  <button class="bulk-close" id="bulkClose">✕</button>
</div>

<div class="drawer-overlay" id="drawerOverlay">
  <div class="drawer" id="drawer">
    <div class="drawer-head">
      <div>
        <div class="drawer-title">Order <em id="drawerOID">#VV-1042</em></div>
        <div style="font-size:.72rem;color:var(--light);margin-top:4px" id="drawerDate"></div>
      </div>
      <button class="drawer-close" id="drawerClose">✕</button>
    </div>
    <div class="drawer-body" id="drawerBody"></div>
    <div class="drawer-footer">
      <div class="status-select-wrap">
        <select class="status-select" id="drawerStatusSel">
          <option value="pending">Pending</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
          <option value="refunded">Refunded</option>
        </select>
        <button class="btn-update" id="drawerUpdateBtn">Update</button>
      </div>
      <button class="btn-print" onclick="showToast('🖨️ Opening print view…')">Print</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="confirmOverlay">
  <div class="confirm-modal">
    <div class="confirm-icon">⚠️</div>
    <h3 class="confirm-title">Cancel Order?</h3>
    <p class="confirm-sub" id="confirmMsg">Are you sure you want to cancel this order? The customer will be notified.</p>
    <div class="confirm-actions">
      <button class="btn-cancel" id="cancelBtn">Go Back</button>
      <button class="btn-del" id="confirmBtn">Yes, Cancel</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"><span>✨</span><span id="toastMsg">Done!</span></div>

<script>
// ── MOBILE SIDEBAR TOGGLE ──────────────────────────────────────────────────────
document.getElementById('menuToggle').addEventListener('click',()=>{
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('dimOverlay').classList.toggle('visible');
});
document.getElementById('dimOverlay').addEventListener('click',()=>{
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('dimOverlay').classList.remove('visible');
});

// ── CUSTOM DIGITAL CURSOR ─────────────────────────────────────────────────────
const cursor=document.getElementById('cursor'),ring=document.getElementById('cursorRing');
document.addEventListener('mousemove',e=>{
  cursor.style.left=e.clientX+'px';cursor.style.top=e.clientY+'px';
  setTimeout(()=>{ring.style.left=e.clientX+'px';ring.style.top=e.clientY+'px';},80);
});
document.querySelectorAll('a,button,.nav-item,.kpi-card,.row-btn,.custom-cb,.tab,.pg-btn').forEach(el=>{
  el.addEventListener('mouseenter',()=>{cursor.style.transform='translate(-50%,-50%) scale(1.8)';cursor.style.background='var(--gold)';});
  el.addEventListener('mouseleave',()=>{cursor.style.transform='translate(-50%,-50%) scale(1)';cursor.style.background='var(--primary)';});
});

// ── FEEDBACK TOAST ─────────────────────────────────────────────────────────────
function showToast(msg){
  const t=document.getElementById('toast');
  document.getElementById('toastMsg').textContent=msg;
  t.classList.add('show');
  clearTimeout(showToast._t);
  showToast._t=setTimeout(()=>t.classList.remove('show'),2800);
}
</script>
</body>
</html>