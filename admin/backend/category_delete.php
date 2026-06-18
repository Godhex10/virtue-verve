<?php
// 1. Include core database configuration and admin protection layers
// Going up two levels (../..) because this file lives inside admin/backend/
include '../../includes/db.php';
include '../../includes/auth.php';
requireAdmin();

// 2. Ensure an ID is passed through the URL query parameter
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);

    // ─── OPTIONAL BUT PROFESSIONAL: CLEAN UP DISK IMAGES ───
    // First, look up the category record to check if an image file exists
    $img_query = "SELECT image FROM categories WHERE id = ? LIMIT 1";
    $img_stmt = $conn->prepare($img_query);
    if ($img_stmt) {
        $img_stmt->bind_param("i", $id);
        $img_stmt->execute();
        $res = $img_stmt->get_result();
        
        if ($res->num_rows === 1) {
            $category = $res->fetch_assoc();
            $image_file = $category['image'];
            
            // If the record contains a filename, check if the file exists on your XAMPP server disk
            if (!empty($image_file)) {
                $target_path = '../../uploads/categories/' . $image_file;
                if (file_exists($target_path)) {
                    unlink($target_path); // Erases the image file from the folder entirely
                }
            }
        }
        $img_stmt->close();
    }

    // ─── CORE DATABASE RECORD DELETION ───
    // Prepared statement strictly guards against URL parameter ID injection modifications
    $delete_query = "DELETE FROM categories WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    
    if ($delete_stmt) {
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            // Success redirect back to your frontend categories manager panel
            header("Location: ../categories.php?status=deleted");
            exit;
        } else {
            header("Location: ../categories.php?status=error&message=Failed+to+delete+database+record");
            exit;
        }
        $delete_stmt->close();
    } else {
        header("Location: ../categories.php?status=error&message=Database+preparation+failed");
        exit;
    }

} else {
    // Kick direct URL manual hit manipulation bypass attempts right back to the UI panel
    header("Location: ../categories.php");
    exit;
}