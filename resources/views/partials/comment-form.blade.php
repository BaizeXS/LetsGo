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

@if(isset($isAuthenticated) && $isAuthenticated)
<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const elements = {
        form: document.getElementById('comment-form'),
        content: document.getElementById('comment-content'),
        parentId: document.getElementById('parent-id'),
        replyToDiv: document.getElementById('reply-to'),
        replyToName: document.getElementById('reply-to-name'),
        cancelReply: document.getElementById('cancel-reply'),
        commentsList: document.getElementById('comments-list'),
        commentsCount: document.getElementById('comments-count')
    };
    
    // Constants
    const postId = {{ $post['id'] ?? 0 }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const currentUserId = '{{ Auth::id() ?? session()->get('mock_user.id', 0) }}';
    const currentUserName = '{{ Auth::user()->name ?? session()->get('mock_user.name', 'Current User') }}';
    const currentUserAvatar = '{{ Auth::user()->avatar ?? session()->get('mock_user.avatar', '/images/default-avatar.jpg') }}';
    
    // Local Storage Keys
    const STORAGE_KEY = 'post_' + postId + '_comments';
    
    // Initialize comments
    initComments();
    setupEventListeners();
    
    // Function to initialize comments
    function initComments() {
        loadLocalComments();
        setupExistingComments();
    }
    
    // Load comments from local storage
    function loadLocalComments() {
        try {
            const storedComments = getLocalComments();
            
            if (storedComments.length > 0) {
                const existingCommentIds = getExistingCommentIds();
                
                // Add comments that don't exist yet
                storedComments.forEach(comment => {
                    if (!existingCommentIds.includes(comment.id)) {
                        addNewComment(comment);
                        updateCommentCount(1);
                    }
                });
            }
        } catch (e) {
            console.error('Error loading comments from localStorage:', e);
        }
    }
    
    // Set up event listeners
    function setupEventListeners() {
        // Reply buttons
        document.querySelectorAll('.comment-reply').forEach(button => {
            button.addEventListener('click', handleCommentReply);
        });
        
        // Cancel reply
        if (elements.cancelReply) {
            elements.cancelReply.addEventListener('click', cancelReply);
        }
        
        // Submit comment
        if (elements.form) {
            elements.form.addEventListener('submit', submitComment);
        }
    }
    
    // Set up existing comments
    function setupExistingComments() {
        // Add event listeners to existing comment actions
        document.querySelectorAll('.comment-like').forEach(button => {
            button.addEventListener('click', handleCommentLike);
            updateLikeButtonState(button);
        });

        // Add delete buttons to user's comments
        document.querySelectorAll('.comment').forEach(addDeleteButtonIfNeeded);
    }
    
    // Update like button state based on data attributes
    function updateLikeButtonState(button) {
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
    }
    
    // Add delete button to comment if needed
    function addDeleteButtonIfNeeded(comment) {
        const commentActions = comment.querySelector('.comment-actions');
        if (!commentActions) return;
        
        const likeButton = commentActions.querySelector('.comment-like');
        if (!likeButton) return;
        
        const commentId = likeButton.getAttribute('data-id');
        const commentUserId = comment.getAttribute('data-user-id') || '0';
        
        // Add delete button only if it doesn't exist and user owns the comment or is admin
        if ((currentUserId === commentUserId || currentUserId === '1') && !commentActions.querySelector('.comment-delete')) { 
            const deleteButton = document.createElement('button');
            deleteButton.className = 'comment-delete text-red-500';
            deleteButton.setAttribute('data-id', commentId);
            deleteButton.innerHTML = '<i class="far fa-trash-alt mr-1"></i> Delete';
            deleteButton.addEventListener('click', handleCommentDelete);
            commentActions.appendChild(deleteButton);
        }
    }
    
    // Cancel reply
    function cancelReply() {
        elements.parentId.value = '';
        elements.replyToDiv.classList.add('hidden');
    }
    
    // Submit new comment
    function submitComment(e) {
        e.preventDefault();
        
        const content = elements.content.value.trim();
        if (!content) return;
        
        const formData = {
            content: content,
            parent_id: elements.parentId.value || null,
            _token: csrfToken
        };
        
        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Posting...';
        
        try {
            // Generate a random ID for the comment
            const mockCommentId = 'mock_' + Math.floor(Math.random() * 100000);
            
            // Create a mock comment object
            const mockComment = {
                id: mockCommentId,
                content: content,
                user: {
                    id: currentUserId,
                    name: currentUserName,
                    avatar: currentUserAvatar
                },
                parent_id: elements.parentId.value || null,
                created_at: 'Just now',
                likes: 0,
                user_liked: false
            };
            
            // Add comment to DOM
            addNewComment(mockComment);
            updateCommentCount(1);
            
            // Clear form
            elements.content.value = '';
            cancelReply();
            
            // Store comment in localStorage
            saveCommentToLocalStorage(mockComment);
            
            // If database is available, try to send to server
            saveCommentToServer(formData, postId);
        } catch (error) {
            console.error('Error:', error);
            alert('Comment submission failed, please try again later');
        } finally {
            // Restore button state
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    }
    
    // Add new comment to DOM
    function addNewComment(comment) {
        const commentElement = document.createElement('div');
        commentElement.className = 'comment';
        commentElement.dataset.id = comment.id;
        commentElement.dataset.userId = comment.user.id || '0';
        commentElement.dataset.likes = comment.likes || '0';
        commentElement.dataset.userLiked = comment.user_liked ? 'true' : 'false';
        
        commentElement.innerHTML = `
            <img src="${comment.user.avatar || '/images/default-avatar.jpg'}" alt="${comment.user.name}" class="comment-avatar">
            
            <div class="comment-content">
                <div class="comment-header">
                    <div class="font-semibold">${comment.user.name}</div>
                    <div class="text-sm text-gray-500">${comment.created_at || 'Just now'}</div>
                </div>
                
                <div class="comment-text">
                    ${comment.content}
                </div>
                
                <div class="comment-actions">
                    <button class="comment-like" data-id="${comment.id}">
                        <i class="far fa-heart mr-1"></i> <span class="like-count">${comment.likes || 0}</span> Like
                    </button>
                    <button class="comment-reply" data-id="${comment.id}">
                        <i class="far fa-comment mr-1"></i> Reply
                    </button>
                    ${(comment.user.id === currentUserId || currentUserId === '1') ? 
                        `<button class="comment-delete text-red-500" data-id="${comment.id}">
                            <i class="far fa-trash-alt mr-1"></i> Delete
                        </button>` : ''}
                </div>
            </div>
        `;
        
        // Add to list top
        if (elements.commentsList.firstChild) {
            elements.commentsList.insertBefore(commentElement, elements.commentsList.firstChild);
        } else {
            elements.commentsList.appendChild(commentElement);
        }
        
        // Add event listeners
        const likeButton = commentElement.querySelector('.comment-like');
        const replyButton = commentElement.querySelector('.comment-reply');
        const deleteButton = commentElement.querySelector('.comment-delete');
        
        likeButton.addEventListener('click', handleCommentLike);
        replyButton.addEventListener('click', handleCommentReply);
        if (deleteButton) {
            deleteButton.addEventListener('click', handleCommentDelete);
        }
    }
    
    // Handle comment like
    function handleCommentLike() {
        const commentId = this.getAttribute('data-id');
        const likeIcon = this.querySelector('i');
        const likeCount = this.querySelector('.like-count');
        const isLiked = likeIcon.classList.contains('fas');
        const commentElement = this.closest('.comment');
        
        // Update UI first for immediate feedback
        const newLikeCount = updateLikeUI(likeIcon, likeCount, isLiked, commentElement);
        
        // Update localStorage
        updateLikeInLocalStorage(commentId, newLikeCount, !isLiked);
        
        // Send to server if available
        updateLikeOnServer(commentId, !isLiked);
    }
    
    // Update like UI
    function updateLikeUI(likeIcon, likeCount, isLiked, commentElement) {
        let newCount;
        
        if (!isLiked) {
            likeIcon.classList.remove('far');
            likeIcon.classList.add('fas', 'text-red-500');
            newCount = parseInt(likeCount.textContent) + 1;
            commentElement.dataset.userLiked = 'true';
        } else {
            likeIcon.classList.remove('fas', 'text-red-500');
            likeIcon.classList.add('far');
            newCount = Math.max(0, parseInt(likeCount.textContent) - 1);
            commentElement.dataset.userLiked = 'false';
        }
        
        likeCount.textContent = newCount;
        commentElement.dataset.likes = newCount;
        
        return newCount;
    }
    
    // Handle comment reply
    function handleCommentReply() {
        const commentId = this.getAttribute('data-id');
        const commenterName = this.closest('.comment').querySelector('.font-semibold').textContent;
        
        elements.parentId.value = commentId;
        elements.replyToName.textContent = commenterName;
        elements.replyToDiv.classList.remove('hidden');
        
        elements.content.focus();
    }
    
    // Handle comment delete
    function handleCommentDelete() {
        if (!confirm('Are you sure you want to delete this comment?')) return;
        
        const commentId = this.getAttribute('data-id');
        const commentElement = this.closest('.comment');
        
        // Remove from DOM
        commentElement.remove();
        updateCommentCount(-1);
        
        // Remove from localStorage
        removeCommentFromLocalStorage(commentId);
        
        // Delete from server if available
        deleteCommentFromServer(commentId);
    }
    
    // Update comment count
    function updateCommentCount(change) {
        if (elements.commentsCount) {
            elements.commentsCount.textContent = parseInt(elements.commentsCount.textContent) + change;
        }
    }
    
    // Local Storage Helper Functions
    function getLocalComments() {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    }
    
    function getExistingCommentIds() {
        return Array.from(elements.commentsList.querySelectorAll('.comment'))
            .map(el => el.dataset.id);
    }
    
    function saveCommentToLocalStorage(comment) {
        try {
            let storedComments = getLocalComments();
            storedComments.unshift(comment);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(storedComments));
        } catch (e) {
            console.error('Error storing comment in localStorage:', e);
        }
    }
    
    function updateLikeInLocalStorage(commentId, likeCount, isLiked) {
        try {
            let storedComments = getLocalComments();
            const commentIndex = storedComments.findIndex(comment => comment.id === commentId);
            
            if (commentIndex !== -1) {
                storedComments[commentIndex].likes = likeCount;
                storedComments[commentIndex].user_liked = isLiked;
                localStorage.setItem(STORAGE_KEY, JSON.stringify(storedComments));
            }
        } catch (e) {
            console.error('Error updating like in localStorage:', e);
        }
    }
    
    function removeCommentFromLocalStorage(commentId) {
        try {
            let storedComments = getLocalComments();
            storedComments = storedComments.filter(comment => comment.id !== commentId);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(storedComments));
        } catch (e) {
            console.error('Error removing comment from localStorage:', e);
        }
    }
    
    // Server API Functions
    function saveCommentToServer(formData, postId) {
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
    }
    
    function updateLikeOnServer(commentId, isLiked) {
        fetch(`/api/comments/${commentId}/like`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ liked: isLiked })
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
                // If server returns accurate count, update UI and localStorage
                const likeButton = document.querySelector(`.comment-like[data-id="${commentId}"]`);
                if (likeButton) {
                    const likeCount = likeButton.querySelector('.like-count');
                    likeCount.textContent = data.likes;
                    
                    const commentElement = likeButton.closest('.comment');
                    commentElement.dataset.likes = data.likes;
                    
                    // Update localStorage
                    updateLikeInLocalStorage(commentId, data.likes, isLiked);
                }
            }
        })
        .catch(error => {
            console.log('Like updated via local storage, but server connection failed:', error);
        });
    }
    
    function deleteCommentFromServer(commentId) {
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
});
</script>
@endif 