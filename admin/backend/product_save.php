<?php
include '../../includes/db.php';
include '../../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id               = !empty($_POST['id']) ? intval($_POST['id']) : null;
    $existing_image   = $_POST['existing_image'] ?? '';
    
    $name             = trim($_POST['name'] ?? '');
    $sku              = trim($_POST['sku'] ?? '');
    $category_slug    = trim($_POST['category_slug'] ?? '');
    $price            = floatval($_POST['price'] ?? 0);
    $old_price        = (!empty($_POST['old_price'])) ? floatval($_POST['old_price']) : null;
    $stock            = intval($_POST['stock'] ?? 0);
    $description      = trim($_POST['description'] ?? '');
    $status           = trim($_POST['status'] ?? 'active');
    $badge            = trim($_POST['badge'] ?? '');
    $colors           = trim($_POST['colors'] ?? ''); // Captures comma-separated raw visual hex string

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

    if (empty($sku)) {
        $clean_name = strtoupper(preg_replace('/[^a-zA-Z]/', '', $name));
        $prefix = (strlen($clean_name) >= 3) ? substr($clean_name, 0, 3) : 'PROD';
        $sku = "VV-" . $prefix . "-" . rand(100, 999);
    }

    // TARGET STORAGE DIRECTORY
    $upload_dir = '../uploads/products/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

    // Pre-seed image fields with current files to prevent accidental deletion during metadata modification
    $img1 = $existing_image;
    $img2 = '';
    $img3 = '';

    if ($id !== null) {
        $img_check = $conn->prepare("SELECT image, image_2, image_3 FROM products WHERE id = ?");
        if ($img_check) {
            $img_check->bind_param("i", $id);
            $img_check->execute();
            $img_check->bind_result($db_img1, $db_img2, $db_img3);
            if ($img_check->fetch()) {
                $img1 = !empty($existing_image) ? $existing_image : $db_img1;
                $img2 = $db_img2;
                $img3 = $db_img3;
            }
            $img_check->close();
        }
    }

    // PROCESS THE HTML5 MULTIPLE SELECTION ARRAY CHANNEL (images[])
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $uploaded_count = count($_FILES['images']['name']);
        $slot_idx = 1;

        for ($i = 0; $i < $uploaded_count; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $file_tmp  = $_FILES['images']['tmp_name'][$i];
                $file_name = $_FILES['images']['name'][$i];
                $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (in_array($file_ext, $allowed_extensions)) {
                    $new_filename = time() . '_angle' . $slot_idx . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
                    if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                        if ($slot_idx === 1) {
                            if (!empty($img1) && file_exists($upload_dir . $img1) && $img1 !== $existing_image) {
                                @unlink($upload_dir . $img1);
                            }
                            $img1 = $new_filename;
                        } elseif ($slot_idx === 2) {
                            if (!empty($img2) && file_exists($upload_dir . $img2)) {
                                @unlink($upload_dir . $img2);
                            }
                            $img2 = $new_filename;
                        } elseif ($slot_idx === 3) {
                            if (!empty($img3) && file_exists($upload_dir . $img3)) {
                                @unlink($upload_dir . $img3);
                            }
                            $img3 = $new_filename;
                        }
                        $slot_idx++;
                        if ($slot_idx > 3) break; // Limit matching standard database table boundaries
                    }
                } else {
                    header("Location: ../products.php?status=error&message=Invalid+image+format+detected");
                    exit;
                }
            }
        }
    }

    // ─── DATABASE PREPARED STATEMENT WRITES ───
    if ($id === null) {
        $query = "INSERT INTO products (name, slug, sku, category_slug, price, old_price, stock, image, image_2, image_3, description, status, badge, colors) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            header("Location: ../products.php?status=error&message=" . urlencode("DB Error: " . $conn->error));
            exit;
        }

        $stmt->bind_param("ssssddisssssss", $name, $slug, $sku, $category_slug, $price, $old_price, $stock, $img1, $img2, $img3, $description, $status, $badge, $colors);

    } else {
        $query = "UPDATE products SET name = ?, slug = ?, sku = ?, category_slug = ?, price = ?, old_price = ?, stock = ?, image = ?, image_2 = ?, image_3 = ?, description = ?, status = ?, badge = ?, colors = ? WHERE id = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            header("Location: ../products.php?status=error&message=" . urlencode("DB Error: " . $conn->error));
            exit;
        }

        $stmt->bind_param("ssssddisssssssi", $name, $slug, $sku, $category_slug, $price, $old_price, $stock, $img1, $img2, $img3, $description, $status, $badge, $colors, $id);
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