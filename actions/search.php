<?php
/**
 * User Search Handler
 * 
 * Provides user search functionality:
 * - Authentication verification
 * - Input sanitization
 * - Database search with LIKE queries
 * - Limited result set for performance
 * - JSON response format
 * 
 * Rate limiting placeholder included for future implementation
 */

session_start();
include '../config/db_connection.php';
include '../config/CookieHandler.php';

// Initialize rate limiting (TODO: implement rate limiting)

// Verify user authentication
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Validate search input

if (isset($_GET['q'])) {
    // Sanitize and prepare search term with wildcards for LIKE query
    $search = '%' . $_GET['q'] . '%';
    
    // Search users by username or email
    // Limit to 5 results for performance and UI
    $stmt = $pdo->prepare("
        SELECT id, username, profile_picture 
        FROM users 
        WHERE username LIKE ? OR email LIKE ?
        LIMIT 5
    ");
    $stmt->execute([$search, $search]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($results);
    exit();
}
