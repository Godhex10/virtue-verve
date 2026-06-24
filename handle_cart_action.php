<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit;
}

// Receive data from product-details.php
$product_id       = isset($_POST['product_id']) ? $_POST['product_id'] : '';
$product_name     = isset($_POST['product_name']) ? $_POST['product_name'] : '';
$product_price    = isset($_POST['product_price']) ? floatval($_POST['product_price']) : 0;
$product_category = isset($_POST['product_category']) ? $_POST['product_category'] : '';
$product_img      = isset($_POST['product_img']) ? $_POST['product_img'] : '';
$product_qty      = isset($_POST['product_qty']) ? intval($_POST['product_qty']) : 1;
$product_color    = isset($_POST['product_color']) ? $_POST['product_color'] : '#000000';

if (empty($product_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID missing'
    ]);
    exit;
}

// Unique key for same product with different colors
$cart_key = $product_id . '_' . str_replace('#', '', $product_color);

if (isset($_SESSION['cart'][$cart_key])) {

    $_SESSION['cart'][$cart_key]['qty'] += $product_qty;

} else {

    $_SESSION['cart'][$cart_key] = [
        'id'       => $product_id,
        'name'     => $product_name,
        'price'    => $product_price,
        'category' => $product_category,
        'img'      => $product_img,
        'qty'      => $product_qty,
        'color'    => $product_color
    ];
}

// Calculate total cart quantity
$total_items = 0;

foreach ($_SESSION['cart'] as $item) {
    $total_items += $item['qty'];
}

echo json_encode([
    'success' => true,
    'message' => 'Added to cart',
    'total_items' => $total_items
]);

exit;
?>