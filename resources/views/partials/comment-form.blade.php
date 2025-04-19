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
        
        // Load locally stored comments
        const postId = {{ $post['id'] ?? 0 }};
        try {
            const storedComments = JSON.parse(localStorage.getItem('post_' + postId + '_comments') || '[]');
            const commentsList = document.getElementById('comments-list');
            
            // Check if comments already exist on the page (avoid duplicates)
            if (storedComments.length > 0) {
                // Get list of existing comment IDs
                const existingCommentIds = Array.from(commentsList.querySelectorAll('.comment'))
                    .map(el => el.dataset.id);
                
                // Add comments that don't exist yet
                storedComments.forEach(comment => {
                    if (!existingCommentIds.includes(comment.id)) {
                        addNewComment(comment);
                        // Update comment count
                        updateCommentCount(1);
                    }
                });
            }
        } catch (e) {
            console.error('Error loading comments from localStorage:', e);
        }
        
        // Reply button click
        document.querySelectorAll('.comment-reply').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.getAttribute('data-id');
                const commenterName = this.closest('.comment').querySelector('.font-semibold').textContent;
                
                parentId.value = commentId;
                replyToName.textContent = commenterName;
                replyToDiv.classList.remove('hidden');
                
                // Scroll to comment form
                commentContent.focus();
            });
        });
        
        // Cancel reply
        cancelReply.addEventListener('click', function() {
            parentId.value = '';
            replyToDiv.classList.add('hidden');
        });
        
        // Submit comment
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
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Posting...';
            
            // Add local mock implementation
            // Use this mock method to add comments when database connection fails
            try {
                // Generate a random ID for the comment
                const mockCommentId = 'mock_' + Math.floor(Math.random() * 100000);
                
                // Create a mock comment object
                const mockComment = {
                    id: mockCommentId,
                    content: content,
                    user: {
                        id: '{{ Auth::id() ?? session()->get('mock_user.id', 0) }}',
                        name: '{{ Auth::user()->name ?? session()->get('mock_user.name', 'Current User') }}',
                        avatar: '{{ Auth::user()->avatar ?? session()->get('mock_user.avatar', '/images/default-avatar.jpg') }}'
                    },
                    parent_id: parentId.value || null,
                    created_at: 'Just now',
                    likes: 0,
                    user_liked: false
                };
                
                // Add comment to DOM
                addNewComment(mockComment);
                
                // Update comment count
                updateCommentCount(1);
                
                // Clear form
                commentContent.value = '';
                parentId.value = '';
                replyToDiv.classList.add('hidden');
                
                // Restore button state
                submitButton.disabled = false;
                submitButton.textContent = originalText;
                
                // Store comment in localStorage (as a simple persistence solution)
                try {
                    let storedComments = JSON.parse(localStorage.getItem('post_' + postId + '_comments') || '[]');
                    storedComments.unshift(mockComment);
                    localStorage.setItem('post_' + postId + '_comments', JSON.stringify(storedComments));
                } catch (e) {
                    console.error('Error storing comment in localStorage:', e);
                }
                
                // If database is available, try to send to server
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
                        console.log('Comment added via local storage, but server storage failed');
                        return null;
                    }
                    return response.json();
                })
                .catch(error => {
                    console.log('Comment added via local storage, but server connection failed:', error);
                });
            } catch (error) {
                console.error('Error:', error);
                // Restore button state
                submitButton.disabled = false;
                submitButton.textContent = originalText;
                alert('Comment submission failed, please try again later');
            }
        });
        
        // Add new comment to DOM
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
            
            // Add to list top
            if (commentsList.firstChild) {
                commentsList.insertBefore(commentElement, commentsList.firstChild);
            } else {
                commentsList.appendChild(commentElement);
            }
            
            // Add event listeners
            const likeButton = commentElement.querySelector('.comment-like');
            const replyButton = commentElement.querySelector('.comment-reply');
            const deleteButton = commentElement.querySelector('.comment-delete');
            
            likeButton.addEventListener('click', handleCommentLike);
            replyButton.addEventListener('click', handleCommentReply);
            deleteButton.addEventListener('click', handleCommentDelete);
        }
        
        // Comment like handling
        function handleCommentLike() {
            const commentId = this.getAttribute('data-id');
            const likeIcon = this.querySelector('i');
            const likeCount = this.querySelector('.like-count');
            const isLiked = likeIcon.classList.contains('fas');
            const commentElement = this.closest('.comment');
            
            // First do visual feedback, then send request
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
            
            // Update data attribute to keep state consistent
            commentElement.dataset.likes = likeCount.textContent;
            
            // Update localStorage like status
            try {
                const postId = {{ $post['id'] ?? 0 }};
                let storedComments = JSON.parse(localStorage.getItem('post_' + postId + '_comments') || '[]');
                const commentIndex = storedComments.findIndex(comment => comment.id === commentId);
                
                if (commentIndex !== -1) {
                    storedComments[commentIndex].likes = parseInt(likeCount.textContent);
                    storedComments[commentIndex].user_liked = !isLiked;
                    localStorage.setItem('post_' + postId + '_comments', JSON.stringify(storedComments));
                }
            } catch (e) {
                console.error('Error updating like in localStorage:', e);
            }
            
            // If server is available, try to update like status
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
                    console.log('Like updated via local storage, but server update failed');
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (data && data.likes !== undefined) {
                    // If server returns explicit like count, use server
                    likeCount.textContent = data.likes;
                    commentElement.dataset.likes = data.likes;
                    
                    // Also update localStorage
                    try {
                        const postId = {{ $post['id'] ?? 0 }};
                        let storedComments = JSON.parse(localStorage.getItem('post_' + postId + '_comments') || '[]');
                        const commentIndex = storedComments.findIndex(comment => comment.id === commentId);
                        
                        if (commentIndex !== -1) {
                            storedComments[commentIndex].likes = data.likes;
                            localStorage.setItem('post_' + postId + '_comments', JSON.stringify(storedComments));
                        }
                    } catch (e) {
                        console.error('Error updating like count in localStorage:', e);
                    }
                }
            })
            .catch(error => {
                console.log('Like updated via local storage, but server connection failed:', error);
            });
        }
        
        // Reply comment handling
        function handleCommentReply() {
            const commentId = this.getAttribute('data-id');
            const commenterName = this.closest('.comment').querySelector('.font-semibold').textContent;
            
            parentId.value = commentId;
            replyToName.textContent = commenterName;
            replyToDiv.classList.remove('hidden');
            
            commentContent.focus();
        }
        
        // Delete comment handling
        function handleCommentDelete() {
            if (!confirm('Are you sure you want to delete this comment?')) return;
            
            const commentId = this.getAttribute('data-id');
            const commentElement = this.closest('.comment');
            
            // First remove comment from DOM
            commentElement.remove();
            
            // Update comment count
            updateCommentCount(-1);
            
            // Remove comment from localStorage
            try {
                const postId = {{ $post['id'] ?? 0 }};
                let storedComments = JSON.parse(localStorage.getItem('post_' + postId + '_comments') || '[]');
                storedComments = storedComments.filter(comment => comment.id !== commentId);
                localStorage.setItem('post_' + postId + '_comments', JSON.stringify(storedComments));
            } catch (e) {
                console.error('Error removing comment from localStorage:', e);
            }
            
            // If server is available, try to delete from server
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
                    console.log('Comment removed via local storage, but server delete failed');
                    return null;
                }
                return response.json();
            })
            .catch(error => {
                console.log('Comment removed via local storage, but server connection failed:', error);
            });
        }
        
        // Update comment count
        function updateCommentCount(change) {
            const commentsCount = document.getElementById('comments-count');
            if (commentsCount) {
                commentsCount.textContent = parseInt(commentsCount.textContent) + change;
            }
        }
        
        // Add event listeners to existing comments
        document.querySelectorAll('.comment-like').forEach(button => {
            button.addEventListener('click', handleCommentLike);
            
            // Ensure like button shows correct style and count
            const commentElement = button.closest('.comment');
            const isLiked = commentElement.dataset.userLiked === 'true';
            const likeCount = commentElement.dataset.likes || '0';
            const likeIcon = button.querySelector('i');
            
            // Update like icon style
            if (isLiked && !likeIcon.classList.contains('fas')) {
                likeIcon.classList.remove('far');
                likeIcon.classList.add('fas', 'text-red-500');
            } else if (!isLiked && !likeIcon.classList.contains('far')) {
                likeIcon.classList.remove('fas', 'text-red-500');
                likeIcon.classList.add('far');
            }
            
            // Update like count
            const likeCountSpan = button.querySelector('.like-count');
            if (likeCountSpan) {
                likeCountSpan.textContent = likeCount;
            }
        });

        // Add delete button and event listeners to existing comments
        document.querySelectorAll('.comment').forEach(comment => {
            const commentActions = comment.querySelector('.comment-actions');
            if (!commentActions) return; // Ensure element exists
            
            const likeButton = commentActions.querySelector('.comment-like');
            if (!likeButton) return; // Ensure element exists
            
            const commentId = likeButton.getAttribute('data-id');
            
            // Add delete button (only for current user's comments)
            const currentUserId = '{{ Auth::id() ?? 0 }}';
            const commentUserId = comment.getAttribute('data-user-id') || '0';
            
            // Add delete button only if it doesn't exist
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