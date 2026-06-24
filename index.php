<?php
// If db.php is in an includes folder right next to index.php:
include './includes/db.php'; 

// (If you are inside an admin subfolder, it would be: include '../includes/db.php';)
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Virtue & Verve – Premium Bags Collection</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght=0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./css/styles.css">
</head>
<body>

  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <div class="mobile-nav" id="mobileNav">
    <span class="close-nav" id="closeNav">✕</span>
    <a href="#hero">Home</a>
    <a href="#categories">Categories</a>
    <a href="#featured">Collection</a>
    <a href="#why">Why Us</a>
    <a href="./shop.html" class="btn-primary" style="font-family:'DM Sans';font-size:0.82rem;letter-spacing:2px;text-transform:uppercase;">Shop Now</a>
  </div>

  <nav id="navbar">
    <a href="#" class="logo">Virtue &amp; <span>Verve</span></a>
    <ul class="nav-links">
      <li><a href="#categories">Categories</a></li>
      <li><a href="#featured">Collection</a></li>
      <li><a href="#why">About</a></li>
      <li><a href="#testimonials">Reviews</a></li>
      <li><a href="./shop.html" class="nav-cta">Shop Now</a></li>
    </ul>
    <div class="hamburger" id="hamburger">
      <span></span><span></span><span></span>
    </div>
  </nav>

  <section id="hero">
    <div class="hero-bg"></div>
    <div class="hero-blob"></div>
    <div class="hero-content">
      <p class="hero-eyebrow">New Collection 2026</p>
      <h1>Carry <em>Style</em>,<br>Carry <em>Grace</em></h1>
      <p class="hero-desc">Discover our curated selection of premium handbags, totes, clutches and more — crafted for the woman who knows her worth.</p>
      <div class="hero-actions">
        <a href="./shop.html" class="btn-primary">Shop the Collection</a>
        <a href="#categories" class="btn-ghost">Explore Categories</a>
      </div>
      <div class="hero-stats">
        <div>
          <div class="stat-num">500+</div>
          <div class="stat-label">Bag Styles</div>
        </div>
        <div>
          <div class="stat-num">2K+</div>
          <div class="stat-label">Happy Clients</div>
        </div>
        <div>
          <div class="stat-num">4.9★</div>
          <div class="stat-label">Avg Rating</div>
        </div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-img-frame">
        <img src="https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&q=80" alt="Luxury Bag"/>
        <div class="hero-badge">
          <div class="badge-text">
            <strong>Free Delivery</strong>
            <span>On orders above ₦20,000</span>
          </div>
        </div>
        <div class="hero-tag">✦ Premium Quality</div>
      </div>
    </div>
  </section>

  <?php
  // Fetch active categories from the database
  $marquee_categories = [];
  if (isset($conn)) {
      $cat_res = $conn->query("SELECT name FROM categories WHERE status = 'active' ORDER BY name ASC");
      if (!$cat_res) {
          $cat_res = $conn->query("SELECT name FROM categories ORDER BY name ASC");
      }
      
      if ($cat_res && $cat_res->num_rows > 0) {
          while ($row = $cat_res->fetch_assoc()) {
              $marquee_categories[] = $row['name'];
          }
      }
  }
  ?>

  <div class="marquee-wrap">
    <div class="marquee-track">
      <?php if (!empty($marquee_categories)): ?>
        <?php foreach ($marquee_categories as $category): ?>
          <span><?php echo htmlspecialchars($category); ?></span><span class="dot">✦</span>
        <?php endforeach; ?>
        <?php foreach ($marquee_categories as $category): ?>
          <span><?php echo htmlspecialchars($category); ?></span><span class="dot">✦</span>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php
  // ── FETCH CATEGORIES WITH PRODUCT COUNTS FROM DATABASE ──────────────────
  $categories_data = [];
  $db_conn = isset($conn) ? $conn : null;

  if ($db_conn) {
      $cat_grid_query = "SELECT c.name, c.slug, c.image, COUNT(p.id) AS product_count 
                         FROM categories c 
                         LEFT JOIN products p ON c.slug = p.category_slug 
                         GROUP BY c.id, c.name, c.slug, c.image 
                         ORDER BY product_count DESC";
                         
      try {
          $res = mysqli_query($db_conn, $cat_grid_query);
          if ($res) {
              while ($row = mysqli_fetch_assoc($res)) {
                  $categories_data[] = $row;
              }
          }
      } catch (Exception $e) {
          // Fail safely
      }
  }
  ?>

  <section id="categories">
    <div class="section-header reveal">
      <p class="section-eyebrow">Browse by Style</p>
      <h2>Shop by <em>Category</em></h2>
      <p class="section-sub">Find the perfect bag for every occasion</p>
    </div>
    
    <div class="categories-grid">
      <?php if (!empty($categories_data)): ?>
        <?php foreach ($categories_data as $cat): 
          $cat_image = !empty($cat['image']) ? $cat['image'] : 'https://images.unsplash.com/photo-1591561954557-26941169b49e?w=400&q=80';
          $cat_slug  = !empty($cat['slug']) ? $cat['slug'] : '';
        ?>
          <div class="cat-card reveal" onclick="window.location.href='./shop.html?cat=<?php echo urlencode($cat_slug); ?>'" style="cursor: pointer;">
            <img src="./admin/uploads/categories/<?php echo htmlspecialchars($cat_image); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>"/>
            <div class="cat-overlay">
              <div class="cat-name"><?php echo htmlspecialchars($cat['name']); ?></div>
              <div class="cat-count"><?php echo number_format($cat['product_count']); ?> Products</div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    
    <div class="categories-cta reveal">
      <a href="./shop.html" class="btn-primary">View All Categories →</a>
    </div>
  </section>

  <?php
  // ── FETCH FEATURED PRODUCTS FROM DATABASE ─────────────────
  $featured_products = [];

  if ($db_conn) {
      $prod_query = "SELECT * FROM products 
                     WHERE badge = 'New' OR status = 'new' OR status = 'active' 
                     ORDER BY id DESC 
                     LIMIT 6";
                     
      try {
          $res = mysqli_query($db_conn, $prod_query);
          if ($res) {
              while ($row = mysqli_fetch_assoc($res)) {
                  $featured_products[] = $row;
              }
          }
      } catch (Exception $e) {
          // Fail safely
      }
  }
  ?>

  <section id="featured">
    <div class="section-header reveal">
      <p class="section-eyebrow">Handpicked for You</p>
      <h2>Featured <em>Collection</em></h2>
      <p class="section-sub">Our most loved pieces this season</p>
    </div>
    
    <div class="products-grid">
      <?php if (!empty($featured_products)): ?>
        <?php foreach ($featured_products as $prod): 
          $prod_img = !empty($prod['image']) ? $prod['image'] : 'placeholder.jpg';
          $prod_type = !empty($prod['category_slug']) ? ucfirst($prod['category_slug']) : 'Luxury Bag';
          $prod_name = $prod['name'];
          $prod_price = isset($prod['price']) ? $prod['price'] : 0;
          $prod_slug = $prod['slug']; // Dynamically loads the clean alphanumeric string code identifier
          
          // CRITICAL REFACTOR: Converted from ID routing parameter to unique dynamic slug identifier mapping
          $details_url = "product-details.php?slug=" . urlencode($prod_slug);
        ?>
          <div class="product-card reveal">
            <div class="product-img">
              <a href="<?php echo $details_url; ?>">
                <img src="./admin/uploads/products/<?php echo htmlspecialchars($prod_img); ?>" alt="<?php echo htmlspecialchars($prod_name); ?>"/>
              </a>
              <?php if (!empty($prod['badge'])): ?>
                <span class="product-badge"><?php echo htmlspecialchars($prod['badge']); ?></span>
              <?php endif; ?>
              <div class="product-actions">
                <button class="action-btn" title="Wishlist">♡</button>
                <a href="<?php echo $details_url; ?>" class="action-btn" title="Quick View" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">👁</a>
              </div>
            </div>
            
            <div class="product-info">
              <p class="product-type"><?php echo htmlspecialchars($prod_type); ?></p>
              <h3 class="product-name">
                <a href="<?php echo $details_url; ?>" style="color: inherit; text-decoration: none;">
                  <?php echo htmlspecialchars($prod_name); ?>
                </a>
              </h3>
              <div class="product-footer">
                <span class="product-price">₦<?php echo number_format($prod_price, 0, '.', ','); ?></span>
                <a href="<?php echo $details_url; ?>" class="buy-btn" style="text-decoration: none;">Buy Now</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    
    <div class="products-cta reveal">
      <a href="./products.php" class="btn-primary">View All Products →</a>
    </div>
  </section>

  <section id="why">
    <div class="section-header reveal">
      <p class="section-eyebrow">Why Choose Us</p>
      <h2>Quality You Can <em>Trust</em></h2>
      <p class="section-sub" style="color:rgba(255,255,255,0.5)">We go above and beyond for every customer</p>
    </div>
    <div class="features-grid">
      <div class="feature-card reveal">
        <span class="feature-num">01</span>
        <span class="feature-icon">✨</span>
        <h3 class="feature-title">Premium Quality</h3>
        <p class="feature-desc">Every bag is carefully selected and quality-checked before reaching your hands.</p>
      </div>
      <div class="feature-card reveal">
        <span class="feature-num">02</span>
        <span class="feature-icon">🚚</span>
        <h3 class="feature-title">Fast Delivery</h3>
        <p class="feature-desc">Quick & reliable shipping nationwide. Track your order every step of the way.</p>
      </div>
      <div class="feature-card reveal">
        <span class="feature-num">03</span>
        <span class="feature-icon">💎</span>
        <h3 class="feature-title">Best Prices</h3>
        <p class="feature-desc">Luxury looks without the luxury price tag. Affordable fashion for every woman.</p>
      </div>
      <div class="feature-card reveal">
        <span class="feature-num">04</span>
        <span class="feature-icon">🔄</span>
        <h3 class="feature-title">Easy Returns</h3>
        <p class="feature-desc">Not satisfied? We offer a hassle-free return and exchange policy.</p>
      </div>
    </div>
  </section>

  <section id="testimonials">
    <div class="section-header reveal">
      <p class="section-eyebrow">Happy Customers</p>
      <h2>What Our <em>Clients</em> Say</h2>
      <p class="section-sub">Real reviews from real bag lovers</p>
    </div>
    <div class="testimonials-track">
      <div class="testi-card reveal">
        <div class="testi-stars">★★★★★</div>
        <div class="testi-quote">"</div>
        <p class="testi-text">I ordered the Milano bag and it arrived within 2 days. The quality is absolutely amazing — everyone keeps asking where I got it!</p>
        <div class="testi-author">
          <div class="testi-avatar">👩🏾</div>
          <div>
            <div class="testi-name">Adaeze Okafor</div>
            <div class="testi-location">Lagos, Nigeria</div>
          </div>
        </div>
      </div>
      <div class="testi-card reveal">
        <div class="testi-stars">★★★★★</div>
        <div class="testi-quote">"</div>
        <p class="testi-text">Best bag vendor I've ever shopped from online. The prices are fair and the bags look exactly like the pictures. Highly recommend!</p>
        <div class="testi-author">
          <div class="testi-avatar">👩🏽</div>
          <div>
            <div class="testi-name">Fatima Aliyu</div>
            <div class="testi-location">Abuja, Nigeria</div>
          </div>
        </div>
      </div>
      <div class="testi-card reveal">
        <div class="testi-stars">★★★★★</div>
        <div class="testi-quote">"</div>
        <p class="testi-text">I've bought 4 bags already and I keep coming back. The customer service is top notch and the packaging is so beautiful!</p>
        <div class="testi-author">
          <div class="testi-avatar">👩🏿</div>
          <div>
            <div class="testi-name">Blessing Eze</div>
            <div class="testi-location">Port Harcourt, Nigeria</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="cta-banner">
    <p class="cta-eyebrow">Limited Time Offer</p>
    <h2 class="cta-title">Your Dream Bag is<br><em>Waiting for You</em></h2>
    <p class="cta-sub">Shop our full collection — new arrivals added weekly</p>
    <a href="./shop.html" class="btn-white">Shop the Full Collection</a>
  </section>

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
          <li><a href="./shop.html">New Arrivals</a></li>
          <li><a href="./shop.html">Handbags</a></li>
          <li><a href="./shop.html">Tote Bags</a></li>
          <li><a href="./shop.html">Clutches</a></li>
          <li><a href="./shop.html">Backpacks</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="#">Track Order</a></li>
          <li><a href="#">Returns & Exchange</a></li>
          <li><a href="#">Contact Us</a></li>
          <li><a href="#">FAQs</a></li>
          <li><a href="#">Size Guide</a></li>
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
    // Custom cursor
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
    document.querySelectorAll('a,button,.cat-card').forEach(el => {
      el.addEventListener('mouseenter', () => {
        cursor.style.transform = 'translate(-50%,-50%) scale(1.8)';
        cursor.style.background = 'var(--gold)';
      });
      el.addEventListener('mouseleave', () => {
        cursor.style.transform = 'translate(-50%,-50%) scale(1)';
        cursor.style.background = 'var(--primary)';
      });
    });

    // Navbar scroll
    const nav = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });

    // Mobile nav
    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('mobileNav').classList.add('open');
    });
    document.getElementById('closeNav').addEventListener('click', () => {
      document.getElementById('mobileNav').classList.remove('open');
    });
    document.querySelectorAll('.mobile-nav a').forEach(a => {
      a.addEventListener('click', () => {
        document.getElementById('mobileNav').classList.remove('open');
      });
    });

    // Scroll reveal
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
          setTimeout(() => {
            entry.target.classList.add('visible');
          }, 80);
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
  </script>
</body>
</html>