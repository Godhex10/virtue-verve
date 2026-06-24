<?php
session_start();

// Guard Clause: If the customer isn't logged in, redirect them to your login page
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_name = $_SESSION['customer_name'];
$customer_id = $_SESSION['customer_id'];

/* 
  ─── DATABASE INTEGRATION PREPARATION ───
  Below is a clean structured template loop. When you connect your MySQL database later, 
  your query will look something like this:
  
  $query = "SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC";
  ...
*/

// Mock data representing all orders placed by this specific logged-in user
include './includes/db.php';

$customer_orders = [];

$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $customer_id);
$stmt->execute();

$orders_result = $stmt->get_result();

while ($order = $orders_result->fetch_assoc()) {

    $items = [];

    $item_stmt = $conn->prepare("
        SELECT *
        FROM order_items
        WHERE order_id = ?
    ");

    $item_stmt->bind_param("i", $order['id']);
    $item_stmt->execute();

    $items_result = $item_stmt->get_result();

    while ($item = $items_result->fetch_assoc()) {

        $items[] = [
            'name' => $item['product_name'],
            'qty'  => $item['quantity'],
            'price'=> $item['price']
        ];
    }

    $customer_orders[] = [
        'order_id' => '#' . $order['id'],
        'date' => date('F j, Y', strtotime($order['created_at'])),
        'status' => strtolower($order['status']),
        'total' => $order['total_amount'],
        'carrier' => '',
        'tracking_number' => '',
        'address' => $order['shipping_address'],
        'items' => $items
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Orders – Virtue & Verve</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght=0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght=300;400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./css/cart.css">
  <style>
    .dashboard-container {
      max-width: 1000px;
      margin: 60px auto;
      padding: 0 20px;
    }
    
    .welcome-banner {
      margin-bottom: 40px;
    }
    .welcome-banner h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2.4rem;
      font-weight: 400;
      color: var(--dark);
    }
    .welcome-banner h2 em {
      font-style: italic;
      color: var(--primary);
    }

    /* Individual Order Card Group */
    .order-history-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 2px;
      margin-bottom: 30px;
      box-shadow: 0 4px 20px rgba(8,129,120,0.01);
      overflow: hidden;
    }

    .order-card-header {
      background: var(--cream-faint);
      border-bottom: 1px solid var(--border);
      padding: 20px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
    }

    .meta-group-grid {
      display: flex;
      gap: 40px;
      flex-wrap: wrap;
    }

    .meta-item h4 {
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--mid);
      margin-bottom: 4px;
    }

    .meta-item p {
      font-size: 0.88rem;
      font-weight: 500;
      color: var(--dark);
    }

    /* Status Pill Badges */
    .status-badge {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 500;
      padding: 6px 14px;
      border-radius: 50px;
      display: inline-block;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-processing { background: #cce5ff; color: #004085; }
    .status-shipped { background: rgba(8,129,120,0.1); color: var(--primary); }
    .status-delivered { background: #d4edda; color: #155724; }

    .order-card-body {
      padding: 30px;
    }

    /* Micro Tracking Timeline for History Dashboard */
    .mini-timeline {
      display: flex;
      justify-content: space-between;
      position: relative;
      margin: 20px 0 35px;
    }
    .mini-timeline::before {
      content: '';
      position: absolute;
      top: 15px;
      left: 30px;
      right: 30px;
      height: 2px;
      background: var(--border);
      z-index: 1;
    }
    .mini-timeline-fill {
      position: absolute;
      top: 15px;
      left: 30px;
      height: 2px;
      background: var(--primary);
      z-index: 1;
      transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .mini-step {
      position: relative;
      z-index: 2;
      text-align: center;
      flex: 1;
    }
    .mini-dot {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--white);
      border: 2px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 8px;
      font-size: 0.8rem;
    }
    .mini-step.completed .mini-dot {
      background: var(--primary);
      border-color: var(--primary);
      color: var(--white);
    }
    .mini-step.active .mini-dot {
      border-color: var(--primary);
      color: var(--primary);
      box-shadow: 0 0 0 4px var(--primary-faint);
    }
    .mini-label {
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--light);
    }
    .mini-step.active .mini-label, .mini-step.completed .mini-label {
      color: var(--dark);
    }

    /* Manifest Table inside orders */
    .manifest-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    .manifest-table th {
      text-align: left;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--mid);
      padding-bottom: 10px;
      border-bottom: 1.5px solid var(--border);
    }
    .manifest-table td {
      padding: 14px 0;
      border-bottom: 1px solid var(--border);
      font-size: 0.9rem;
      color: var(--dark);
    }

    .no-orders-box {
      text-align: center;
      padding: 60px;
      border: 1px dashed var(--border);
      background: var(--white);
    }

    @media(max-width: 768px) {
      .order-card-header { flex-direction: column; align-items: flex-start; }
      .meta-group-grid { gap: 16px 24px; }
      .mini-timeline { flex-direction: column; align-items: flex-start; gap: 16px; padding-left: 10px; }
      .mini-timeline::before, .mini-timeline-fill { display: none; }
      .mini-step { display: flex; align-items: center; gap: 12px; }
      .mini-dot { margin: 0; }
    }
  </style>
</head>
<body>

  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <!-- Mobile Nav -->
  <div class="mobile-nav" id="mobileNav">
    <span class="close-nav" id="closeNav">✕</span>
    <a href="index.php">Home</a>
    <a href="products.php">Shop</a>
    <a href="cart.php">Cart</a>
  </div>

  <!-- NAV -->
  <nav>
    <a href="index.php" class="logo">Virtue &amp; <span>Verve</span></a>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="products.php">Shop</a></li>
      <li><a href="orders.php" class="active">My Orders</a></li>
      <li><a href="cart.php" class="nav-cta">My Cart</a></li>
    </ul>
    <div class="nav-right">
      <button class="cart-icon-wrap" onclick="location.href='cart.php'">
        🛍️ <span class="cart-badge" id="navCartCount">0</span>
      </button>
      <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
    </div>
  </nav>

  <!-- HERO -->
  <div class="page-hero">
    <div class="hero-inner">
      <div class="hero-left">
        <nav class="breadcrumb">
          <a href="index.php">Home</a><span>/</span>
          <span>Account</span><span>/</span>
          <span>Order History</span>
        </nav>
        <h1 class="page-title">Your <em>Orders</em></h1>
        <p class="cart-meta">Review manifest status records for all past purchases.</p>
      </div>
    </div>
  </div>

  <!-- DASHBOARD CONTAINER -->
  <main class="dashboard-container">
    
    <div class="welcome-banner">
      <h2>Welcome back, <em><?= htmlspecialchars($customer_name) ?></em></h2>
      <p class="delivery-sub" style="margin-top:4px">Here is the standard operational dispatch history tied to your secure profile account.</p>
    </div>

    <?php if (empty($customer_orders)): ?>
        <!-- Empty State -->
        <div class="no-orders-box">
            <h3 class="delivery-title" style="font-size:1.5rem">No orders found <em>yet</em></h3>
            <p class="delivery-sub">Once you complete a purchase checkout processing, your track logs will instantly display here.</p>
            <a href="products.php" class="empty-cta" style="display:inline-block; margin-top:15px">Go to Shop</a>
        </div>
    <?php else: ?>
        
        <?php foreach ($customer_orders as $order): 
            // Calculate progress bar percentages for active horizontal timeline tracking updates
            $status_map = [
    'pending' => 0,
    'processing' => 50,
    'delivered' => 100
];
            $pct = isset($status_map[$order['status']]) ? $status_map[$order['status']] : 0;
        ?>
            <div class="order-history-card">
                <!-- Top Meta Control Section -->
                <div class="order-card-header">
                    <div class="meta-group-grid">
                        <div class="meta-item">
                            <h4>Order Placed</h4>
                            <p><?= $order['date'] ?></p>
                        </div>
                        <div class="meta-item">
                            <h4>Order Reference ID</h4>
                            <p style="color:var(--primary)"><?= htmlspecialchars($order['order_id']) ?></p>
                        </div>
                        <div class="meta-item">
                            <h4>Total Value</h4>
                            <p>₦<?= number_format($order['total']) ?></p>
                        </div>
                    </div>
                    <div>
                        <span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                    </div>
                </div>

                <!-- Expanded Body: Tracking Timeline & Items -->
                <div class="order-card-body">
                    
                    <!-- Dynamic Progress Bar UI Line -->
                    <div class="mini-timeline">
                        <div class="mini-timeline-fill" data-width="<?= $pct ?>%"></div>
                        
                        <div class="mini-step <?= $order['status'] == 'pending' ? 'active' : '' ?> <?= in_array($order['status'], ['processing', 'shipped', 'delivered']) ? 'completed' : '' ?>">
                            <div class="mini-dot">📄</div>
                            <div class="mini-label">Placed</div>
                        </div>
                        <div class="mini-step <?= $order['status'] == 'processing' ? 'active' : '' ?> <?= in_array($order['status'], ['shipped', 'delivered']) ? 'completed' : '' ?>">
                            <div class="mini-dot">⚙️</div>
                            <div class="mini-label">Processing</div>
                        </div>
                        <div class="mini-step <?= $order['status'] == 'shipped' ? 'active' : '' ?> <?= in_array($order['status'], ['delivered']) ? 'completed' : '' ?>">
                            <div class="mini-dot">📦</div>
                            <div class="mini-label">Shipped</div>
                        </div>
                        <div class="mini-step <?= $order['status'] == 'delivered' ? 'active' : '' ?>">
                            <div class="mini-dot">🏠</div>
                            <div class="mini-label">Delivered</div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <table class="manifest-table">
                        <thead>
                            <tr>
                                <th>Product Item</th>
                                <th style="text-align: center;">Quantity</th>
                                <th style="text-align: right;">Unit Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td style="text-align: center;">×<?= $item['qty'] ?></td>
                                    <td style="text-align: right;">₦<?= number_format($item['price']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($order['status'] == 'shipped' && !empty($order['tracking_number'])): ?>
                        <p style="margin-top: 20px; font-size: 0.8rem; color: var(--light);">
                            🚚 Dispatched via <strong><?= htmlspecialchars($order['carrier']) ?></strong>. Waybill tracking code: <span style="color:var(--primary); font-weight:500;"><?= htmlspecialchars($order['tracking_number']) ?></span>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

  </main>

  <!-- FOOTER -->
  <footer>
    <div class="footer-grid">
      <div>
        <div class="footer-logo">Virtue &amp; <span>Verve</span></div>
        <p class="footer-desc">Your go-to destination for premium, stylish bags at unbeatable prices.</p>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="orders.php">Track Order</a></li>
          <li><a href="#">Returns</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 Virtue & Verve. All rights reserved.</p>
    </div>
  </footer>

  <script>
  // UI Cursor mechanics
  const cursor = document.getElementById('cursor');
  const ring = document.getElementById('cursorRing');
  document.addEventListener('mousemove', e => {
    cursor.style.left = e.clientX + 'px';
    cursor.style.top = e.clientY + 'px';
    setTimeout(() => { ring.style.left = e.clientX + 'px'; ring.style.top = e.clientY + 'px'; }, 80);
  });

  document.getElementById('hamburger').addEventListener('click', () => { document.getElementById('mobileNav').classList.add('open'); });
  document.getElementById('closeNav').addEventListener('click', () => { document.getElementById('mobileNav').classList.remove('open'); });

  // Animate active timeline filler bands inside loops smoothly on paint
  window.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.mini-timeline-fill').forEach(bar => {
          setTimeout(() => {
              bar.style.width = bar.dataset.width;
          }, 200);
      });
  });
  </script>
</body>
</html>