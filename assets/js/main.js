function togglePostMenu(postId) {
    const menu = document.getElementById(`post-menu-${postId}`);
    const allMenus = document.querySelectorAll('.post-menu-content');
    
    // Close all other menus
    allMenus.forEach(m => {
        if (m.id !== `post-menu-${postId}`) {
            m.style.display = 'none';
        }
    });
    
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Close menus when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.matches('.post-menu-btn')) {
        const menus = document.querySelectorAll('.post-menu-content');
        menus.forEach(menu => menu.style.display = 'none');
    }
});

function showEditForm(postId) {
    document.getElementById(`edit-form-${postId}`).style.display = 'block';
    document.getElementById(`post-menu-${postId}`).style.display = 'none';
}

function hideEditForm(postId) {
    document.getElementById(`edit-form-${postId}`).style.display = 'none';
}

function toggleLike(postId) {
    fetch('actions/toggle_like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `post_id=${postId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likeButton = document.querySelector(`#post-${postId} .like-button`);
            const likeCount = document.querySelector(`#post-${postId} .like-count`);
            
            if (data.action === 'liked') {
                likeButton.classList.add('liked');
            } else {
                likeButton.classList.remove('liked');
            }
            
            likeCount.textContent = `${data.likes} likes`;
        }
    });
}

function previewImage(input) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = ''; // Clear previous preview

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const img = document.createElement('img');
        
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.appendChild(img);
            preview.style.display = 'block'; // Make sure preview is visible
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none'; // Hide preview if no file selected
    }
}

// Add comment submission handler
function submitComment(event, form) {
    event.preventDefault();
    
    const formData = new FormData(form);
    const postId = formData.get('post_id');
    
    fetch('actions/add_comment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create new comment element
            const commentHtml = `
                <div class="comment" id="comment-${data.comment.id}">
                    <div class="comment-author-info">
                        <img src="${data.comment.profile_picture}" 
                             alt="Profile" class="comment-profile-pic">
                        <a href="profile.php?id=${data.comment.user_id}" class="comment-author">
                            <strong>${data.comment.username}</strong>
                        </a>
                    </div>
                    <div class="comment-content">${data.comment.content}</div>
                    <span class="comment-date">${data.comment.created_at}</span>
                    ${data.comment.user_id === data.current_user_id ? `
                        <button class="delete-comment-btn" data-comment-id="${data.comment.id}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ` : ''}
                </div>
            `;
            
            // Add new comment to the comments section
            const commentsSection = form.closest('.comments-section');
            const existingComments = commentsSection.querySelectorAll('.comment');
            if (existingComments.length > 0) {
                existingComments[0].insertAdjacentHTML('beforebegin', commentHtml);
            } else {
                form.insertAdjacentHTML('afterend', commentHtml);
            }
            
            // Clear the input
            form.querySelector('input[name="content"]').value = '';
        } else {
            alert(data.error || 'Failed to add comment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add comment');
    });
}

// Add event delegation for comment deletion
document.addEventListener('click', function(event) {
    const deleteBtn = event.target.closest('.delete-comment-btn');
    if (deleteBtn) {
        event.preventDefault();
        const commentId = deleteBtn.dataset.commentId;
        
        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('comment_id', commentId);
        
        fetch('actions/delete_comment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentElement = document.getElementById(`comment-${commentId}`);
                commentElement.remove();
            } else {
                alert(data.error || 'Failed to delete comment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete comment');
        });
    }
});

function searchUsers(query) {
    if (query.length < 2) return;
    
    fetch(`actions/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('search-results');
            resultsDiv.innerHTML = '';
            
            data.forEach(result => {
                const div = document.createElement('div');
                div.className = 'search-result';
                div.innerHTML = `
                    <a href="profile.php?id=${result.id}">
                        <img src="${result.profile_picture || 'assets/images/default-avatar.png'}" alt="">
                        <span>${result.username}</span>
                    </a>
                `;
                resultsDiv.appendChild(div);
            });
            
            resultsDiv.style.display = data.length ? 'block' : 'none';
        });
}
