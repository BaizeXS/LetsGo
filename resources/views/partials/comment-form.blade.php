<div class="comment-form mt-2">
    @if(isset($isAuthenticated) && $isAuthenticated)
        <form id="comment-form">
            @csrf
            <input type="hidden" id="parent-id" value="">
            <div class="flex items-center space-x-2">
                <textarea id="comment-content" class="flex-1 border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500" rows="1" placeholder="Write a comment..."></textarea>
                <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded-full text-sm hover:bg-red-600 transition whitespace-nowrap">
                    Post
                </button>
            </div>
        </form>
        <div id="reply-to" class="bg-gray-50 p-1 rounded mt-1 text-xs hidden">
            Replying to <span id="reply-to-name"></span>
            <button id="cancel-reply" class="text-red-500 ml-1">Cancel</button>
        </div>
    @else
        <div class="bg-gray-50 rounded-lg overflow-hidden py-2">
            <div class="px-3 flex items-center justify-between">
                <span class="text-sm text-gray-600">Sign in to comment</span>
                <div class="flex gap-2">
                    <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="px-3 py-1 text-xs bg-red-500 text-white rounded-full hover:bg-red-600 transition">
                        Log In
                    </a>
                    <a href="{{ route('register', ['redirect' => url()->current()]) }}" class="px-3 py-1 text-xs border border-gray-300 rounded-full hover:bg-gray-100 transition">
                        Sign Up
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(isset($isAuthenticated) && $isAuthenticated)
        const commentForm = document.getElementById('comment-form');
        const commentContent = document.getElementById('comment-content');
        const parentId = document.getElementById('parent-id');
        const replyToDiv = document.getElementById('reply-to');
        const replyToName = document.getElementById('reply-to-name');
        const cancelReply = document.getElementById('cancel-reply');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // 回复按钮点击
        document.querySelectorAll('.comment-reply').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.getAttribute('data-id');
                const commenterName = this.closest('.comment').querySelector('.font-semibold').textContent;
                
                parentId.value = commentId;
                replyToName.textContent = commenterName;
                replyToDiv.classList.remove('hidden');
                
                // 滚动到评论表单
                commentContent.focus();
            });
        });
        
        // 取消回复
        cancelReply.addEventListener('click', function() {
            parentId.value = '';
            replyToDiv.classList.add('hidden');
        });
        
        // 提交评论
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const content = commentContent.value.trim();
            if (!content) return;
            
            const postId = {{ $post['id'] ?? 0 }};
            const formData = {
                content: content,
                parent_id: parentId.value || null,
                _token: csrfToken
            };
            
            // 显示加载状态
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Posting...';
            
            fetch('/api/posts/' + postId + '/comments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('网络响应错误');
                }
                return response.json();
            })
            .then(data => {
                // 恢复按钮状态
                submitButton.disabled = false;
                submitButton.textContent = originalText;
                
                if (data.success) {
                    // 清空表单
                    commentContent.value = '';
                    parentId.value = '';
                    replyToDiv.classList.add('hidden');
                    
                    // 添加新评论到列表
                    addNewComment(data.comment);
                    
                    // 更新评论计数
                    updateCommentCount(1);
                } else {
                    alert(data.message || '评论提交失败，请稍后重试');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // 恢复按钮状态
                submitButton.disabled = false;
                submitButton.textContent = originalText;
                alert('评论提交失败，请稍后重试');
            });
        });
        
        // 添加新评论到DOM
        function addNewComment(comment) {
            const commentsList = document.getElementById('comments-list');
            
            const commentElement = document.createElement('div');
            commentElement.className = 'comment';
            commentElement.dataset.id = comment.id;
            commentElement.dataset.userId = comment.user.id || '0';
            commentElement.dataset.likes = '0';
            commentElement.dataset.userLiked = 'false';
            
            commentElement.innerHTML = `
                <img src="${comment.user.avatar || '/images/default-avatar.jpg'}" alt="${comment.user.name}" class="comment-avatar">
                
                <div class="comment-content">
                    <div class="comment-header">
                        <div class="font-semibold">${comment.user.name}</div>
                        <div class="text-sm text-gray-500">Just now</div>
                    </div>
                    
                    <div class="comment-text">
                        ${comment.content}
                    </div>
                    
                    <div class="comment-actions">
                        <button class="comment-like" data-id="${comment.id}">
                            <i class="far fa-heart mr-1"></i> <span class="like-count">0</span> Like
                        </button>
                        <button class="comment-reply" data-id="${comment.id}">
                            <i class="far fa-comment mr-1"></i> Reply
                        </button>
                        <button class="comment-delete text-red-500" data-id="${comment.id}">
                            <i class="far fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            `;
            
            // 添加到列表顶部
            if (commentsList.firstChild) {
                commentsList.insertBefore(commentElement, commentsList.firstChild);
            } else {
                commentsList.appendChild(commentElement);
            }
            
            // 添加事件监听器
            const likeButton = commentElement.querySelector('.comment-like');
            const replyButton = commentElement.querySelector('.comment-reply');
            const deleteButton = commentElement.querySelector('.comment-delete');
            
            likeButton.addEventListener('click', handleCommentLike);
            replyButton.addEventListener('click', handleCommentReply);
            deleteButton.addEventListener('click', handleCommentDelete);
        }
        
        // 评论点赞处理
        function handleCommentLike() {
            const commentId = this.getAttribute('data-id');
            const likeIcon = this.querySelector('i');
            const likeCount = this.querySelector('.like-count');
            const isLiked = likeIcon.classList.contains('fas');
            const commentElement = this.closest('.comment');
            
            // 先进行视觉反馈，再发送请求
            if (!isLiked) {
                likeIcon.classList.remove('far');
                likeIcon.classList.add('fas', 'text-red-500');
                likeCount.textContent = parseInt(likeCount.textContent) + 1;
                commentElement.dataset.userLiked = 'true';
            } else {
                likeIcon.classList.remove('fas', 'text-red-500');
                likeIcon.classList.add('far');
                likeCount.textContent = Math.max(0, parseInt(likeCount.textContent) - 1);
                commentElement.dataset.userLiked = 'false';
            }
            
            // 更新数据属性以保持状态一致
            commentElement.dataset.likes = likeCount.textContent;
            
            fetch(`/api/comments/${commentId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ liked: !isLiked })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('网络响应错误');
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    // 如果请求失败，恢复之前的状态
                    if (isLiked) {
                        likeIcon.classList.remove('far');
                        likeIcon.classList.add('fas', 'text-red-500');
                        likeCount.textContent = parseInt(likeCount.textContent) + 1;
                        commentElement.dataset.userLiked = 'true';
                    } else {
                        likeIcon.classList.remove('fas', 'text-red-500');
                        likeIcon.classList.add('far');
                        likeCount.textContent = Math.max(0, parseInt(likeCount.textContent) - 1);
                        commentElement.dataset.userLiked = 'false';
                    }
                    commentElement.dataset.likes = likeCount.textContent;
                    
                    alert(data.message || '操作失败，请稍后重试');
                } else if (data.likes !== undefined) {
                    // 如果服务器返回了明确的点赞数，以服务器为准
                    likeCount.textContent = data.likes;
                    commentElement.dataset.likes = data.likes;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // 如果发生错误，恢复之前的状态
                if (isLiked) {
                    likeIcon.classList.remove('far');
                    likeIcon.classList.add('fas', 'text-red-500');
                    likeCount.textContent = parseInt(likeCount.textContent) + 1;
                    commentElement.dataset.userLiked = 'true';
                } else {
                    likeIcon.classList.remove('fas', 'text-red-500');
                    likeIcon.classList.add('far');
                    likeCount.textContent = Math.max(0, parseInt(likeCount.textContent) - 1);
                    commentElement.dataset.userLiked = 'false';
                }
                commentElement.dataset.likes = likeCount.textContent;
            });
        }
        
        // 回复评论处理
        function handleCommentReply() {
            const commentId = this.getAttribute('data-id');
            const commenterName = this.closest('.comment').querySelector('.font-semibold').textContent;
            
            parentId.value = commentId;
            replyToName.textContent = commenterName;
            replyToDiv.classList.remove('hidden');
            
            commentContent.focus();
        }
        
        // 删除评论处理
        function handleCommentDelete() {
            if (!confirm('确定要删除这条评论吗？')) return;
            
            const commentId = this.getAttribute('data-id');
            const commentElement = this.closest('.comment');
            
            fetch(`/api/comments/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('网络响应错误');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // 从DOM中移除评论
                    commentElement.remove();
                    
                    // 更新评论计数
                    updateCommentCount(-1);
                } else {
                    alert(data.message || '删除失败，请稍后重试');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('删除失败，请稍后重试');
            });
        }
        
        // 更新评论计数
        function updateCommentCount(change) {
            const commentsCount = document.getElementById('comments-count');
            if (commentsCount) {
                commentsCount.textContent = parseInt(commentsCount.textContent) + change;
            }
        }
        
        // 为现有评论添加事件监听器
        document.querySelectorAll('.comment-like').forEach(button => {
            button.addEventListener('click', handleCommentLike);
            
            // 确保点赞按钮显示正确的样式和数量
            const commentElement = button.closest('.comment');
            const isLiked = commentElement.dataset.userLiked === 'true';
            const likeCount = commentElement.dataset.likes || '0';
            const likeIcon = button.querySelector('i');
            
            // 更新点赞图标样式
            if (isLiked && !likeIcon.classList.contains('fas')) {
                likeIcon.classList.remove('far');
                likeIcon.classList.add('fas', 'text-red-500');
            } else if (!isLiked && !likeIcon.classList.contains('far')) {
                likeIcon.classList.remove('fas', 'text-red-500');
                likeIcon.classList.add('far');
            }
            
            // 更新点赞计数
            const likeCountSpan = button.querySelector('.like-count');
            if (likeCountSpan) {
                likeCountSpan.textContent = likeCount;
            }
        });

        // 为现有评论添加删除按钮和事件监听器
        document.querySelectorAll('.comment').forEach(comment => {
            const commentActions = comment.querySelector('.comment-actions');
            if (!commentActions) return; // 确保元素存在
            
            const likeButton = commentActions.querySelector('.comment-like');
            if (!likeButton) return; // 确保元素存在
            
            const commentId = likeButton.getAttribute('data-id');
            
            // 添加删除按钮（仅对当前用户的评论）
            const currentUserId = '{{ Auth::id() ?? 0 }}';
            const commentUserId = comment.getAttribute('data-user-id') || '0';
            
            // 确认删除按钮不存在才添加
            if ((currentUserId === commentUserId || currentUserId === '1') && !commentActions.querySelector('.comment-delete')) { 
                const deleteButton = document.createElement('button');
                deleteButton.className = 'comment-delete text-red-500';
                deleteButton.setAttribute('data-id', commentId);
                deleteButton.innerHTML = '<i class="far fa-trash-alt mr-1"></i> Delete';
                deleteButton.addEventListener('click', handleCommentDelete);
                commentActions.appendChild(deleteButton);
            }
        });
    @endif
});
</script> 