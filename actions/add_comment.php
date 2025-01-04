<?php
session_start();
include '../config/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['content'])) {
    $content = trim($_POST['content']);
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$post_id, $user_id, $content])) {
        // Get the new comment data including username and profile picture
        $stmt = $pdo->prepare("
            SELECT comments.*, users.username, users.profile_picture 
            FROM comments 
            JOIN users ON comments.user_id = users.id 
            WHERE comments.id = LAST_INSERT_ID()
        ");
        $stmt->execute();
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'comment' => [
                'id' => $comment['id'],
                'content' => $comment['content'],
                'username' => $comment['username'],
                'user_id' => $comment['user_id'],
                'profile_picture' => $comment['profile_picture'] ?: 'assets/images/default-avatar.png',
                'created_at' => date('M d, Y H:i', strtotime($comment['created_at']))
            ],
            'current_user_id' => $_SESSION['user_id']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add comment']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Empty comment']);
}
exit();
