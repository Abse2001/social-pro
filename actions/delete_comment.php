<?php
/**
 * Comment Deletion Handler
 * 
 * Manages comment deletion with:
 * - Authentication verification
 * - Owner verification (users can only delete their own comments)
 * - Database deletion
 * - JSON response
 */

// Initialize session for user authentication
session_start();

// Include database connection
include '../config/db_connection.php';

// Set response type to JSON for AJAX requests
header('Content-Type: application/json');

// Verify user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Process deletion if POST request and comment ID exists
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['comment_id'])) {
    // Sanitize comment ID input
    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];

    // Prepare and execute delete query (only for user's own comments)
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    
    // Handle deletion result
    if ($stmt->execute([$comment_id, $user_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete comment']);
    }
} else {
    // Handle invalid request
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
