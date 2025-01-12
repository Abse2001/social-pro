# Social Media Application Documentation

## Project Overview
This is a PHP-based social media application that allows users to create profiles, share posts, interact through likes and comments, and search for other users. The application follows a standard web architecture with a MySQL database backend.

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
    remember_token VARCHAR(64) DEFAULT NULL,
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
