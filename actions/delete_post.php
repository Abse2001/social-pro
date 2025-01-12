<?php
/**
 * Post Deletion Handler
 * 
 * Handles post deletion with:
 * - User authentication check
 * - Owner verification
 * - Database deletion
 * - Redirect handling based on referrer
 */

// Start session to access user data
session_start();

// Include database connection configuration
include '../config/db_connection.php';

// Verify user is logged in by checking session
// Redirect to index if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Only process POST requests to prevent unauthorized access
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize the post ID from POST data to prevent SQL injection
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Get current user's ID from session
    $user_id = $_SESSION['user_id'];

    // Prepare DELETE query with user_id check to ensure owner is deleting
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    
    // Execute delete query and handle the result
    if ($stmt->execute([$post_id, $user_id])) {
        // Get the referring page to redirect back appropriately
        $referrer = $_SERVER['HTTP_REFERER'];
        
        // Check if user came from profile page
        if (strpos($referrer, 'profile.php') !== false) {
            header("Location: ../profile.php?success=post_deleted");
        } else {
            // Default redirect to index with success message
            header("Location: ../index.php?success=post_deleted");
        }
    } else {
        // Handle deletion failure
        $referrer = $_SERVER['HTTP_REFERER'];
        
        // Return to appropriate page with error message
        if (strpos($referrer, 'profile.php') !== false) {
            header("Location: ../profile.php?error=delete_failed");
        } else {
            header("Location: ../index.php?error=delete_failed");
        }
    }
} else {
    // Redirect non-POST requests to index
    header("Location: ../index.php");
}

// Ensure script stops executing after redirect
exit();
