<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<nav class="navbar">
    <a href="index.php" class="navbar-brand">Social Media</a>
    <div class="search-container">
        <input type="text" id="search" placeholder="Search users..." 
               onkeyup="searchUsers(this.value)">
        <div id="search-results" class="search-results"></div>
    </div>
    <div class="navbar-nav">
        <a href="index.php" class="nav-link active">Feed</a>
        <a href="profile.php" class="nav-link">
            <img src="<?php echo !empty($_SESSION['profile_picture']) ? htmlspecialchars($_SESSION['profile_picture']) : 'assets/images/default-avatar.png'; ?>" 
                 alt="Profile" class="nav-profile-pic">
            Profile
        </a>
        <a href="auth/logout.php" class="nav-link">Logout</a>
    </div>
</nav>

<div class="main-content">
    <div class="feed-container">
        <div class="feed-header">
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h2>
        </div>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="error">
            <?php 
            if ($_GET['error'] == 'post_failed') echo "Failed to create post";
            if ($_GET['error'] == 'empty_post') echo "Post cannot be empty";
            ?>
        </div>
    <?php endif; ?>

    <div class="post-form">
        <h3>Create a Post</h3>
        <form action="actions/create_post.php" method="POST" enctype="multipart/form-data">
            <textarea name="content" placeholder="What's on your mind?" required></textarea>
            <div class="post-form-footer">
                <input type="file" name="post_image" accept="image/*" id="post_image" onchange="previewImage(this)">
                <div id="image-preview" class="image-preview-container"></div>
                <label for="post_image" class="image-upload-label">
                    <i class="fas fa-image"></i> Add Image
                </label>
                <button type="submit" class="btn">Post</button>
            </div>
        </form>
    </div>
    
    <div class="posts">
        <?php
        $stmt = $pdo->prepare("
            SELECT posts.*, users.username, users.profile_picture 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            ORDER BY posts.created_at DESC
        ");
        $stmt->execute();
        $posts = $stmt->fetchAll();

        if ($posts): 
            foreach ($posts as $post):
        ?>
            <div class="post" id="post-<?php echo $post['id']; ?>">
                <div class="post-header">
                    <div class="post-author-info">
                        <img src="<?php echo !empty($post['profile_picture']) ? htmlspecialchars($post['profile_picture']) : 'assets/images/default-avatar.png'; ?>" 
                             alt="Profile" class="post-profile-pic">
                        <a href="profile.php?id=<?php echo $post['user_id']; ?>" class="post-author">
                            <?php echo htmlspecialchars($post['username']); ?>
                        </a>
                        <span class="post-date">
                            <?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?>
                            <?php if (isset($post['updated_at']) && $post['updated_at'] != $post['created_at']): ?>
                                <span class="edited-tag">(edited)</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                        <div class="post-menu">
                            <button onclick="togglePostMenu(<?php echo $post['id']; ?>)" class="post-menu-btn">⋮</button>
                            <div id="post-menu-<?php echo $post['id']; ?>" class="post-menu-content">
                                <button onclick="showEditForm(<?php echo $post['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit Post
                                </button>
                                <form action="actions/delete_post.php" method="POST">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this post?')">
                                        <i class="fas fa-trash-alt"></i> Delete Post
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    <?php if (!empty($post['image_url'])): ?>
                        <div class="post-image">
                            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Post image">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="post-actions">
                    <?php
                    // Check if user has liked this post
                    $like_check = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
                    $like_check->execute([$post['id'], $_SESSION['user_id']]);
                    $is_liked = $like_check->rowCount() > 0;
                    
                    // Get like count
                    $like_count = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
                    $like_count->execute([$post['id']]);
                    $likes = $like_count->fetch()['count'];
                    ?>
                    <button onclick="toggleLike(<?php echo $post['id']; ?>)" 
                            class="like-button <?php echo $is_liked ? 'liked' : ''; ?>">
                        ❤ Like
                    </button>
                    <span class="like-count"><?php echo $likes; ?> likes</span>
                </div>
                <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                    <div id="edit-form-<?php echo $post['id']; ?>" class="edit-form" style="display: none;">
                        <form action="actions/edit_post.php" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                            <button type="submit" class="btn-small">Save</button>
                            <button type="button" class="btn-small" onclick="hideEditForm(<?php echo $post['id']; ?>)">Cancel</button>
                        </form>
                    </div>
                <?php endif; ?>
                
                <!-- Comments Section -->
                <div class="comments-section">
                    <form action="actions/add_comment.php" method="POST" class="comment-form">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="text" name="content" placeholder="Write a comment..." required>
                        <button type="submit" class="btn-small">Comment</button>
                    </form>
                    
                    <?php
                    $comment_stmt = $pdo->prepare("
                        SELECT comments.*, users.username, users.profile_picture 
                        FROM comments 
                        JOIN users ON comments.user_id = users.id 
                        WHERE post_id = ? 
                        ORDER BY comments.created_at DESC
                    ");
                    $comment_stmt->execute([$post['id']]);
                    $comments = $comment_stmt->fetchAll();
                    
                    foreach ($comments as $comment):
                    ?>
                        <div class="comment">
                            <div class="comment-author-info">
                                <img src="<?php echo !empty($comment['profile_picture']) ? htmlspecialchars($comment['profile_picture']) : 'assets/images/default-avatar.png'; ?>" 
                                     alt="Profile" class="comment-profile-pic">
                                <a href="profile.php?id=<?php echo $comment['user_id']; ?>" class="comment-author">
                                    <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                </a>
                            </div>
                            <?php echo htmlspecialchars($comment['content']); ?>
                            <span class="comment-date"><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php 
            endforeach;
        else:
        ?>
            <p>No posts yet.</p>
        <?php endif; ?>
    </div>
    
    <div class="logout-button">
        <a href="auth/logout.php" class="btn">Logout</a>
    </div>
</div>
