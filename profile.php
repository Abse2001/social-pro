<?php
session_start();
include 'config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];

// Get user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get user's posts
$posts_stmt = $pdo->prepare("
    SELECT posts.*, users.username, users.profile_picture
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.user_id = ?
    ORDER BY posts.created_at DESC
");
$posts_stmt->execute([$user_id]);
$posts = $posts_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Social Media App</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <a href="index.php" class="navbar-brand">Social Media</a>
            <div class="search-container">
                <input type="text" id="search" placeholder="Search users..." 
                       onkeyup="searchUsers(this.value)">
                <div id="search-results" class="search-results"></div>
            </div>
            <div class="navbar-nav">
                <a href="index.php" class="nav-link">Feed</a>
                <a href="profile.php" class="nav-link active">Profile</a>
                <a href="auth/logout.php" class="nav-link">Logout</a>
            </div>
        </nav>
        <div class="profile-header">
            <div class="profile-info">
                <img src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : './assets/images/default-avatar.png'; ?>" 
                     alt="Profile Picture" class="profile-picture">
                <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="bio"><?php echo htmlspecialchars($user['bio'] ?? 'No bio yet.'); ?></p>
                
                <?php if ($user_id == $_SESSION['user_id']): ?>
                    <button onclick="location.href='edit_profile.php'" class="btn">Edit Profile</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="profile-posts">
            <h2>Posts</h2>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <div class="post-header">
                        <div class="post-author-info">
                            <img src="<?php echo !empty($post['profile_picture']) ? htmlspecialchars($post['profile_picture']) : './assets/images/default-avatar.png'; ?>" 
                                 alt="Profile" class="post-profile-pic">
                            <span class="post-author"><?php echo htmlspecialchars($post['username']); ?></span>
                        </div>
                        <span class="post-date"><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></span>
                    </div>
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                    <div class="post-actions">
                        <?php if ($user_id == $_SESSION['user_id']): ?>
                            <button onclick="showEditForm(<?php echo $post['id']; ?>)" class="btn-small">Edit</button>
                            <form action="actions/delete_post.php" method="POST" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="btn-small delete" 
                                        onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
