<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// If already logged in, skip this page
if (isAdminLoggedIn()) {
  header("Location: index.php");
  exit;
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email']);
  $password = trim($_POST['password']);

  if (!empty($email) && !empty($password)) {
    // Secure MySQLi prepared statement
    $sql = "SELECT id, password FROM admins WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        

        // Verify password hash
       if (password_verify($password, $admin['password'])) {
    $_SESSION['admin_id'] = $admin['id'];
    header("Location: index.php");
    exit;
}
      }
      // Generic error message for security (don't reveal if email or password was wrong)
      $error_message = "Invalid email address or password.";
      $stmt->close();
    } else {
      $error_message = "An error occurred. Please try again.";
    }
  } else {
    $error_message = "Please fill in all fields.";
  }
}
?>
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

        <!-- Display error if it exists -->
        <?php if (!empty($error_message)): ?>
          <div class="error-alert" style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
            <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error_message); ?>
          </div>
        <?php endif; ?>

        <form class="login-form" action="./login.php" method="POST">

          <div class="field-group">
            <label class="field-label">Email Address</label>
            <div class="field-wrap">
              <span class="field-icon"><i class="fa-solid fa-envelope" style="color: #088178c4;"></i></span>
              <!-- Added PHP to persist the email input value on failed attempts -->
              <input type="email" name="email" class="field-input" placeholder="admin@virtueandverve.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
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
  </script>
</body>
</html>