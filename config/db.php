<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'virtue');

// Create connection
function getConnection() {
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    return $connection;
}

// Get the connection
$connection = getConnection();
?>