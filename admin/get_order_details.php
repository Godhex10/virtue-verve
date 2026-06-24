<?php

include '../includes/db.php';

$order_id = (int) $_GET['id'];

$order = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT * FROM orders WHERE id = $order_id"
    )
);

$items = [];

$item_result = mysqli_query(
    $conn,
    "SELECT * FROM order_items WHERE order_id = $order_id"
);

while ($row = mysqli_fetch_assoc($item_result)) {
    $items[] = $row;
}

echo json_encode([
    'order' => $order,
    'items' => $items
]);