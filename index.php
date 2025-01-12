<?php
// Initialize session and include required files
session_start();
include 'config/db_connection.php';
include 'config/CookieHandler.php';

// Check for "Remember Me" functionality
// If user is not logged in but has a remember token cookie
if (!isset($_SESSION['user_id']) && CookieHandler::exists('remember_token')) {
    $token = CookieHandler::get('remember_token');
    
    // Verify remember token and get user details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    // If valid token found, create session for user
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile_picture'] = $user['profile_picture'];
    } else {
        // If token is invalid, remove it
        CookieHandler::delete('remember_token');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>

        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media App</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php 
        if(isset($_SESSION['user_id'])) {
            include 'includes/feed.php';
        } else {
            header("Location: auth/login.php");
            exit();
        }
        ?>
    </div>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/theme.js"></script>
</body>
</html>
