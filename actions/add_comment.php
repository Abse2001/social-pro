<?php
session_start();
include '../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['content'])) {
    $content = trim($_POST['content']);
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$post_id, $user_id, $content])) {
        header("Location: ../index.php?success=comment_added");
    } else {
        header("Location: ../index.php?error=comment_failed");
    }
} else {
    header("Location: ../index.php?error=empty_comment");
}
exit();
