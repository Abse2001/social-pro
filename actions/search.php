<?php
session_start();
include '../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

if (isset($_GET['q'])) {
    $search = '%' . $_GET['q'] . '%';
    
    // Search users
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
