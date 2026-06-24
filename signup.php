<?php
// Start user session and pull active database configuration
session_start();
include './includes/db.php'; 

$error_msg = "";

// Handle standard HTML POST registration request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and clean inputs
    $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
    $lastname  = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
    $email     = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone     = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password  = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $terms     = isset($_POST['terms']) ? true : false;
    
    // Concatenate full name for database compatibility
    $full_name = trim("$firstname $lastname");

    // Server-side baseline validations
    if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
        $error_msg = 'Please fill out all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Invalid email address format.';
    } elseif (strlen($password) < 8) {
        $error_msg = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error_msg = 'Passwords do not match.';
    } elseif (!$terms) {
        $error_msg = 'Please accept our Terms & Conditions to continue.';
    } else {
        // FIXED: Changed table name from customers to users
        $check_query = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($check_query);
        
        if ($stmt === false) {
            $error_msg = 'Database error: ' . $conn->error;
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error_msg = 'An account with this email already exists.';
            }
            $stmt->close();
        }
    }

    // If there are no validation errors, proceed with registration
    if (empty($error_msg)) {
        // Securely hash the password prior to storage
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // FIXED: Changed target table name from customers to users
        $insert_query = "INSERT INTO users (name, email, password, phone, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_query);

        if ($stmt === false) {
            $error_msg = 'Database error during preparation: ' . $conn->error;
        } else {
            $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $phone);

            if ($stmt->execute()) {
                $customer_id = $stmt->insert_id;
                
                // Log user in automatically by populating session variables
                $_SESSION['customer_id']   = $customer_id;
                $_SESSION['customer_name'] = $full_name;
                $_SESSION['customer_email'] = $email;

                // Redirect user directly to the shop page upon success
                header("Location: index.php");
                exit;
            } else {
                $error_msg = 'Registration failed. Please try again later.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Account – Virtue & Verve</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --primary: #088178;
      --primary-light: #0aac9f;
      --primary-dark: #055e57;
      --primary-faint: #e6f5f4;
      --cream: #faf8f5;
      --dark: #1a1a1a;
      --mid: #4a4a4a;
      --light: #9a9a9a;
      --white: #ffffff;
      --gold: #c9a96e;
      --border: #ece9e4;
      --danger: #e05252;
      --success: #2ecc71;
    }

    html { scroll-behavior: smooth; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--cream);
      color: var(--dark);
      overflow-x: hidden;
      min-height: 100vh;
    }

    /* ── CURSOR ── */
    .cursor {
      width: 12px; height: 12px;
      background: var(--primary); border-radius: 50%;
      position: fixed; top: 0; left: 0;
      pointer-events: none; z-index: 9999;
      transition: transform 0.15s ease, background 0.2s;
      transform: translate(-50%, -50%);
    }

    /* ── NAV ── */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      padding: 20px 5%;
      display: flex; align-items: center; justify-content: space-between;
      background: rgba(250,248,245,0.97);
      backdrop-filter: blur(12px);
      box-shadow: 0 1px 30px rgba(8,129,120,0.08);
    }
    .logo {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.8rem; font-weight: 600;
      color: var(--primary); letter-spacing: 2px; text-decoration: none;
    }
    .logo span { color: var(--gold); }
    .nav-links { display: flex; gap: 36px; list-style: none; }
    .nav-links a {
      font-size: 0.8rem; font-weight: 500;
      text-transform: uppercase; letter-spacing: 2px;
      color: var(--mid); text-decoration: none;
      position: relative; transition: color 0.3s;
    }
    .nav-links a:hover { color: var(--primary); }
    .nav-cta {
      background: var(--primary); color: #fff !important;
      padding: 10px 24px !important; border-radius: 2px;
    }

    /* ── ERROR BANNER ── */
    .error-banner {
      background: #fdf2f2;
      border: 1px solid var(--danger);
      color: var(--danger);
      padding: 14px;
      font-size: 0.85rem;
      margin-bottom: 24px;
      border-radius: 2px;
    }

    /* ── MAIN LAYOUT ── */
    .page-wrap {
      display: grid;
      grid-template-columns: 1fr 1fr;
      min-height: 100vh;
    }

    /* ── LEFT PANEL ── */
    .left-panel {
      position: relative; overflow: hidden;
      background: var(--primary-dark);
      display: flex; flex-direction: column;
      justify-content: flex-end;
      padding: 60px;
      min-height: 100vh;
    }
    .left-bg-img {
      position: absolute; inset: 0;
      background: url('https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=900&q=80') center/cover no-repeat;
      filter: brightness(0.35) saturate(0.8);
    }
    .panel-content { position: relative; z-index: 2; }
    .panel-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,0.1); backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,0.15);
      color: rgba(255,255,255,0.85); padding: 8px 16px; border-radius: 100px;
      font-size: 0.72rem; letter-spacing: 2px; text-transform: uppercase;
      margin-bottom: 28px;
    }
    .panel-badge span { color: var(--gold); }
    .panel-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(2.4rem, 4vw, 3.6rem);
      font-weight: 300; color: white; line-height: 1.1; margin-bottom: 20px;
    }
    .panel-title em { font-style: italic; color: var(--gold); }
    .panel-desc {
      font-size: 0.9rem; color: rgba(255,255,255,0.65);
      line-height: 1.85; max-width: 400px; margin-bottom: 40px;
    }
    .panel-perks { display: flex; flex-direction: column; gap: 14px; }
    .perk { display: flex; align-items: center; gap: 14px; font-size: 0.85rem; color: rgba(255,255,255,0.8); }
    .perk-icon {
      width: 36px; height: 36px; border-radius: 50%;
      background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
      display: flex; align-items: center; justify-content: center;
    }

    /* ── RIGHT PANEL (FORM) ── */
    .right-panel {
      display: flex; flex-direction: column;
      justify-content: center; align-items: center;
      padding: 140px 60px 60px;
      background: var(--cream);
      position: relative; overflow: hidden;
    }
    .form-wrap { width: 100%; max-width: 440px; position: relative; z-index: 1; }
    .form-eyebrow {
      font-size: 0.72rem; font-weight: 500; text-transform: uppercase;
      letter-spacing: 4px; color: var(--primary); display: flex; align-items: center; gap: 10px; margin-bottom: 14px;
    }
    .form-eyebrow::before { content: ''; display: block; width: 28px; height: 1px; background: var(--primary); }
    .form-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 3.5vw, 2.8rem); font-weight: 300; color: var(--dark); line-height: 1.1; margin-bottom: 8px; }
    .form-title em { font-style: italic; color: var(--primary); }
    .form-sub { font-size: 0.87rem; color: var(--light); margin-bottom: 36px; }
    .form-sub a { color: var(--primary); text-decoration: none; font-weight: 500; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .field-group { margin-bottom: 18px; position: relative; }
    .field-label { display: block; font-size: 0.7rem; font-weight: 500; text-transform: uppercase; letter-spacing: 2px; color: var(--mid); margin-bottom: 8px; }
    .field-wrap { position: relative; }
    .field-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 1rem; color: var(--light); pointer-events: none; }
    .field-input {
      width: 100%; padding: 13px 20px 13px 42px;
      border: 1.5px solid var(--border); border-radius: 2px;
      font-size: 0.88rem; font-family: 'DM Sans', sans-serif;
      color: var(--dark); background: var(--white); outline: none;
      transition: border-color 0.25s, box-shadow 0.25s;
    }
    .field-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-faint); }

    .terms-check { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 24px; cursor: pointer; }
    .terms-text { font-size: 0.78rem; color: var(--mid); line-height: 1.6; }
    .terms-text a { color: var(--primary); text-decoration: none; }

    .submit-btn {
      width: 100%; background: var(--primary); color: white;
      border: none; padding: 16px; border-radius: 2px;
      font-size: 0.85rem; font-weight: 500; letter-spacing: 2.5px;
      text-transform: uppercase; cursor: pointer;
      transition: background 0.25s, transform 0.2s;
      box-shadow: 0 8px 28px rgba(8,129,120,0.28);
      margin-bottom: 20px;
    }
    .submit-btn:hover { background: var(--primary-dark); transform: translateY(-2px); }
    .signin-link { text-align: center; font-size: 0.82rem; color: var(--light); }
    .signin-link a { color: var(--primary); text-decoration: none; font-weight: 500; }

    @media (max-width: 960px) {
      .page-wrap { grid-template-columns: 1fr; }
      .left-panel { min-height: 250px; padding: 100px 40px 50px; }
      .right-panel { padding: 60px 40px; }
    }
  </style>
</head>
<body>

  <div class="cursor" id="cursor"></div>

  <nav>
    <a href="index.php" class="logo">Virtue &amp; <span>Verve</span></a>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="products.php">Shop</a></li>
      <li><a href="cart.php">Cart</a></li>
      <li><a href="signup.php" class="nav-cta">Sign Up</a></li>
    </ul>
  </nav>

  <div class="page-wrap">
    <div class="left-panel">
      <div class="left-bg-img"></div>
      <div class="panel-content">
        <div class="panel-badge">✦ <span>Virtue & Verve</span> Membership</div>
        <h2 class="panel-title">Join the <em>Verve</em><br>Community</h2>
        <p class="panel-desc">
          Create your free account and unlock a world of curated luxury bags, exclusive member deals, and a seamless shopping experience.
        </p>

        <div class="panel-perks">
          <div class="perk"><div class="perk-icon">📦</div><span>Track all your orders in one place</span></div>
          <div class="perk"><div class="perk-icon">🏷️</div><span>Exclusive member discounts & early access</span></div>
          <div class="perk"><div class="perk-icon">♡</div><span>Save your favourite bags to a wishlist</span></div>
        </div>
      </div>
    </div>

    <div class="right-panel">
      <div class="form-wrap">
        <p class="form-eyebrow">New Account</p>
        <h1 class="form-title">Create your <em>Account</em></h1>
        <p class="form-sub">Already have an account? <a href="login.php">Sign in here</a></p>

        <?php if (!empty($error_msg)): ?>
          <div class="error-banner">⚠️ <?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <form action="signup.php" method="POST">
          <div class="form-row">
            <div class="field-group">
              <label class="field-label" for="firstname">First Name</label>
              <div class="field-wrap">
                <span class="field-icon">👤</span>
                <input class="field-input" id="firstname" name="firstname" type="text" placeholder="Ada" required value="<?= isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : '' ?>"/>
              </div>
            </div>
            <div class="field-group">
              <label class="field-label" for="lastname">Last Name</label>
              <div class="field-wrap">
                <span class="field-icon">👤</span>
                <input class="field-input" id="lastname" name="lastname" type="text" placeholder="Okafor" required value="<?= isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : '' ?>"/>
              </div>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label" for="email">Email Address</label>
            <div class="field-wrap">
              <span class="field-icon">✉️</span>
              <input class="field-input" id="email" name="email" type="email" placeholder="ada@example.com" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"/>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label" for="phone">Phone Number</label>
            <div class="field-wrap">
              <span class="field-icon">📞</span>
              <input class="field-input" id="phone" name="phone" type="tel" placeholder="080 0000 0000" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>"/>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label" for="password">Password</label>
            <div class="field-wrap">
              <span class="field-icon">🔒</span>
              <input class="field-input" id="password" name="password" type="password" placeholder="Min. 8 characters" required/>
            </div>
          </div>

          <div class="field-group">
            <label class="field-label" for="confirm_password">Confirm Password</label>
            <div class="field-wrap">
              <span class="field-icon">🔒</span>
              <input class="field-input" id="confirm_password" name="confirm_password" type="password" placeholder="Re-enter your password" required/>
            </div>
          </div>

          <label class="terms-check">
            <input type="checkbox" name="terms" value="1" <?= isset($_POST['terms']) ? 'checked' : '' ?>/>
            <span class="terms-text">
              I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>.
            </span>
          </label>

          <button type="submit" class="submit-btn">
            Create My Account
          </button>

          <p class="signin-link">Already a member? <a href="login.php">Sign in to your account →</a></p>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Premium custom cursor tracking links
    const cursor = document.getElementById('cursor');
    document.addEventListener('mousemove', e => {
      cursor.style.left = e.clientX + 'px'; 
      cursor.style.top = e.clientY + 'px';
    });
  </script>
</body>
</html>