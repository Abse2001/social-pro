<?php
session_start();
include '../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    // Verify post belongs to user
    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if ($post && $post['user_id'] == $user_id) {
        $update = $pdo->prepare("UPDATE posts SET content = ? WHERE id = ? AND user_id = ?");
        if ($update->execute([$content, $post_id, $user_id])) {
            header("Location: ../index.php?success=post_updated");
        } else {
            header("Location: ../index.php?error=update_failed");
        }
    } else {
        header("Location: ../index.php?error=unauthorized");
    }
} else {
    header("Location: ../index.php");
}
exit();
