<?php
// includes/db.php

// 1. Database Credentials
$host     = 'localhost';
$db_user  = 'root';
$db_pass  = ''; // Default XAMPP password is empty
$db_name  = 'virtue';

// 2. Establish Connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// 3. Check Connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 4. Set Charset to match our database (ensures emojis and special characters work)
$conn->set_charset("utf8mb4");

// Note: We leave the PHP tag open if this is a pure PHP file to avoid accidental whitespace issues!