<?php
include '../../includes/db.php';
include '../../includes/auth.php';
requireAdmin();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);

    // FETCH ALL 3 FILE ASSET FILENAMES FOR DISK CLEANUP
    $img_query = "SELECT image, image_2, image_3 FROM products WHERE id = ? LIMIT 1";
    $img_stmt = $conn->prepare($img_query);
    if ($img_stmt) {
        $img_stmt->bind_param("i", $id);
        $img_stmt->execute();
        $res = $img_stmt->get_result();
        
        if ($res->num_rows === 1) {
            $product = $res->fetch_assoc();
            $upload_dir = '../../uploads/products/';
            
            // Loop array to unlink valid files
            foreach (['image', 'image_2', 'image_3'] as $slot) {
                if (!empty($product[$slot])) {
                    $target_path = $upload_dir . $product[$slot];
                    if (file_exists($target_path)) {
                        @unlink($target_path);
                    }
                }
            }
        }
        $img_stmt->close();
    }

    // CORE DATABASE RECORD DELETION
    $delete_query = "DELETE FROM products WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    
    if ($delete_stmt) {
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            header("Location: ../products.php?status=deleted");
            exit;
        } else {
            header("Location: ../products.php?status=error&message=Failed+to+delete+database+record");
            exit;
        }
        $delete_stmt->close();
    } else {
        header("Location: ../products.php?status=error&message=Database+preparation+failed");
        exit;
    }

} else {
    header("Location: ../products.php");
    exit;
}