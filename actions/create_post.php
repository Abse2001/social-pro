<?php
session_start();
include '../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['content'])) {
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $image_url = null;

    // Handle image upload
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['post_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            if (!file_exists('../assets/uploads/posts')) {
                mkdir('../assets/uploads/posts', 0777, true);
            }
            
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = 'assets/uploads/posts/' . $new_filename;
            
            if (move_uploaded_file($_FILES['post_image']['tmp_name'], '../' . $upload_path)) {
                $image_url = $upload_path;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image_url) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$user_id, $content, $image_url])) {
        header("Location: ../index.php");
    } else {
        header("Location: ../index.php?error=post_failed");
    }
} else {
    header("Location: ../index.php?error=empty_post");
}
exit();
