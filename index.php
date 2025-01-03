<?php
session_start();
include 'config/db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media App</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Display feed for logged in users -->
            <?php include 'includes/feed.php'; ?>
        <?php else: ?>
            <!-- Display login/register options -->
            <div class="auth-container">
                <h1>Welcome to Social Media App</h1>
                <div class="auth-buttons">
                    <a href="auth/login.php" class="btn">Login / Register</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
