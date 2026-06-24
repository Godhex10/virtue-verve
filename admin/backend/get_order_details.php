<?php

include '../includes/db.php';

$order_id = (int) $_GET['id'];

$order_query = $conn->query("
    SELECT *
    FROM orders
    WHERE id = $order_id
");

$order = $order_query->fetch_assoc();

$items_query = $conn->query("
    SELECT *
    FROM order_items
    WHERE order_id = $order_id
");

$items = [];

while($row = $items_query->fetch_assoc()){
    $items[] = $row;
}

echo json_encode([
    'order' => $order,
    'items' => $items
]);