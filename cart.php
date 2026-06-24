<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ─── PURE PHP FORM ACTION INTERCEPTOR ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $id = $_POST['product_id'];
        $name = $_POST['product_name'];
        $price = floatval($_POST['product_price']);
        $category = $_POST['product_category'];
        $img = $_POST['product_img'];
        $qty = intval($_POST['product_qty']);
        $color = $_POST['product_color'];

        $cart_key = $id . '_' . str_replace('#', '', $color);

        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$cart_key] = [
                'id' => $id,
                'name' => $name,
                'price' => $price,
                'category' => $category,
                'img' => $img,
                'qty' => $qty,
                'color' => $color
            ];
        }
    }

    if ($action === 'update_qty') {
        $key = $_POST['cart_key'];
        $delta = intval($_POST['delta']);
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] += $delta;
            if ($_SESSION['cart'][$key]['qty'] < 1) {
                unset($_SESSION['cart'][$key]);
            }
        }
    }

    if ($action === 'remove') {
        $key = $_POST['cart_key'];
        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }
    }

    // Redirect cleanly to refresh page state without form double submission warnings
    header("Location: cart.php");
    exit;
}

// Auto-populate accounts fields
$logged_in_name  = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : '';
$logged_in_email = isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : '';
$logged_in_phone = isset($_SESSION['customer_phone']) ? $_SESSION['customer_phone'] : '';

// Direct PHP Pricing Computations
$subtotal = 0;
$total_items = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['qty'];
    $total_items += $item['qty'];
}

$delivery_fee = ($subtotal >= 50000 || $subtotal == 0) ? 0 : 2500;
$grand_total = $subtotal + $delivery_fee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Cart – Virtue & Verve</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./css/cart.css">
  <style>
    .delivery-details-section { background: var(--white); border: 1px solid var(--border); padding: 30px; margin-top: 30px; }
    .delivery-title { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; margin-bottom: 20px; }
    .delivery-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .full-width { grid-column: span 2; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-input, .form-textarea { width: 100%; padding: 12px; border: 1.5px solid var(--border); outline: none; }
    .qty-btn-submit { border: none; background: #f4f1eb; cursor: pointer; font-size: 1.1rem; padding: 4px 10px; font-weight: bold; }
  </style>
</head>
<body>

  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <nav>
    <a href="index.php" class="logo">Virtue &amp; <span>Verve</span></a>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="products.php">Shop</a></li>
    </ul>
    <div class="nav-right">
      <button class="cart-icon-wrap" onclick="window.location.reload();">
        🛍️ <span class="cart-badge"><?php echo $total_items; ?></span>
      </button>
    </div>
  </nav>

  <div class="page-hero">
    <div class="hero-inner">
      <h1 class="page-title">Your <em>Cart</em></h1>
      <p class="cart-meta"><strong><?php echo $total_items; ?> items</strong> inside your basket</p>
    </div>
  </div>

  <div class="cart-layout">
    <div class="cart-items-section">

      <?php if (empty($_SESSION['cart'])): ?>
        <div style="text-align: center; padding: 60px 20px;">
          <h2>Your cart is currently empty</h2>
          <p style="margin: 15px 0 25px; color: var(--mid);">Explore our collections to add premium items.</p>
          <a href="products.php" style="padding: 12px 30px; background: var(--primary); color: #fff; text-decoration: none;">Browse Collection</a>
        </div>
      <?php else: ?>

        <div class="cart-header">
          <span>Product</span>
          <span>Price</span>
          <span>Quantity</span>
          <span>Total</span>
          <span></span>
        </div>

        <div class="cart-items-list">
          <?php foreach ($_SESSION['cart'] as $key => $item): ?>
            <div class="cart-item" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; align-items: center; padding: 20px 0; border-bottom: 1px solid var(--border);">
              <div style="display: flex; align-items: center;">
                <img src="<?php echo $item['img']; ?>" style="width: 60px; height: 60px; object-fit: cover; margin-right: 15px;" />
                <div>
                  <h3 style="font-size: 1rem; font-weight: 500;"><?php echo htmlspecialchars($item['name']); ?></h3>
                  <small style="color: <?php echo $item['color']; ?>; font-weight: bold;">● Color: <?php echo $item['color']; ?></small>
                </div>
              </div>
              <div>₦<?php echo number_format($item['price']); ?></div>
              
              <div>
                <form action="cart.php" method="POST" style="display: inline-block;">
                  <input type="hidden" name="action" value="update_qty">
                  <input type="hidden" name="cart_key" value="<?php echo $key; ?>">
                  <input type="hidden" name="delta" value="-1">
                  <button type="submit" class="qty-btn-submit">−</button>
                </form>
                <span style="display: inline-block; width: 30px; text-align: center; font-weight: 500;"><?php echo $item['qty']; ?></span>
                <form action="cart.php" method="POST" style="display: inline-block;">
                  <input type="hidden" name="action" value="update_qty">
                  <input type="hidden" name="cart_key" value="<?php echo $key; ?>">
                  <input type="hidden" name="delta" value="1">
                  <button type="submit" class="qty-btn-submit">+</button>
                </form>
              </div>

              <div>₦<?php echo number_format($item['price'] * $item['qty']); ?></div>
              
              <div>
                <form action="cart.php" method="POST">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="cart_key" value="<?php echo $key; ?>">
                  <button type="submit" style="background: none; border: none; color: #e05252; font-size: 1.2rem; cursor: pointer;">✕</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="delivery-details-section">
          <h2 class="delivery-title">Delivery <em>Details</em></h2>
          <form action="process_checkout.php" method="POST">
            <div class="delivery-grid">
              <div class="form-group full-width">
                <label>Full Name</label>
                <input type="text" class="form-input" name="fullname" required value="<?php echo htmlspecialchars($logged_in_name); ?>">
              </div>
              <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" class="form-input" name="phone" required value="<?php echo htmlspecialchars($logged_in_phone); ?>">
              </div>
              <div class="form-group">
                <label>Email Address</label>
                <input type="email" class="form-input" name="email" required value="<?php echo htmlspecialchars($logged_in_email); ?>">
              </div>
              <div class="form-group full-width">
                <label>Street Address</label>
                <input type="text" class="form-input" name="address" required>
              </div>
              <div class="form-group">
                <label>City</label>
                <input type="text" class="form-input" name="city" required>
              </div>
              <div class="form-group">
                <label>State</label>
                <input type="text" class="form-input" name="state" required placeholder="e.g., Lagos">
              </div>
              <div class="form-group full-width">
                <label>Order Notes</label>
                <textarea class="form-textarea" name="notes"></textarea>
              </div>
            </div>
            <div style="margin-top: 30px;">
               <button type="submit" style="width: 100%; padding: 16px; background: var(--primary); color: white; border: none; font-size: 1.1rem; cursor: pointer; font-weight: 500;">Place Order Securely →</button>
            </div>
          </form>
        </div>
      <?php endif; ?>

    </div>

    <aside class="order-summary" style="background: #faf8f5; padding: 30px; border: 1px solid var(--border); height: fit-content;">
      <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">Order Summary</h3>
      <div style="display: flex; flex-direction: column; gap: 12px;">
        <div style="display: flex; justify-content: space-between;"><span>Subtotal</span><strong>₦<?php echo number_format($subtotal); ?></strong></div>
        <div style="display: flex; justify-content: space-between;"><span>Delivery</span><strong><?php echo ($delivery_fee === 0) ? 'FREE' : '₦'.number_format($delivery_fee); ?></strong></div>
        <hr style="border: none; border-top: 1px solid var(--border); margin: 8px 0;">
        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;"><span>Total</span><span style="color: var(--primary);">₦<?php echo number_format($grand_total); ?></span></div>
      </div>
    </aside>
  </div>

  <script>
    const cursor = document.getElementById('cursor');
    const ring = document.getElementById('cursorRing');
    if(cursor && ring) {
        document.addEventListener('mousemove', e => {
          cursor.style.left = e.clientX + 'px'; cursor.style.top = e.clientY + 'px';
          setTimeout(() => { ring.style.left = e.clientX + 'px'; ring.style.top = e.clientY + 'px'; }, 40);
        });
    }
  </script>
</body>
</html>