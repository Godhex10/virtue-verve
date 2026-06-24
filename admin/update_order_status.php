<?php

include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $order_id = (int) $_POST['order_id'];
    $status   = trim($_POST['status']);

    $allowed = [
        'Pending',
        'Processing',
        'Delivered',
        'Cancelled'
    ];

    if (!in_array($status, $allowed)) {
        exit('Invalid status');
    }

    $stmt = $conn->prepare("
        UPDATE orders
        SET status = ?
        WHERE id = ?
    ");

    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}