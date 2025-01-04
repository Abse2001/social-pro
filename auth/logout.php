<?php
session_start();
include '../config/CookieHandler.php';

// Clear remember me token in database if it exists
if (isset($_SESSION['user_id'])) {
    include '../config/db_connection.php';
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Clear cookies
CookieHandler::delete('remember_token');

// Destroy session
session_destroy();

header("Location: ../index.php");
exit();
