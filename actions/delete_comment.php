<?php
session_start();
include '../config/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['comment_id'])) {
    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];

    // Only allow users to delete their own comments
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    
    if ($stmt->execute([$comment_id, $user_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete comment']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
