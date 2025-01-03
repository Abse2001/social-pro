<?php
session_start();
include '../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];

    // Verify post belongs to user
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$post_id, $user_id])) {
        header("Location: ../index.php?success=post_deleted");
    } else {
        header("Location: ../index.php?error=delete_failed");
    }
} else {
    header("Location: ../index.php");
}
exit();
