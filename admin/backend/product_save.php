<?php
include '../../includes/db.php';
include '../../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id               = !empty($_POST['id']) ? intval($_POST['id']) : null;
    $existing_image   = $_POST['existing_image'] ?? '';
    $existing_image_2 = $_POST['existing_image_2'] ?? '';
    $existing_image_3 = $_POST['existing_image_3'] ?? '';
    
    $name             = trim($_POST['name'] ?? '');
    $sku              = trim($_POST['sku'] ?? '');
    $category_slug    = trim($_POST['category_slug'] ?? '');
    $price            = floatval($_POST['price'] ?? 0);
    $old_price        = (!empty($_POST['old_price'])) ? floatval($_POST['old_price']) : null;
    $stock            = intval($_POST['stock'] ?? 0);
    $description      = trim($_POST['description'] ?? '');
    $status           = trim($_POST['status'] ?? 'active');
    $badge            = trim($_POST['badge'] ?? '');

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

    if (empty($sku)) {
        $clean_name = strtoupper(preg_replace('/[^a-zA-Z]/', '', $name));
        $prefix = (strlen($clean_name) >= 3) ? substr($clean_name, 0, 3) : 'PROD';
        $sku = "VV-" . $prefix . "-" . rand(100, 999);
    }

    // TARGET STORAGE DIRECTORY
    $upload_dir = '../../uploads/products/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

    // IMAGE PROCESSING HELPER FUNCTION
    function processImageUpload($file_key, $existing_filename, $upload_dir, $allowed_extensions) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES[$file_key]['tmp_name'];
            $file_name = $_FILES[$file_key]['name'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed_extensions)) {
                $new_filename = time() . '_' . $file_key . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
                if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                    // Erase old image file asset from disk if updating
                    if (!empty($existing_filename) && file_exists($upload_dir . $existing_filename)) {
                        @unlink($upload_dir . $existing_filename);
                    }
                    return $new_filename;
                }
            } else {
                header("Location: ../products.php?status=error&message=Invalid+format+on+" . $file_key);
                exit;
            }
        }
        return $existing_filename; // Keep original asset token
    }

    // RUN THE FILE SELECTION CHANNELS
    $img1 = processImageUpload('image', $existing_image, $upload_dir, $allowed_extensions);
    $img2 = processImageUpload('image_2', $existing_image_2, $upload_dir, $allowed_extensions);
    $img3 = processImageUpload('image_3', $existing_image_3, $upload_dir, $allowed_extensions);

    // ─── DATABASE PREPARED STATEMENT WRITES ───
    if ($id === null) {
        $query = "INSERT INTO products (name, slug, sku, category_slug, price, old_price, stock, image, image_2, image_3, description, status, badge) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            header("Location: ../products.php?status=error&message=" . urlencode("DB Error: " . $conn->error));
            exit;
        }

        $stmt->bind_param("ssssddissssss", $name, $slug, $sku, $category_slug, $price, $old_price, $stock, $img1, $img2, $img3, $description, $status, $badge);

    } else {
        $query = "UPDATE products SET name = ?, slug = ?, sku = ?, category_slug = ?, price = ?, old_price = ?, stock = ?, image = ?, image_2 = ?, image_3 = ?, description = ?, status = ?, badge = ? WHERE id = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            header("Location: ../products.php?status=error&message=" . urlencode("DB Error: " . $conn->error));
            exit;
        }

        $stmt->bind_param("ssssddissssssi", $name, $slug, $sku, $category_slug, $price, $old_price, $stock, $img1, $img2, $img3, $description, $status, $badge, $id);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: ../products.php?status=success");
        exit;
    } else {
        $error_msg = $stmt->error;
        $stmt->close();
        header("Location: ../products.php?status=error&message=" . urlencode("Execution Failed: " . $error_msg));
        exit;
    }

} else {
    header("Location: ../products.php");
    exit;
}