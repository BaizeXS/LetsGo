<style>
.post-actions {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    z-index: 90; /* Lower than the close button but high enough to be above content */
}

.action-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn:hover {
    transform: translateY(-2px);
}

.action-btn.liked i {
    color: #ef4444;
}

.action-btn.favorited i {
    color: #eab308;
}
</style>

<div class="post-actions">
    <div class="action-btn" id="like-btn">
        <i class="far fa-heart text-xl"></i>
    </div>
    
    <div class="action-btn" id="favorite-btn">
        <i class="far fa-bookmark text-xl"></i>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 点赞功能
    const likeBtn = document.getElementById('like-btn');
    likeBtn.addEventListener('click', function() {
        const isLiked = likeBtn.classList.toggle('liked');
        
        // 调用API更新点赞状态
        fetch('/posts/{{ $post["id"] }}/like', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            // 更新UI
            const likesCount = document.getElementById('likes-count');
            if (likesCount && data.likes) {
                likesCount.textContent = data.likes;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
    
    // 收藏功能
    const favoriteBtn = document.getElementById('favorite-btn');
    favoriteBtn.addEventListener('click', function() {
        const isFavorited = favoriteBtn.classList.toggle('favorited');
        
        // 调用API更新收藏状态
        fetch('/posts/{{ $post["id"] }}/favorite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            // 视觉反馈即可，不需要更新UI
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});
</script> 