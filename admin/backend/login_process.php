<?php
// login_process.php
session_start();
require_once './config/db.php'; // Our MySQLi config file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize input
    $email = mysqli_real_escape_string($connection, trim($_POST['email']));
    $password = trim($_POST['password']);
    
    // Validate
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields";
        header("Location: ../login.php");
        exit();
    }
    
    // Query database
    $query = "SELECT id, username, email, password FROM admins WHERE email = '$email'";
    $result = mysqli_query($connection, $query);
    
    if ($result && mysqli_num_rows($result) === 1) {
        $admin = mysqli_fetch_assoc($result);
        
        // Plain text comparison for now (we'll upgrade this later)
        if ($password === $admin['password']) {
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['logged_in'] = true;
            
            // Redirect to dashboard
            header("Location: ../index.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid password";
            header("Location: ../admin-login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No account found with that email";
        header("Location: ../admin-login.php");
        exit();
    }
} else {
    header("Location: ../admin-login.php");
    exit();
}
?>