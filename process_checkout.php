<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include your existing database connection file
include './includes/db.php';

// Check if cart is empty or direct access is attempted
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Check if form data was sent
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Fallback to 0 or NULL if user isn't logged in
    $user_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL);
    
    // Sanitize shipping delivery data strings
    $fullname = trim($_POST['fullname']);
    $phone    = trim($_POST['phone']);
    $email    = trim($_POST['email']);
    $address  = trim($_POST['address']);
    $city     = trim($_POST['city']);
    $state    = trim($_POST['state']);
    $notes    = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    // 1. Calculate matching order total sums directly from backend session payload 
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }
    
    // Check for matching promotional discount rules (syncing logic mirror from client side script)
    $discount = 0; 
    // If you need your promo active on checkout processing uncomment below line or logic:
    // $discount = $subtotal * 0.1; 
    
    $after_discount = $subtotal - $discount;
    $delivery_fee = ($after_discount >= 50000) ? 0 : 2500;
    $total_amount = $after_discount + $delivery_fee;

    // Start database safety transaction to verify everything works or roll back errors
    $conn->begin_transaction();

    try {
        // 2. Insert summary information data inside primary orders table row matrix
        $order_query = "INSERT INTO orders (user_id, fullname, phone, email, total_amount, status, payment_status, shipping_address, city, state, notes) 
                        VALUES (?, ?, ?, ?, ?, 'Pending', 'unpaid', ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("isssdssss", $user_id, $fullname, $phone, $email, $total_amount, $address, $city, $state, $notes);
        $stmt->execute();
        
        // Catch generated order auto_increment primary key ID
        $order_id = $conn->insert_id;
        $stmt->close();

        // 3. Loop item listings to map structure rows directly to order_items child table entries
        $item_query = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, color) VALUES (?, ?, ?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_query);

        foreach ($_SESSION['cart'] as $item) {
            $p_id   = $item['id'];
            $p_name = $item['name'];
            $p_prc  = $item['price'];
            $p_qty  = $item['qty'];
            $p_col  = $item['color']; // Stores selected hex code string context

            $item_stmt->bind_param("iisdis", $order_id, $p_id, $p_name, $p_prc, $p_qty, $p_col);
            $item_stmt->execute();
        }
        $item_stmt->close();

        // If everything executed successfully up to here, commit to the database
        $conn->commit();

        // 4. Wipe cart data session context safely now that the purchase order is recorded
        unset($_SESSION['cart']);

        // Redirect to a confirmation window page or home page with custom status indicator variables
        echo "<script>
                alert('Order Placed Successfully! Your Order ID is: " . $order_id . "');
                window.location.href = 'index.php';
              </script>";
        exit;

    } catch (Exception $e) {
        // Roll back changes if any query crashes
        $conn->rollback();
        die("Critical Checkout Processing Error: " . $e->getMessage());
    }
} else {
    header("Location: cart_2.php");
    exit;
}
?>