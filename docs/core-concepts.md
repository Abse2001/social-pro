# Core Web Development Concepts

This document outlines fundamental concepts used in our social media application with practical code examples.

## 1. PHP Fundamentals

PHP is used extensively for server-side scripting and dynamic content generation.

### Example - Post Creation:
```php
// From actions/create_post.php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['content'])) {
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $image_url = null;

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image_url) VALUES (?, ?, ?)");
    if ($stmt->execute([$user_id, $content, $image_url])) {
        header("Location: ../index.php");
    }
}
```

## 2. Database Fundamentals

The application uses MySQL for data storage with PDO for secure database operations.

### Example - Database Schema:
```sql
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Example - Secure Query Execution:
```php
// From actions/search.php
$search = '%' . $_GET['q'] . '%';
$stmt = $pdo->prepare("
    SELECT id, username, profile_picture 
    FROM users 
    WHERE username LIKE ? OR email LIKE ?
    LIMIT 5
");
$stmt->execute([$search, $search]);
```

## 3. Form Handling and Validation

Secure form handling with proper validation is crucial for web applications.

### Example - Login Form Validation:
```php
// From auth/login.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_id = trim($_POST['login_id']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$login_id, $login_id]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        // Login successful
    }
}
```

## 4. Application Security Fundamentals

Security measures are implemented throughout the application.

### Example - Password Hashing:
```php
// From auth/login.php registration section
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
```

### Example - SQL Injection Prevention:
```php
// Using prepared statements everywhere
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
```

### Example - XSS Prevention:
```php
// From includes/feed.php
<h1><?php echo htmlspecialchars($user['username']); ?></h1>
```

## 5. File and Directory Handling

The application handles file uploads securely for profile pictures and post images.

### Example - File Upload:
```php
// From edit_profile.php
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['profile_picture']['name'];
    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
    
    if (in_array(strtolower($filetype), $allowed)) {
        if (!file_exists('uploads/profile_pictures')) {
            mkdir('uploads/profile_pictures', 0777, true);
        }
        
        $new_filename = uniqid() . '.' . $filetype;
        $upload_path = 'assets/uploads/profile_pictures/' . $new_filename;
        
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
            // File upload successful
        }
    }
}
```

## 6. Session Management and Cookies

The application uses sessions and cookies for user authentication and preferences.

### Example - Session Management:
```php
// From index.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
```

### Example - Cookie Handling:
```php
// From config/CookieHandler.php
class CookieHandler {
    private const COOKIE_EXPIRY = 30 * 24 * 60 * 60; // 30 days
    private const SECURE = true;
    private const HTTP_ONLY = true;
    
    public static function set($name, $value, $expiry = null) {
        $expiry = $expiry ?? time() + self::COOKIE_EXPIRY;
        setcookie(
            $name,
            $value,
            [
                'expires' => $expiry,
                'secure' => self::SECURE,
                'httponly' => self::HTTP_ONLY,
                'samesite' => 'Strict'
            ]
        );
    }
}
```

### Example - Remember Me Functionality:
```php
// From auth/login.php
if (isset($_POST['remember_me']) && $_POST['remember_me'] == 'on') {
    $token = bin2hex(random_bytes(32));
    CookieHandler::set('remember_token', $token);
    
    $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
    $stmt->execute([$token, $user['id']]);
}
```

## Best Practices

1. Always validate and sanitize user input
2. Use prepared statements for database queries
3. Implement proper error handling
4. Secure file uploads with type checking
5. Use secure session and cookie settings
6. Implement CSRF protection
7. Prevent XSS attacks through output escaping
8. Follow the principle of least privilege
