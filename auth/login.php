<?php
session_start();
include '../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        // Login Process
        $login_id = trim($_POST['login_id']);
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$login_id, $login_id]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['profile_picture'] = $user['profile_picture'];
            header("Location: ../index.php");
            exit();
        } else {
            $login_error = "Invalid credentials";
        }
    } 
    else if (isset($_POST['register'])) {
        // Registration Process
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'reg_email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['reg_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validation
        if ($password !== $confirm_password) {
            $register_error = "Passwords do not match";
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $register_error = "Email already registered";
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $email, $hashed_password])) {
                    $success_message = "Registration successful! Please login.";
                } else {
                    $register_error = "Registration failed";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Social Media App</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-box">
            <!-- Login Form -->
            <div class="form-container" id="loginForm">
                <h2>Welcome Back!</h2>
                <?php if (isset($login_error)): ?>
                    <div class="error"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <?php if (isset($success_message)): ?>
                    <div class="success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <input type="text" id="login_id" name="login_id" placeholder="Email or Username" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary">Sign In</button>
                </form>
                <div class="auth-footer">
                    <p>Don't have an account?</p>
                    <button onclick="toggleForms()" class="btn btn-secondary">Create Account</button>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="form-container" id="registerForm" style="display: none;">
                <h2>Create Account</h2>
                <?php if (isset($register_error)): ?>
                    <div class="error"><?php echo $register_error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <input type="text" id="username" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <input type="email" id="reg_email" name="reg_email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="reg_password" name="reg_password" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary">Register</button>
                </form>
                <div class="auth-footer">
                    <p>Already have an account?</p>
                    <button onclick="toggleForms()" class="btn btn-secondary">Sign In</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleForms() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
            }
        }
    </script>
</body>
</html>
