<div class="comment-form mt-4">
    @if(isset($isAuthenticated) && $isAuthenticated)
        <form id="comment-form">
            <textarea id="comment-content" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-red-500" rows="3" placeholder="Write a comment..."></textarea>
            <input type="hidden" id="parent-id" value="">
            <div class="flex justify-end mt-2">
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-full hover:bg-red-600 transition">
                    Post Comment
                </button>
            </div>
        </form>
        <div id="reply-to" class="bg-gray-50 p-2 rounded mt-2 text-sm hidden">
            Replying to <span id="reply-to-name"></span>
            <button id="cancel-reply" class="text-red-500 ml-2">Cancel</button>
        </div>
    @else
        <div class="bg-gray-50 rounded-lg overflow-hidden">
            <div class="p-4">
                <div class="flex items-center justify-center mb-4">
                    <i class="fas fa-comment-alt text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-center text-lg font-semibold mb-2">Join the conversation</h3>
                <p class="text-center text-gray-600 mb-4">Sign in to post comments and interact with other travelers</p>
                <div class="flex justify-center gap-3">
                    <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="px-4 py-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition">
                        Log In
                    </a>
                    <a href="{{ route('register', ['redirect' => url()->current()]) }}" class="px-4 py-2 border border-gray-300 rounded-full hover:bg-gray-100 transition">
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
                parent_id: parentId.value || null
            };
            
            fetch('/posts/' + postId + '/comments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 清空表单
                    commentContent.value = '';
                    parentId.value = '';
                    replyToDiv.classList.add('hidden');
                    
                    // 添加新评论到列表
                    addNewComment(data.comment);
                    
                    // 更新评论计数
                    updateCommentCount();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
        
        // 添加新评论到DOM
        function addNewComment(comment) {
            const commentsList = document.getElementById('comments-list');
            
            const commentElement = document.createElement('div');
            commentElement.className = 'comment';
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
                            <i class="far fa-heart mr-1"></i> Like
                        </button>
                        <button class="comment-reply" data-id="${comment.id}">
                            <i class="far fa-comment mr-1"></i> Reply
                        </button>
                    </div>
                </div>
            `;
            
            // 添加到列表顶部
            commentsList.insertBefore(commentElement, commentsList.firstChild);
            
            // 添加事件监听器
            const likeButton = commentElement.querySelector('.comment-like');
            const replyButton = commentElement.querySelector('.comment-reply');
            
            likeButton.addEventListener('click', handleCommentLike);
            replyButton.addEventListener('click', handleCommentReply);
        }
        
        // 评论点赞处理
        function handleCommentLike() {
            const commentId = this.getAttribute('data-id');
            
            fetch(`/comments/${commentId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 视觉反馈
                    this.querySelector('i').classList.remove('far');
                    this.querySelector('i').classList.add('fas');
                    this.querySelector('i').classList.add('text-red-500');
                }
            })
            .catch(error => {
                console.error('Error:', error);
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
        
        // 更新评论计数
        function updateCommentCount() {
            const commentsCount = document.getElementById('comments-count');
            if (commentsCount) {
                commentsCount.textContent = parseInt(commentsCount.textContent) + 1;
            }
        }
        
        // 为现有评论添加事件监听器
        document.querySelectorAll('.comment-like').forEach(button => {
            button.addEventListener('click', handleCommentLike);
        });
    @endif
});
</script> 