<?php
include '../includes/db.php';
$status_cfg = [
    // Lowercase variants
    'pending'    => ['cls' => 'pill-pending',    'label' => 'Pending'],
    'processing' => ['cls' => 'pill-processing', 'label' => 'Processing'],
    'delivered'  => ['cls' => 'pill-delivered',  'label' => 'Delivered'],
    'cancelled'  => ['cls' => 'pill-cancelled',  'label' => 'Cancelled'],
    
    // Capitalized variants (matching your database ENUM exactly)
    'Pending'    => ['cls' => 'pill-pending',    'label' => 'Pending'],
    'Processing' => ['cls' => 'pill-processing', 'label' => 'Processing'],
    'Delivered'  => ['cls' => 'pill-delivered',  'label' => 'Delivered'],
    'Cancelled'  => ['cls' => 'pill-cancelled',  'label' => 'Cancelled']
];

$pay_cfg = [
    // Lowercase variants
    'paid'     => ['cls' => 'pay-paid',     'label' => 'Paid'],
    'unpaid'   => ['cls' => 'pay-pending',  'label' => 'Unpaid'],
    
    // Capitalized variants
    'Paid'     => ['cls' => 'pay-paid',     'label' => 'Paid'],
    'Unpaid'   => ['cls' => 'pay-pending',  'label' => 'Unpaid']
];

$orders = [];

$query = "
SELECT *
FROM orders
ORDER BY created_at DESC
";

$result = mysqli_query($conn, $query);

while ($order = mysqli_fetch_assoc($result)) {

    $items = [];

    $item_query = "
    SELECT *
    FROM order_items
    WHERE order_id = {$order['id']}
    ";

    $item_result = mysqli_query($conn, $item_query);

    while ($item = mysqli_fetch_assoc($item_result)) {

        $items[] = [
            'name'  => $item['product_name'],
            'img'   => '',
            'price' => $item['price'],
            'qty'   => $item['quantity'],
            'color' => $item['color']
        ];
    }

$orders[] = [
        'id'       => $order['id'],
        'oid'      => '#VV-' . str_pad($order['id'], 4, '0', STR_PAD_LEFT),
        'customer' => [
            'name' => $order['fullname'],
            'email'=> $order['email'],
            'av'   => '👤'
        ],
        'items'    => $items,
        'amount'   => $order['total_amount'],
        'status'   => $order['status'], 
        'payment'  => $order['payment_status'],
        'date'     => date('M d, Y', strtotime($order['created_at'])),
        'city'     => $order['city']
    ];
}
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
      
      <button class="tab" data-status="delivered">Delivered <span class="tab-count" id="tc-delivered"><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'delivered'; })); ?></span></button>
      <button class="tab" data-status="cancelled">Cancelled <span class="tab-count" id="tc-cancelled"><?php echo count(array_filter($orders, function($o) { return $o['status'] === 'cancelled'; })); ?></span></button>

    <div class="toolbar">
      <div class="toolbar-left">
        <select class="filter-select" id="payFilter">
          <option value="all">All Payments</option>
          <option value="paid">Paid</option>
          <option value="pending">Unpaid</option>
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
                <div
    class="row-btn view-order"
    title="View"
    data-id="<?php echo $o['id']; ?>">
    👁
</div>
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
    <option value="Pending">Pending</option>
    <option value="Processing">Processing</option>
    <option value="Delivered">Delivered</option>
    <option value="Cancelled">Cancelled</option>
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


</script>
<script>

// Existing code...

function showToast(msg){
  const t=document.getElementById('toast');
  document.getElementById('toastMsg').textContent=msg;
  t.classList.add('show');
  clearTimeout(showToast._t);
  showToast._t=setTimeout(()=>t.classList.remove('show'),2800);
}


// ADD THIS BELOW

let currentOrderId = null;

document.querySelectorAll('.view-order').forEach(btn => {

    btn.addEventListener('click', function () {

        currentOrderId = this.dataset.id;

        fetch('get_order_details.php?id=' + currentOrderId)
        .then(res => res.json())
        .then(data => {

            document.getElementById('drawerOID').innerText =
                '#VV-' +
String(data.order.id)
.padStart(4,'0');

            document.getElementById('drawerDate').innerText =
                data.order.created_at;

            let html = `
                <h3>Customer Details</h3>

                <p><strong>Name:</strong> ${data.order.fullname}</p>
                <p><strong>Email:</strong> ${data.order.email}</p>
                <p><strong>Phone:</strong> ${data.order.phone}</p>

                <p><strong>Address:</strong> ${data.order.shipping_address}</p>

                <p><strong>City:</strong> ${data.order.city}</p>

                <p><strong>State:</strong> ${data.order.state}</p>

                <hr>

                <h3>Products</h3>
            `;

            data.items.forEach(item => {

                html += `
                    <div style="margin-bottom:15px;">
                        <strong>${item.product_name}</strong><br>
                        Qty: ${item.quantity}<br>
                        Color: ${item.color}<br>
                        Price: ₦${item.price}
                    </div>
                `;
            });

            html += `
                <hr>
                <h3>Total: ₦${data.order.total_amount}</h3>
            `;

            document.getElementById('drawerBody').innerHTML = html;

            document.getElementById('drawerOverlay')
                .classList.add('open');
        });
    });
});


document.getElementById('drawerClose')
.addEventListener('click', () => {

    document.getElementById('drawerOverlay')
    .classList.remove('open');

});

document.getElementById('drawerOverlay')
.addEventListener('click', e => {

    if(e.target.id === 'drawerOverlay'){
        document.getElementById('drawerOverlay')
        .classList.remove('open');
    }

});


document.getElementById('drawerUpdateBtn')
.addEventListener('click', () => {

    const status =
        document.getElementById('drawerStatusSel').value;

    const formData = new FormData();

    formData.append('order_id', currentOrderId);
    formData.append('status', status);

    fetch('update_order_status.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {

        if(data === 'success'){

            showToast('Order updated');

            setTimeout(() => {
                location.reload();
            }, 1000);

        } else {

            showToast('Update failed');

        }

    });

});

</script>
</body>
</html>