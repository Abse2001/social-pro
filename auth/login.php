<?php
session_start();
include '../config/db_connection.php';
include '../config/CookieHandler.php';

// Variable to control which form to show
$show_register_form = false;

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
            
            // Handle Remember Me
            if (isset($_POST['remember_me']) && $_POST['remember_me'] == 'on') {
                $token = bin2hex(random_bytes(32));
                CookieHandler::set('remember_token', $token);
                
                // Store token in database
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $user['id']]);
            }
            
            header("Location: ../index.php");
            exit();
        } else {
            $login_error = "Invalid credentials";
        }
    } 
    else if (isset($_POST['register'])) {
        // Set to show registration form since we're processing a registration
        $show_register_form = true;
        
        // Keep the entered values to repopulate the form
        $entered_username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $entered_email = filter_input(INPUT_POST, 'reg_email', FILTER_SANITIZE_EMAIL);
        
        // Registration Process
        $username = $entered_username;
        $email = $entered_email;
        $password = $_POST['reg_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validation
        if ($password !== $confirm_password) {
            $register_error = "Passwords do not match";
        } else {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->rowCount() > 0) {
                $register_error = "Username already taken";
            } else {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->rowCount() > 0) {
                    $register_error = "Email already registered";
                } else {
                    // All checks passed, insert new user
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                    
                    try {
                        if ($stmt->execute([$username, $email, $hashed_password])) {
                            $success_message = "Registration successful! Please login.";
                            $show_register_form = false; // Show login form after successful registration
                        } else {
                            $register_error = "Registration failed. Please try again.";
                        }
                    } catch (PDOException $e) {
                        $register_error = "Registration failed. Please try again.";
                    }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <a href="../index.php" class="navbar-brand">Social Media</a>
            <div class="theme-switch">
                <button id="theme-toggle" class="theme-toggle-btn">
                    <i class="fas fa-sun"></i>
                    <span>Light</span>
                </button>
            </div>
        </nav>
        <div class="auth-box">
            <!-- Login Form -->
            <div class="form-container" id="loginForm" style="display: <?php echo !$show_register_form ? 'block' : 'none'; ?>; background-color: var(--card-bg); color: var(--text-color);">
                <h2 style="color: var(--text-color);">Welcome Back!</h2>
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
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="remember_me"> Remember Me
                        </label>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary">Sign In</button>
                </form>
                <div class="auth-footer">
                    <p>Don't have an account?</p>
                    <button onclick="toggleForms()" class="btn btn-secondary">Create Account</button>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="form-container" id="registerForm" style="display: <?php echo $show_register_form ? 'block' : 'none'; ?>; background-color: var(--card-bg); color: var(--text-color);">
                <h2 style="color: var(--text-color);">Create Account</h2>
                <?php if (isset($register_error)): ?>
                    <div class="error"><?php echo htmlspecialchars($register_error); ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <input type="text" 
                               id="username" 
                               name="username" 
                               placeholder="Username" 
                               value="<?php echo isset($entered_username) ? htmlspecialchars($entered_username) : ''; ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <input type="email" 
                               id="reg_email" 
                               name="reg_email" 
                               placeholder="Email" 
                               value="<?php echo isset($entered_email) ? htmlspecialchars($entered_email) : ''; ?>"
                               required>
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

    <script src="../assets/js/theme.js"></script>
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

        // If there was a registration error, ensure the registration form is visible
        <?php if ($show_register_form): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        });
        <?php endif; ?>
    </script>
</body>
</html>
