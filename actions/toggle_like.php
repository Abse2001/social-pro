<?php
/**
 * Like Toggle Handler
 * 
 * Manages post like/unlike functionality:
 * - Authentication check
 * - Like status verification
 * - Toggle like state
 * - Update like count
 * - JSON response with updated state
 */

// Initialize session for user authentication
session_start();

// Include database connection configuration
include '../config/db_connection.php';

// Verify user is authenticated before proceeding
// Return JSON error response if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Only process POST requests to prevent unauthorized access
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize post ID from POST data to prevent SQL injection
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Get current user's ID from session
    $user_id = $_SESSION['user_id'];

    // Check if user has already liked this post
    $check = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $check->execute([$post_id, $user_id]);
    
    // Toggle like status based on existing like
    if ($check->rowCount() > 0) {
        // User already liked post - remove like
        $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        $action = 'unliked';
    } else {
        // User hasn't liked post - add new like
        $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$post_id, $user_id]);
        $action = 'liked';
    }

    // Get updated like count for the post
    $count = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
    $count->execute([$post_id]);
    $like_count = $count->fetch()['count'];

    // Return JSON response with updated like status and count
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'action' => $action,
        'likes' => $like_count
    ]);
    exit();
}
