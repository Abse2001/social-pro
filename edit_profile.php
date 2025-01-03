<?php
session_start();
include 'config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Handle profile picture upload
    $profile_picture = $user['profile_picture']; // Keep existing picture by default
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            // Create uploads directory if it doesn't exist
            if (!file_exists('uploads/profile_pictures')) {
                mkdir('uploads/profile_pictures', 0777, true);
            }
            
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = 'assets/uploads/profile_pictures/' . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                // Delete old profile picture if it exists
                if ($user['profile_picture'] && file_exists($user['profile_picture'])) {
                    unlink($user['profile_picture']);
                }
                $profile_picture = $upload_path;
            }
        }
    }
    
    // Update user information
    $update_stmt = $pdo->prepare("
        UPDATE users 
        SET username = ?, email = ?, bio = ?, profile_picture = ?
        WHERE id = ?
    ");
    
    if ($update_stmt->execute([$username, $email, $bio, $profile_picture, $user_id])) {
        $_SESSION['username'] = $username; // Update session username
        header("Location: profile.php?success=profile_updated");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Social Media App</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <a href="index.php" class="navbar-brand">Social Media</a>
            <div class="navbar-nav">
                <a href="index.php" class="nav-link">Feed</a>
                <a href="profile.php" class="nav-link active">Profile</a>
                <a href="auth/logout.php" class="nav-link">Logout</a>
            </div>
        </nav>

        <div class="edit-profile-container">
            <h2>Edit Profile</h2>
            <form action="" method="POST" enctype="multipart/form-data" class="edit-profile-form">
                <div class="form-group">
                    <label for="profile_picture">Profile Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                             alt="Current Profile Picture" class="current-profile-pic">
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="profile.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
