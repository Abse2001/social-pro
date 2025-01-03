<?php
session_start();
include '../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];

    // Check if like exists
    $check = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $check->execute([$post_id, $user_id]);
    
    if ($check->rowCount() > 0) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        $action = 'unliked';
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$post_id, $user_id]);
        $action = 'liked';
    }

    // Get updated like count
    $count = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
    $count->execute([$post_id]);
    $like_count = $count->fetch()['count'];

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'action' => $action,
        'likes' => $like_count
    ]);
    exit();
}
