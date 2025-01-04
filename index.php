<?php
session_start();
include 'config/db_connection.php';
include 'config/CookieHandler.php';

// Check for remember me cookie if not logged in
if (!isset($_SESSION['user_id']) && CookieHandler::exists('remember_token')) {
    $token = CookieHandler::get('remember_token');
    
    // Verify token and log user in
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['profile_picture'] = $user['profile_picture'];
    } else {
        // Invalid token, clear it
        CookieHandler::delete('remember_token');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        // Immediate theme initialization
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
