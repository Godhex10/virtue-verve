<?php
include '../includes/db.php';
include '../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id             = !empty($_POST['id']) ? intval($_POST['id']) : null;
    $name           = isset($_POST['name']) ? trim($_POST['name']) : '';
    $slug           = isset($_POST['slug']) ? trim($_POST['slug']) : '';
    $type           = isset($_POST['type']) ? trim($_POST['type']) : 'Everyday';
    $description    = isset($_POST['description']) ? trim($_POST['description']) : '';
    $status         = isset($_POST['status']) ? trim($_POST['status']) : 'active';
    $show_in_nav    = isset($_POST['show_in_nav']) ? intval($_POST['show_in_nav']) : 0;
    
    // Default the image filename string to whatever was already saved previously
    $image_filename = isset($_POST['existing_image']) ? trim($_POST['existing_image']) : '';

    if (empty($name)) {
        header("Location: ../categories.php?status=error&message=Name+is+required");
        exit;
    }

    if (empty($slug)) {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
    }

    // ─── FILE UPLOAD PROCESSING ENGINE ───
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['image']['tmp_name'];
        $file_original_name = $_FILES['image']['name'];
        $file_extension = strtolower(pathinfo($file_original_name, PATHINFO_EXTENSION));

        // Allowed image file types filter guard
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_extension, $allowed_extensions)) {
            // Generate clean unique filename: e.g., 1718912345_evening-bags.png
            $clean_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($slug));
            $new_filename = time() . '_' . $clean_slug . '.' . $file_extension;

            // Target path directory framework: updates relative to your admin folder root
            $upload_dir = '../uploads/categories/';
            
            // Auto-create directory folder recursively if it doesn't exist yet
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $dest_path = $upload_dir . $new_filename;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                // If a new image successfully uploaded, overwrite the target filename string variable
                $image_filename = $new_filename;
            }
        }
    }

    // ─── SQL PERSISTENCE LAYER MODE DETERMINATION ───
    if ($id === null) {
        // --- CREATE MODE (Includes Image Column Field Parameters) ---
        $query = "INSERT INTO categories (name, slug, type, description, status, show_in_nav, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssis", $name, $slug, $type, $description, $status, $show_in_nav, $image_filename);
    } else {
        // --- EDIT MODE (Updates Image Column Field Parameters) ---
        $query = "UPDATE categories SET name = ?, slug = ?, type = ?, description = ?, status = ?, show_in_nav = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssisi", $name, $slug, $type, $description, $status, $show_in_nav, $image_filename, $id);
    }

    if ($stmt->execute()) {
        header("Location: ../categories.php?status=success");
    } else {
        header("Location: ../categories.php?status=error&message=Database+save+failed");
    }
    
    $stmt->close();
} else {
    header("Location: ../categories.php");
}
exit;