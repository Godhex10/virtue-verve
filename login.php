<?php
session_start();
include './includes/db.php';

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $error_msg = "Please enter both email and password.";
    } else {
        // Look up customer row by indexed email attribute
        $query = "SELECT id, name, password FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $customer = $result->fetch_assoc();
            
            // Verify password hash
            if (password_verify($password, $customer['password'])) {
                $_SESSION['customer_id']   = $customer['id'];
                $_SESSION['customer_name'] = $customer['name'];
                $_SESSION['customer_email'] = $email;

                header("Location: index.php");
                exit;
            } else {
                $error_msg = "Incorrect password. Please try again.";
            }
        } else {
            $error_msg = "No account found with that email address.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Customer Login – Virtue & Verve</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    :root {
      --primary: #088178;
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
    }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--cream);
      color: var(--dark);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .cursor {
      width: 12px; height: 12px; background: var(--primary); border-radius: 50%;
      position: fixed; pointer-events: none; z-index: 9999; transform: translate(-50%, -50%);
      transition: transform 0.15s ease;
    }
    nav {
      padding: 20px 5%; display: flex; align-items: center; justify-content: space-between;
      background: rgba(250,248,245,0.97); backdrop-filter: blur(12px);
      box-shadow: 0 1px 30px rgba(8,129,120,0.08); position: fixed; top:0; left:0; right:0; z-index:100;
    }
    .logo { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; font-weight: 600; color: var(--primary); text-decoration: none; letter-spacing: 2px;}
    .logo span { color: var(--gold); }
    .login-container {
      margin: auto; width: 100%; max-width: 420px; padding: 40px;
      background: var(--white); border: 1px solid var(--border);
      box-shadow: 0 20px 50px rgba(8,129,120,0.04); border-radius: 2px;
      margin-top: 140px; margin-bottom: 60px;
    }
    .title { font-family: 'Cormorant Garamond', serif; font-size: 2.2rem; font-weight: 300; text-align: center; margin-bottom: 8px; }
    .title em { color: var(--primary); font-style: italic; }
    .subtitle { font-size: 0.85rem; color: var(--light); text-align: center; margin-bottom: 32px; }
    .subtitle a { color: var(--primary); text-decoration: none; font-weight: 500; }
    .error-banner { background: #fdf2f2; border: 1px solid var(--danger); color: var(--danger); padding: 12px; font-size: 0.8rem; margin-bottom: 20px; border-radius: 2px; }
    .field-group { margin-bottom: 20px; }
    .field-label { display: block; font-size: 0.7rem; font-weight: 500; text-transform: uppercase; letter-spacing: 2px; color: var(--mid); margin-bottom: 8px; }
    .field-wrap { position: relative; }
    .field-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--light); }
    .field-input { width: 100%; padding: 13px 14px 13px 42px; border: 1.5px solid var(--border); border-radius: 2px; font-size: 0.88rem; outline: none; transition: all 0.25s; font-family: 'DM Sans'; }
    .field-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-faint); }
    .submit-btn { width: 100%; background: var(--primary); color: white; border: none; padding: 16px; border-radius: 2px; font-size: 0.85rem; font-weight: 500; letter-spacing: 2px; text-transform: uppercase; cursor: pointer; margin-top: 10px; box-shadow: 0 8px 24px rgba(8,129,120,0.2); transition: all 0.25s; }
    .submit-btn:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 12px 32px rgba(8,129,120,0.3); }
  </style>
</head>
<body>

  <div class="cursor" id="cursor"></div>

  <nav>
    <a href="index.php" class="logo">Virtue &amp; <span>Verve</span></a>
  </nav>

  <div class="login-container">
    <h1 class="title">Welcome <em>Back</em></h1>
    <p class="subtitle">Don't have an account? <a href="signup.php">Create one here</a></p>

    <?php if(!empty($error_msg)): ?>
      <div class="error-banner">⚠️ <?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <div class="field-group">
        <label class="field-label">Email Address</label>
        <div class="field-wrap">
          <span class="field-icon">✉️</span>
          <input class="field-input" type="email" name="email" required placeholder="your@email.com"/>
        </div>
      </div>

      <div class="field-group">
        <label class="field-label">Password</label>
        <div class="field-wrap">
          <span class="field-icon">🔒</span>
          <input class="field-input" type="password" name="password" required placeholder="Enter password"/>
        </div>
      </div>

      <button type="submit" class="submit-btn">Sign In</button>
    </form>
  </div>

  <script>
    const cursor = document.getElementById('cursor');
    document.addEventListener('mousemove', e => {
      cursor.style.left = e.clientX + 'px'; cursor.style.top = e.clientY + 'px';
    });
  </script>
</body>
</html>