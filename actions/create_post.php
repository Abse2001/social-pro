<?php
/**
 * Post Creation Handler
 * 
 * Manages new post creation including:
 * - Text content processing
 * - Image upload handling
 * - File validation
 * - Database insertion
 * - Directory creation if needed
 */

// Initialize session for user authentication
session_start();

// Include database connection configuration
include '../config/db_connection.php';

// Verify user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Process post creation if POST request and content exists
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['content'])) {
    // Sanitize and prepare post content
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $image_url = null;

    // Handle image upload if present
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
        // Define allowed image types
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['post_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Validate file type
        if (in_array(strtolower($filetype), $allowed)) {
            // Create upload directory if it doesn't exist
            if (!file_exists('../assets/uploads/posts')) {
                mkdir('../assets/uploads/posts', 0777, true);
            }
            
            // Generate unique filename
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = 'assets/uploads/posts/' . $new_filename;
            
            // Move uploaded file to destination
            if (move_uploaded_file($_FILES['post_image']['tmp_name'], '../' . $upload_path)) {
                $image_url = $upload_path;
            }
        }
    }

    // Prepare and execute database insert
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image_url) VALUES (?, ?, ?)");
    
    // Handle database operation result
    if ($stmt->execute([$user_id, $content, $image_url])) {
        header("Location: ../index.php");
    } else {
        header("Location: ../index.php?error=post_failed");
    }
} else {
    // Handle empty post content
    header("Location: ../index.php?error=empty_post");
}

// End script execution
exit();
