<?php
// includes/auth.php

// 1. Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if an admin is logged in
 * @return bool
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Enforce admin authentication
 * Redirects to login page if the admin session is missing
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        // Redirect to the admin login page
        // Using a relative path that works inside the /admin/ folder
        header("Location: login.php");
        exit;
    }
}

/**
 * Check if a customer (user) is logged in
 * @return bool
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Enforce customer authentication
 * Redirects to the main login page if the customer session is missing
 */
function requireUser() {
    if (!isUserLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}