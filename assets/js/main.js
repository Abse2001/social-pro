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
