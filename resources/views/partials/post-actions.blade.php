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
        const isLiked = likeBtn.classList.contains('liked');
        const likesCount = document.getElementById('likes-count');
        const currentLikes = parseInt(likesCount.textContent || '0');
        
        // 先进行视觉反馈
        if (!isLiked) {
            likeBtn.classList.add('liked');
            likeBtn.querySelector('i').classList.remove('far');
            likeBtn.querySelector('i').classList.add('fas');
            if (likesCount) {
                likesCount.textContent = currentLikes + 1;
            }
        } else {
            likeBtn.classList.remove('liked');
            likeBtn.querySelector('i').classList.remove('fas');
            likeBtn.querySelector('i').classList.add('far');
            if (likesCount) {
                likesCount.textContent = Math.max(0, currentLikes - 1);
            }
        }
        
        // 调用API更新点赞状态
        fetch('/posts/{{ $post["id"] }}/like', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: new FormData(),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            // 更新UI，以服务器返回的数据为准
            if (likesCount && data.likes !== undefined) {
                likesCount.textContent = data.likes;
            }
            
            // 如果服务器返回的状态与预期不符，恢复UI
            if (data.user_liked !== undefined && ((data.user_liked && !likeBtn.classList.contains('liked')) || (!data.user_liked && likeBtn.classList.contains('liked')))) {
                likeBtn.classList.toggle('liked');
                const icon = likeBtn.querySelector('i');
                if (data.user_liked) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // 发生错误时恢复原状态
            likeBtn.classList.toggle('liked');
            const icon = likeBtn.querySelector('i');
            if (likeBtn.classList.contains('liked')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                if (likesCount) {
                    likesCount.textContent = currentLikes + 1;
                }
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                if (likesCount) {
                    likesCount.textContent = Math.max(0, currentLikes - 1);
                }
            }
        });
    });
    
    // 收藏功能
    const favoriteBtn = document.getElementById('favorite-btn');
    favoriteBtn.addEventListener('click', function() {
        const isFavorited = favoriteBtn.classList.contains('favorited');
        
        // 先进行视觉反馈
        if (!isFavorited) {
            favoriteBtn.classList.add('favorited');
            favoriteBtn.querySelector('i').classList.remove('far');
            favoriteBtn.querySelector('i').classList.add('fas');
        } else {
            favoriteBtn.classList.remove('favorited');
            favoriteBtn.querySelector('i').classList.remove('fas');
            favoriteBtn.querySelector('i').classList.add('far');
        }
        
        // 调用API更新收藏状态
        fetch('/posts/{{ $post["id"] }}/favorite', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: new FormData(),
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            // 如果服务器返回的状态与预期不符，恢复UI
            if (data.user_favorited !== undefined && ((data.user_favorited && !favoriteBtn.classList.contains('favorited')) || (!data.user_favorited && favoriteBtn.classList.contains('favorited')))) {
                favoriteBtn.classList.toggle('favorited');
                const icon = favoriteBtn.querySelector('i');
                if (data.user_favorited) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // 发生错误时恢复原状态
            favoriteBtn.classList.toggle('favorited');
            const icon = favoriteBtn.querySelector('i');
            if (favoriteBtn.classList.contains('favorited')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
        });
    });
});
</script> 