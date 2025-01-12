<?php
/**
 * Comment Addition Handler
 * 
 * Handles adding new comments to posts with:
 * - User authentication check
 * - Input validation
 * - Database insertion
 * - JSON response with new comment data
 */

// Initialize session for user authentication
session_start();

// Include database connection
include '../config/db_connection.php';

// Set response type to JSON for AJAX requests
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Validate POST request and comment content
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['content'])) {
    // Sanitize and prepare input data
    $content = trim($_POST['content']);  // Remove whitespace
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);  // Sanitize post ID
    $user_id = $_SESSION['user_id'];  // Get current user ID

    // Prepare SQL statement to insert comment
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    
    // Execute insert and handle result
    if ($stmt->execute([$post_id, $user_id, $content])) {
        // Fetch the newly created comment with user details
        $stmt = $pdo->prepare("
            SELECT comments.*, users.username, users.profile_picture 
            FROM comments 
            JOIN users ON comments.user_id = users.id 
            WHERE comments.id = LAST_INSERT_ID()
        ");
        $stmt->execute();
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return success response with comment data
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
        // Return error if insert fails
        echo json_encode(['success' => false, 'error' => 'Failed to add comment']);
    }
} else {
    // Return error for invalid request or empty comment
    echo json_encode(['success' => false, 'error' => 'Empty comment']);
}

// End script execution
exit();
