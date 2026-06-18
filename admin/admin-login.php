<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login – Virtue & Verve</title>
  <link rel="stylesheet" href="./css/admin-login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500;600&display=swap"
    rel="stylesheet" />
</head>

<body>

  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <div class="page-wrap">

    <!-- LEFT PANEL -->
    <div class="left-panel">

      <div class="left-bg"></div>

      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>

      <span class="float-icon fi-1">👜</span>
      <span class="float-icon fi-2">👛</span>
      <span class="float-icon fi-3">🛍️</span>

      <div class="left-top">
        <a href="index.html" class="left-logo">
          Virtue & <span>Verve</span>
        </a>

        <div class="left-tag">
          <span class="pulse-dot"></span>
          Admin Portal
        </div>
      </div>

      <div class="left-middle">
        <h1 class="left-headline">
          Welcome Back,<br>
          <em>Admin</em>
        </h1>

        <p class="left-desc">
          Manage your store, track orders, update products and monitor performance.
        </p>

        <div class="left-features">

          <div class="feature-item">
            <div class="feature-icon-wrap">📦</div>
            <span>Real-time order tracking</span>
          </div>

          <div class="feature-item">
            <div class="feature-icon-wrap">📊</div>
            <span>Sales analytics</span>
          </div>

        </div>
      </div>

      <div class="left-bottom">

        <div class="left-stats">

          <div class="stat-item">
            <div class="stat-val">342</div>
            <div class="stat-label">Orders</div>
          </div>

          <div class="stat-item">
            <div class="stat-val">24</div>
            <div class="stat-label">Products</div>
          </div>

        </div>

        <p>© 2025 Virtue & Verve</p>

      </div>

    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">

      <div class="form-wrap">

        <a href="index.html" class="back-link">
          ← Back to Store
        </a>

        <p class="form-eyebrow">Admin Portal</p>

        <h1 class="form-title">
          Sign in to your<br>
          <em>Dashboard</em>
        </h1>

        <p class="form-sub">
          Enter your credentials to access the admin panel.
        </p>

        <form class="login-form" action="./backend/login_process.php" method="POST">

          <div class="field-group">
            <label class="field-label">Email Address</label>
            <div class="field-wrap">
              <span class="field-icon"><i class="fa-solid fa-envelope" style="color: #088178c4;"></i></span>
              <input type="email" name="email" class="field-input" placeholder="admin@virtueandverve.com" required />
            </div>
          </div>

          <div class="field-group">
            <label class="field-label">Password</label>
            <div class="field-wrap">
              <span class="field-icon"><i class="fa-solid fa-lock" style="color: #088178c4;"></i></span>
              <input type="password" name="password" class="field-input" placeholder="Enter your password" required />
            </div>
          </div>

          <button type="submit" class="submit-btn">
            Sign In to Dashboard
          </button>

        </form>

        <div class="divider-row">
          <div class="divider-line"></div>
          <span class="divider-text">Quick Access</span>
          <div class="divider-line"></div>
        </div>

        <div class="form-footer">
          Not an admin?
          <a href="index.html">Return to Store →</a>
        </div>

      </div>

    </div>

  </div>

  <!-- Error Popup Modal -->
  <div id="errorPopup" class="popup-overlay">
    <div class="popup-content">
      <div class="popup-header">
        <span class="popup-icon">⚠️</span>
        <h3>Login Failed</h3>
        <button class="popup-close" id="popupClose">&times;</button>
      </div>
      <div class="popup-body">
        <p id="popupMessage">Invalid credentials. Please try again.</p>
      </div>
      <div class="popup-footer">
        <button class="popup-btn" id="popupBtn">OK</button>
      </div>
    </div>
  </div>



  <script>
    const cursor = document.getElementById('cursor');
    const ring = document.getElementById('cursorRing');

    if (window.innerWidth > 540) {
      document.addEventListener('mousemove', e => {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';

        setTimeout(() => {
          ring.style.left = e.clientX + 'px';
          ring.style.top = e.clientY + 'px';
        }, 80);
      });
    }


    document.addEventListener('DOMContentLoaded', function() {
          const popup = document.getElementById('errorPopup');
          const popupMessage = document.getElementById('popupMessage');
          const closeBtn = document.getElementById('popupClose');
          const okBtn = document.getElementById('popupBtn');

          // Check if PHP set an error message
          <?php if (isset($_SESSION['error'])): ?>
            popupMessage.textContent = "<?php echo addslashes($_SESSION['error']); ?>";
            popup.classList.add('active');

            // Auto-hide after 5 seconds (optional)
            setTimeout(() => {
              popup.classList.remove('active');
            }, 5000);
          <?php endif; ?>

          // Close popup functions
          function closePopup() {
            popup.classList.remove('active');
          }

          closeBtn.addEventListener('click', closePopup);
          okBtn.addEventListener('click', closePopup);

          // Close if clicking outside the popup content
          popup.addEventListener('click', function(e) {
            if (e.target === popup) {
              closePopup();
            }
          });

          // Clear the error from session after displaying (prevents re-show on refresh)
          <?php unset($_SESSION['error']); ?>
  </script>






</body>

</html>