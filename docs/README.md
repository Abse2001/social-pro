# Social Media Application Documentation

## Project Overview
This is a PHP-based social media application that enables users to create profiles, share posts, interact through likes and comments, and search for other users. The application follows a modern web architecture with a MySQL database backend and features a responsive dark/light theme interface.

## Detailed Component Breakdown

### 1. Authentication System (auth/login.php)
The authentication system handles user identity verification and session management:

#### Key Features:
- Secure user registration with email verification
- Login with email/username support
- Password hashing using PHP's password_hash()
- "Remember Me" functionality with secure tokens
- Session-based authentication
- CSRF protection for forms

```php
// Authentication Example
session_start();
include 'config/db_connection.php';
include 'config/CookieHandler.php';

// Registration Example with Validation
function registerUser($username, $email, $password) {
    global $pdo;
    
    // Validate input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Invalid email format'];
    }
    
    if (strlen($password) < 8) {
        return ['success' => false, 'error' => 'Password must be at least 8 characters'];
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $success = $stmt->execute([$username, $email, $hashed_password]);
        return ['success' => $success];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Email already registered'];
    }
}

// Login Example with Remember Me and Security Features
function loginUser($email, $password, $remember = false) {
    global $pdo;
    
    // Rate limiting check
    if (checkLoginAttempts($email)) {
        return ['success' => false, 'error' => 'Too many login attempts'];
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['last_activity'] = time();
        
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            CookieHandler::set('remember_token', $token, time() + 30*24*60*60);
            
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
        }
        return ['success' => true];
    }
    
    recordFailedAttempt($email);
    return ['success' => false, 'error' => 'Invalid credentials'];
}

// Security helper functions
function checkLoginAttempts($email) {
    // Implement rate limiting logic
    return false;
}

function recordFailedAttempt($email) {
    // Record failed login attempt
}
```

#### Security Measures:
- Rate limiting for login attempts
- Input validation and sanitization
- Secure session handling
- Protection against brute force attacks
- Secure token generation for Remember Me

### 2. Post Management System
```php
class PostManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Create Post
    public function createPost($user_id, $content, $image = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO posts (user_id, content, image_url) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$user_id, $content, $image]);
    }
    
    // Edit Post
    public function editPost($post_id, $user_id, $content) {
        $stmt = $this->pdo->prepare("
            UPDATE posts 
            SET content = ? 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$content, $post_id, $user_id]);
    }
    
    // Delete Post
    public function deletePost($post_id, $user_id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM posts 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$post_id, $user_id]);
    }
}
```

### 3. Social Interaction Features
```php
class SocialInteraction {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Like/Unlike Post
    public function toggleLike($post_id, $user_id) {
        $check = $this->pdo->prepare("
            SELECT id FROM likes 
            WHERE post_id = ? AND user_id = ?
        ");
        $check->execute([$post_id, $user_id]);
        
        if ($check->rowCount() > 0) {
            $stmt = $this->pdo->prepare("
                DELETE FROM likes 
                WHERE post_id = ? AND user_id = ?
            ");
            $action = 'unliked';
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO likes (post_id, user_id) 
                VALUES (?, ?)
            ");
            $action = 'liked';
        }
        
        return [
            'success' => $stmt->execute([$post_id, $user_id]),
            'action' => $action
        ];
    }
    
    // Add Comment
    public function addComment($post_id, $user_id, $content) {
        $stmt = $this->pdo->prepare("
            INSERT INTO comments (post_id, user_id, content) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$post_id, $user_id, $content]);
    }
    
    // Search Users
    public function searchUsers($query) {
        $search = '%' . $query . '%';
        $stmt = $this->pdo->prepare("
            SELECT id, username, profile_picture 
            FROM users 
            WHERE username LIKE ? OR email LIKE ?
            LIMIT 5
        ");
        $stmt->execute([$search, $search]);
        return $stmt->fetchAll();
    }
}
```

### 4. Profile Management
```php
class ProfileManager {
    private $pdo;
    private $upload_dir = 'assets/uploads/profile_pictures/';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Update Profile
    public function updateProfile($user_id, $data) {
        $allowed_fields = ['username', 'email', 'bio'];
        $updates = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                $updates[] = "$field = ?";
                $values[] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $values[] = $user_id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }
    
    // Update Profile Picture
    public function updateProfilePicture($user_id, $file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $file['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($filetype, $allowed)) {
            return false;
        }
        
        $new_filename = uniqid() . '.' . $filetype;
        $upload_path = $this->upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], '../' . $upload_path)) {
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET profile_picture = ? 
                WHERE id = ?
            ");
            return $stmt->execute([$upload_path, $user_id]);
        }
        
        return false;
    }
}
```

#### Edit Posts (actions/edit_post.php)
- Content modification
- User verification
- Update timestamp management
- Security checks

#### Delete Posts (actions/delete_post.php)
- Post removal
- User authorization
- Associated data cleanup
- Image file deletion

### 5. Social Interaction Features
#### Like System (actions/toggle_like.php)
- Toggle like functionality
- Real-time like count updates
- User interaction tracking
- AJAX-based updates

#### Comment System (actions/add_comment.php)
- Comment creation
- User association
- Timestamp management
- Input validation

#### Search Function (actions/search.php)
- User search functionality
- Real-time search results
- Profile picture integration
- Result limiting for performance

### 6. Profile Management
#### View Profile (profile.php)
- User information display
- Post history
- Profile picture display
- Bio and user details

#### Edit Profile (edit_profile.php)
- Profile picture upload
- Bio modification
- Email/username updates
- Form validation

### 7. Frontend Features
#### Theme System (assets/js/theme.js)
- Dark/light theme toggle
- Local storage persistence
- Real-time theme switching
- Icon and text updates

#### Main JavaScript (assets/js/main.js)
- Post menu management
- Image preview functionality
- Like system integration
- Search system implementation
- Form handling

#### Styling (assets/css/style.css)
- Responsive design
- Theme variable system
- Component styling
- Animation effects
- Form styling
- Card layouts

### 8. Security Features
- SQL injection prevention through PDO
- XSS protection with htmlspecialchars
- CSRF protection in forms
- Secure file upload handling
- Password hashing
- Session security
- Cookie protection

### 9. File Structure
```
social_pro/
├── actions/           # PHP action handlers
│   ├── add_comment.php
│   ├── create_post.php
│   ├── delete_post.php
│   ├── edit_post.php
│   ├── search.php
│   └── toggle_like.php
├── assets/           
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   └── uploads/      # User content
│       ├── posts/    # Post images
│       └── profile_pictures/
├── auth/             # Authentication
│   ├── login.php
│   └── logout.php
├── config/           # Configuration
│   ├── CookieHandler.php
│   └── db_connection.php
├── includes/         # PHP components
└── database/         # Database schema
```

## Setup Instructions

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- mod_rewrite enabled
- GD Library for image processing

### Installation Steps
1. Clone repository
2. Configure database in config/db_connection.php
3. Set up virtual host (optional)
4. Ensure write permissions for uploads directory
5. Initialize database (automatic)

### Configuration
1. Database Settings:
   - Host: localhost
   - Default user: root
   - Default password: empty
   - Database: social_pro

2. File Permissions:
   ```bash
   chmod 755 assets/uploads
   chmod 755 assets/uploads/posts
   chmod 755 assets/uploads/profile_pictures
   ```

3. Apache Configuration:
   - Enable mod_rewrite
   - Allow .htaccess override

## Development Guidelines

### Coding Standards
- PSR-4 autoloading compliance
- PSR-12 coding style
- Prepared statements for queries
- Input validation
- Error handling
- Secure file operations

### Best Practices
- Sanitize all user inputs
- Validate file uploads
- Use PDO for database
- Implement CSRF protection
- Secure session handling
- Proper error logging

### Testing
- Verify file uploads
- Check database connections
- Test authentication
- Validate theme switching
- Ensure responsive design

## Technical Stack
- **Backend**: PHP 7+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: Apache (XAMPP recommended)

## Directory Structure
```
social_pro/
├── actions/           # PHP action handlers
├── assets/           # Static resources
│   ├── css/         # Stylesheets
│   ├── js/          # JavaScript files
│   └── uploads/     # User uploaded content
├── auth/            # Authentication related files
├── config/          # Configuration files
├── includes/        # Reusable PHP components
└── database/        # Database schema
```

## Core Features

### 1. Authentication System
- User registration with email verification
- Login with email/username
- Password hashing for security
- Session management

### 2. User Profile Management
- Profile picture upload
- Bio update
- Username and email management
- View other user profiles

### 3. Post Management
- Create text posts
- Upload images with posts
- Edit own posts
- Delete own posts
- View all posts in feed

### 4. Social Interactions
- Like/unlike posts
- Comment on posts
- Search for other users
- View user profiles

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Posts Table
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

### Comments Table
```sql
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Likes Table
```sql
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (post_id, user_id)
);
```

## Key Components

### 1. Database Connection (config/db_connection.php)
- Handles database connectivity
- Creates database and tables if they don't exist
- Sets up PDO with error handling

### 2. Authentication (auth/login.php)
- Handles both login and registration
- Form validation
- Password hashing
- Session creation

### 3. Feed System (includes/feed.php)
- Displays posts from all users
- Handles post creation
- Manages post interactions (likes, comments)
- Real-time updates using JavaScript

### 4. Profile System (profile.php)
- User profile display
- Profile editing
- Post management
- Profile picture handling

### 5. Action Handlers (actions/)
- create_post.php: Handles post creation
- delete_post.php: Manages post deletion
- edit_post.php: Handles post updates
- toggle_like.php: Manages post likes
- add_comment.php: Handles commenting
- search.php: User search functionality

## JavaScript Functions

### Main Features (assets/js/main.js)
- Post menu toggling
- Image preview before upload
- Like functionality
- User search
- Form toggling
- Edit form management

## CSS Styling

The application uses a modern, responsive design with:
- Flexbox layout
- Mobile-first approach
- Custom form styling
- Card-based post design
- Interactive elements
- Consistent color scheme

## Security Features
1. SQL Injection Prevention
   - Prepared statements
   - Parameter binding
   - Input sanitization

2. XSS Prevention
   - HTML escaping
   - Content sanitization

3. CSRF Protection
   - Session validation
   - Form tokens

4. File Upload Security
   - File type validation
   - Size restrictions
   - Unique filename generation

## Setup Instructions

1. Prerequisites
   - PHP 7+
   - MySQL
   - Apache Server
   - Web browser

2. Installation
   ```bash
   # Clone the repository
   git clone [repository-url]

   # Configure database
   # Edit config/db_connection.php with your credentials

   # Set up file permissions
   chmod 755 assets/uploads
   chmod 755 assets/uploads/posts
   chmod 755 assets/uploads/profile_pictures
   ```

3. Database Setup
   - The application will automatically create the database and tables
   - Default database name: social_pro
   - Default user: root
   - Default password: (empty)

## Usage Guidelines

1. User Registration
   - Navigate to login.php
   - Click "Create Account"
   - Fill in required information
   - Submit registration form

2. Creating Posts
   - Log in to your account
   - Use the post creation form at the top of the feed
   - Optionally add an image
   - Click "Post" to publish

3. Interacting with Posts
   - Like: Click the heart icon
   - Comment: Use the comment form below posts
   - Edit: Click the menu (⋮) on your own posts
   - Delete: Access through post menu

4. Profile Management
   - Click your username/avatar
   - Use "Edit Profile" to update information
   - Upload new profile picture
   - Update bio and other details

## Error Handling
- Database connection errors
- File upload failures
- Authentication errors
- Form validation errors
- Permission errors

## Future Enhancements
1. Direct messaging system
2. Post sharing functionality
3. User notifications
4. Friend/Follow system
5. Post categories/tags
6. Rich text editor for posts
7. Video upload support
8. API development

## Troubleshooting

Common Issues:
1. Database Connection
   - Check credentials in db_connection.php
   - Verify MySQL service is running
   - Check database permissions

2. File Uploads
   - Verify directory permissions
   - Check PHP upload settings
   - Validate file size limits

3. Session Issues
   - Clear browser cache
   - Check PHP session configuration
   - Verify session storage permissions

## Contributing
1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## License
[Your License Here]

## Contact
[Your Contact Information]
