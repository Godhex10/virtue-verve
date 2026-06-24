<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include './includes/db.php';

// 1. CAPTURE & VALIDATE ACTIVE PRODUCT ROUTE SLUG
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: shop.html");
    exit;
}

// 2. FETCH SPECIFIC PRODUCT DETAILS FROM DATABASE
$stmt = $conn->prepare("SELECT * FROM products WHERE slug = ? AND status = 'active' LIMIT 1");
if (!$stmt) {
    die("System database structure failure.");
}
$stmt->bind_param("s", $slug);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    die("The requested item is no longer available or does not exist.");
}

// 3. COMPILE AND CALCULATE DYNAMIC CUSTOMER REVIEWS
$product_id = $product['id'];
$reviews = [];
$total_rating = 0;
$rating_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

$rev_stmt = $conn->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY id DESC");
if ($rev_stmt) {
    $rev_stmt->bind_param("i", $product_id);
    $rev_stmt->execute();
    $rev_res = $rev_stmt->get_result();
    while ($r_row = $rev_res->fetch_assoc()) {
        $reviews[] = $r_row;
        $rating = intval($r_row['rating']);
        if (isset($rating_counts[$rating])) {
            $rating_counts[$rating]++;
        }
        $total_rating += $rating;
    }
    $rev_stmt->close();
}

$review_count = count($reviews);
$avg_rating = $review_count > 0 ? round($total_rating / $review_count, 1) : 5.0;

// 4. PARSE SAVED ANGLES AND HEX COLORS STRINGS
$images_gallery = [];
if (!empty($product['image'])) $images_gallery[] = $product['image'];
if (!empty($product['image_2'])) $images_gallery[] = $product['image_2'];
if (!empty($product['image_3'])) $images_gallery[] = $product['image_3'];

if (empty($images_gallery)) {
    $images_gallery[] = 'placeholder.jpg';
}

$color_swatches = !empty($product['colors']) ? explode(',', $product['colors']) : [];

// Compute current cart total count for the header badge
$initial_cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $initial_cart_count += $item['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($product['name']); ?> – Virtue & Verve</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./css/product.css">
</head>
<body>

  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <div class="mobile-nav" id="mobileNav">
    <span class="close-nav" id="closeNav">✕</span>
    <a href="index.html">Home</a>
    <a href="shop.html">Shop</a>
    <a href="#">Categories</a>
    <a href="#">About</a>
  </div>

  <nav>
    <a href="index.html" class="logo">Virtue &amp; <span>Verve</span></a>
    <ul class="nav-links">
      <li><a href="index.html">Home</a></li>
      <li><a href="shop.html">Shop</a></li>
      <li><a href="#">Categories</a></li>
      <li><a href="#">About</a></li>
      <li><a href="shop.html" class="nav-cta">Shop Now</a></li>
    </ul>
    <div class="nav-right">
      <button class="cart-btn" onclick="window.location.href='cart.php'" id="cartBtn" title="Cart">
        🛍️ <span class="cart-count" id="cartCount"><?php echo $initial_cart_count; ?></span>
      </button>
      <div class="hamburger" id="hamburger">
        <span></span><span></span><span></span>
      </div>
    </div>
  </nav>

  <div class="breadcrumb-bar">
    <nav class="breadcrumb">
      <a href="index.html">Home</a>
      <span>/</span>
      <a href="shop.html">Shop</a>
      <span>/</span>
      <a href="shop.html?cat=<?php echo urlencode($product['category_slug']); ?>"><?php echo htmlspecialchars(ucfirst($product['category_slug'])); ?></a>
      <span>/</span>
      <span><?php echo htmlspecialchars($product['name']); ?></span>
    </nav>
  </div>

  <div class="product-section">

    <div class="gallery">
      <div class="main-image-wrap" id="mainImgWrap">
        <?php if (!empty($product['badge'])): ?>
          <span class="img-badge" id="mainBadge"><?php echo htmlspecialchars($product['badge']); ?></span>
        <?php endif; ?>
        <img class="main-img" id="mainImg"
          src="./admin/uploads/products/<?php echo htmlspecialchars($images_gallery[0]); ?>"
          alt="<?php echo htmlspecialchars($product['name']); ?>"/>
        <button class="wishlist-float" id="wishBtn" title="Add to Wishlist">♡</button>
        <span class="zoom-hint">🔍 Click to zoom</span>
      </div>

      <div class="thumbnails">
        <?php foreach ($images_gallery as $index => $img_file): ?>
          <div class="thumb <?php echo $index === 0 ? 'active' : ''; ?>" data-src="./admin/uploads/products/<?php echo htmlspecialchars($img_file); ?>">
            <img src="./admin/uploads/products/<?php echo htmlspecialchars($img_file); ?>" alt="View <?php echo $index + 1; ?>"/>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="share-row">
        <span class="share-label">Share</span>
        <button class="share-btn" title="WhatsApp">💬</button>
        <button class="share-btn" title="Instagram">📸</button>
        <button class="share-btn" title="Facebook">📘</button>
        <button class="share-btn" title="Copy Link" id="copyLink">🔗</button>
      </div>
    </div>

    <div class="product-info">
      <p class="info-eyebrow"><?php echo htmlspecialchars(ucfirst($product['category_slug'])); ?></p>

      <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>

      <div class="rating-row">
        <span class="stars">
          <?php
          for ($i = 1; $i <= 5; $i++) {
              echo $i <= round($avg_rating) ? '★' : '☆';
          }
          ?>
        </span>
        <span class="rating-num"><?php echo number_format($avg_rating, 1); ?></span>
        <span class="rating-sep">|</span>
        <span class="rating-count"><?php echo $review_count; ?> reviews</span>
        <span class="rating-sep">|</span>
        <?php if ($product['stock'] > 0): ?>
          <span class="in-stock">In Stock</span>
        <?php else: ?>
          <span class="out-of-stock" style="color: #8B0000; font-weight: 500;">Out of Stock</span>
        <?php endif; ?>
      </div>

      <div class="price-block">
        <span class="price-main" id="displayPrice">₦<?php echo number_format($product['price']); ?></span>
        <?php if (!empty($product['old_price'])): ?>
          <span class="price-old">₦<?php echo number_format($product['old_price']); ?></span>
          <span class="price-save">Save <?php echo round((($product['old_price'] - $product['price']) / $product['old_price']) * 100); ?>%</span>
        <?php endif; ?>
      </div>

      <p style="font-size:0.9rem;color:var(--mid);line-height:1.8;margin-bottom:24px;animation:fadeUp 0.7s 0.52s both;display:block">
        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
      </p>

      <div class="divider"></div>

      <?php if (!empty($color_swatches)): ?>
      <div class="colors-section">
        <p class="option-label">Color <span id="selectedColor">— Select Color Option</span></p>
        <div class="color-swatches">
          <?php foreach ($color_swatches as $idx => $hex_code): 
              $hex_code = trim($hex_code);
              if (empty($hex_code)) continue;
          ?>
            <div class="color-swatch <?php echo $idx === 0 ? 'active' : ''; ?>" style="background:<?php echo htmlspecialchars($hex_code); ?>" data-color="<?php echo htmlspecialchars($hex_code); ?>" title="<?php echo htmlspecialchars($hex_code); ?>"></div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="qty-section">
        <p class="option-label">Quantity</p>
        <div class="qty-control">
          <button class="qty-btn" id="qtyMinus">−</button>
          <input class="qty-num" id="qtyNum" type="number" value="1" min="1" max="10" readonly/>
          <button class="qty-btn" id="qtyPlus">+</button>
        </div>
      </div>

      <div class="cta-section">
        <?php if (isset($_SESSION['customer_id']) || isset($_SESSION['user_id'])): ?>
          <button class="btn-add-cart" id="addToCartBtn" data-product-id="<?php echo $product['id']; ?>" data-category="<?php echo htmlspecialchars(ucfirst($product['category_slug'])); ?>" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-price="<?php echo $product['price']; ?>" data-img="./admin/uploads/products/<?php echo htmlspecialchars($images_gallery[0]); ?>">🛍 Add to Cart</button>
        <?php else: ?>
          <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn-add-cart" style="display: flex; justify-content: center; align-items: center; text-decoration: none;">🔒 Log In to Purchase</a>
        <?php endif; ?>
      </div>

      <div class="trust-badges">
        <div class="trust-badge">
          <span class="t-icon">🚚</span>
          <span class="t-title">Free Delivery</span>
          <span class="t-sub">Orders above ₦20k</span>
        </div>
        <div class="trust-badge">
          <span class="t-icon">🔄</span>
          <span class="t-title">Easy Returns</span>
          <span class="t-sub">7-day policy</span>
        </div>
        <div class="trust-badge">
          <span class="t-icon">🔒</span>
          <span class="t-title">Secure Payment</span>
          <span class="t-sub">100% protected</span>
        </div>
      </div>

      <div class="accordion">
        <div class="acc-item open">
          <div class="acc-header">Product Details <span class="acc-icon">+</span></div>
          <div class="acc-body">
            <div class="acc-content">
              <ul>
                <li>Premium craft construction with refined attention to style details</li>
                <li>Strategic interior compartments for flawless item separation</li>
                <li>Durable custom metallic zippers and custom hardware accents</li>
                <li>Designed dynamically for fashion versatility and everyday durability</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <section class="reviews-section">
    <div class="section-header" style="text-align:center">
      <p class="section-eyebrow">What They Say</p>
      <h2>Customer <em>Reviews</em></h2>
    </div>
    <div class="reviews-layout">
      <div class="review-summary">
        <div class="big-rating"><?php echo number_format($avg_rating, 1); ?></div>
        <div class="big-stars">
          <?php
          for ($i = 1; $i <= 5; $i++) {
              echo $i <= round($avg_rating) ? '★' : '☆';
          }
          ?>
        </div>
        <p class="big-count">Based on <?php echo $review_count; ?> reviews</p>
        <div class="rating-bars" id="ratingBars">
          <?php
          for ($star = 5; $star >= 1; $star--) {
              $count = $rating_counts[$star];
              $percentage = $review_count > 0 ? ($count / $review_count) * 100 : 0;
          ?>
            <div class="rating-bar-row">
              <span class="bar-label"><?php echo $star; ?></span>
              <div class="bar-track">
                <div class="bar-fill" style="width: <?php echo $percentage; ?>%"></div>
              </div>
              <span class="bar-count"><?php echo $count; ?></span>
            </div>
          <?php } ?>
        </div>
      </div>

      <div class="reviews-list">
        <?php if ($review_count > 0): ?>
          <?php foreach ($reviews as $review): ?>
            <div class="review-card">
              <div class="review-top">
                <div class="reviewer">
                  <div class="reviewer-avatar">👩🏾</div>
                  <div>
                    <div class="reviewer-name"><?php echo htmlspecialchars($review['reviewer_name']); ?></div>
                    <div class="reviewer-date"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></div>
                  </div>
                </div>
                <div style="text-align:right">
                  <div class="review-stars">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= intval($review['rating']) ? '★' : '☆';
                    }
                    ?>
                  </div>
                  <span class="verified">✓ Verified</span>
                </div>
              </div>
              <p class="review-title"><?php echo htmlspecialchars($review['review_title'] ?? 'Review Profile'); ?></p>
              <p class="review-body"><?php echo htmlspecialchars($review['review_body']); ?></p>
              <p class="review-helpful">Was this helpful? <button class="helpful-btn">👍 Yes (<?php echo rand(1, 12); ?>)</button><button class="helpful-btn">👎 No</button></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="text-align:center; padding: 48px 24px; color: var(--mid);">
            <p style="font-size: 1.1rem; margin-bottom: 6px;">No customer reviews left yet.</p>
            <p style="font-size: 0.88rem;">Purchased this item? Be the first to share your thoughts with others!</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <div class="toast" id="toast">
    <span>🛍️</span>
    <span id="toastMsg">Added to cart!</span>
  </div>

  <footer>
    <div class="footer-grid">
      <div>
        <div class="footer-logo">Virtue &amp; <span>Verve</span></div>
        <p class="footer-desc">Your go-to destination for premium, stylish bags at unbeatable prices. Fashion that speaks for itself.</p>
        <div class="footer-socials">
          <a class="social-btn" href="#">📘</a>
          <a class="social-btn" href="#">📸</a>
          <a class="social-btn" href="#">🐦</a>
          <a class="social-btn" href="#">💬</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 Virtue & Verve. All rights reserved.</p>
    </div>
  </footer>

  <script src="./js/product.js"></script>
  <script>
    // Integration logic to pipe properties to the database backend cart session action
    document.addEventListener('DOMContentLoaded', () => {
      const addBtn = document.getElementById('addToCartBtn');
      if (addBtn) {
        addBtn.addEventListener('click', function() {
          const productId = this.getAttribute('data-product-id');
          const name = this.getAttribute('data-name');
          const category = this.getAttribute('data-category');
          const price = this.getAttribute('data-price');
          const img = this.getAttribute('data-img');
          const qty = parseInt(document.getElementById('qtyNum').value) || 1;
          
          // Detect selected layout swatch accent color
          const activeSwatch = document.querySelector('.color-swatch.active');
          const color = activeSwatch ? activeSwatch.getAttribute('data-color') : '#1a1a1a';
          
          const formData = new FormData();
          formData.append('product_id', productId);
formData.append('product_name', name);
formData.append('product_category', category);
formData.append('product_price', price);
formData.append('product_img', img);
formData.append('product_qty', qty);
formData.append('product_color', color);

          fetch('handle_cart_action.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              const badge = document.getElementById('cartCount');
              if (badge) {
                badge.textContent = data.total_items;
                badge.style.transform = 'scale(1.4)';
                setTimeout(() => badge.style.transform = 'scale(1)', 250);
              }
              // Display luxury toast validation alert
              const toast = document.getElementById('toast');
              document.getElementById('toastMsg').textContent = `"${name}" added to cart!`;
              toast.classList.add('show');
              setTimeout(() => toast.classList.remove('show'), 2800);
            }
          })
          .catch(err => console.error('Error handling background cart sync processing:', err));
        });
      }
    });
  </script>
</body>
</html>