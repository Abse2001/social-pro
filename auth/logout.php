<?php
/**
 * Logout Handler
 * 
 * Handles user logout by:
 * 1. Clearing session data
 * 2. Removing remember me token from database
 * 3. Deleting remember me cookie
 * 4. Redirecting to login page
 */

session_start();
include '../config/CookieHandler.php';

// If user is logged in, clear their remember token from database
if (isset($_SESSION['user_id'])) {
    include '../config/db_connection.php';
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Delete the remember me cookie if it exists
CookieHandler::delete('remember_token');

// Destroy all session data
session_destroy();

// Redirect to login page
header("Location: ../index.php");
exit();
