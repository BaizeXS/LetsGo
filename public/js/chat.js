document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const chatMessages = document.getElementById('chat-messages');
    const chatContainer = document.getElementById('chat-container');
    const tipBtn = document.getElementById('tip-btn');
    const planBtn = document.getElementById('plan-btn');
    
    // CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // State variables
    let currentMode = 'normal'; // normal, trip-plan, travel-tip
    let isTyping = false;
    
    // Configure marked.js, enable safe mode
    marked.setOptions({
        breaks: true,         // Convert line breaks to <br>
        gfm: true,            // Enable GitHub flavored markdown
        headerIds: false,     // Don't generate header IDs
        mangle: false,        // Don't mangle email addresses
        sanitize: false,      // Use DOMPurify instead of built-in sanitize
    });
    
    // Parse Markdown and safely convert to HTML
    function parseMarkdown(text) {
        // Escape special characters to prevent XSS
        if (!text) return '';
        
        // Preserve emoji unicode characters
        // For example: ✨ 💡 etc.
        
        // Use marked.js to parse Markdown
        return marked.parse(text);
    }
    
    // Add message to interface
    function appendMessage(text, sender) {
        const messageElement = document.createElement('li');
        
        // User messages don't need Markdown parsing, AI messages do
        if (sender === 'user') {
            // Escape HTML for user messages, simple text display
            messageElement.textContent = text;
        } else {
            // Parse Markdown for AI messages
            messageElement.innerHTML = parseMarkdown(text);
        }
        
        messageElement.classList.add(sender === 'user' ? 'user-message' : 'ai-message');
        
        chatMessages.appendChild(messageElement);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    // Show "typing" indicator
    function showTypingIndicator() {
        if (isTyping) return;
        
        isTyping = true;
        const indicator = document.createElement('div');
        indicator.classList.add('typing-indicator');
        indicator.id = 'typing-indicator';
        
        for (let i = 0; i < 3; i++) {
            const dot = document.createElement('span');
            indicator.appendChild(dot);
        }
        
        chatMessages.appendChild(indicator);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    // Hide "typing" indicator
    function hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.remove();
            isTyping = false;
        }
    }
    
    // Toggle chat mode
    function toggleChatMode(mode) {
        // Reset all buttons
        [tipBtn, planBtn].forEach(btn => btn.classList.remove('active'));
        
        currentMode = mode;
        
        // Activate current mode button
        if (mode === 'travel-tip') {
            tipBtn.classList.add('active');
        } else if (mode === 'trip-plan') {
            planBtn.classList.add('active');
        }
        
        // Update input box placeholder
        if (mode === 'normal') {
            messageInput.placeholder = "Ask me anything...";
        } else if (mode === 'travel-tip') {
            messageInput.placeholder = "Getting travel tips...";
        } else if (mode === 'trip-plan') {
            messageInput.placeholder = "Generating 7-day travel plan...";
        }
    }
    
    // Send special requests (travel tips, trip plans)
    function sendSpecialRequest(mode) {
        toggleChatMode(mode);
        
        let userMessage = '';
        if (mode === 'travel-tip') {
            userMessage = 'Please give me a travel tip';
            appendMessage(userMessage, 'user');
        } else if (mode === 'trip-plan') {
            userMessage = 'Please generate a 7-day travel plan for me';
            appendMessage(userMessage, 'user');
        }
        
        showTypingIndicator();
        
        fetch("/chat/send", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message: userMessage,
                mode: mode
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            hideTypingIndicator();
            
            if (data && data.reply) {
                appendMessage(data.reply, 'ai');
            } else {
                appendMessage('Sorry, I received an invalid reply.', 'ai');
            }
            
            // Restore to normal mode
            toggleChatMode('normal');
        })
        .catch(error => {
            hideTypingIndicator();
            console.error('Error sending message:', error);
            appendMessage('Sorry, an error occurred while sending the message.', 'ai');
            
            // Restore to normal mode
            toggleChatMode('normal');
        });
    }
    
    // Send message
    function sendMessage(message) {
        if (!message) return;
        
        appendMessage(message, 'user');
        messageInput.value = '';
        
        showTypingIndicator();
        
        fetch("/chat/send", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message: message,
                mode: currentMode
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            hideTypingIndicator();
            
            if (data && data.reply) {
                appendMessage(data.reply, 'ai');
            } else {
                appendMessage('Sorry, I received an invalid reply.', 'ai');
            }
            
            // If not normal mode, restore to normal mode after sending message
            if (currentMode !== 'normal') {
                toggleChatMode('normal');
            }
        })
        .catch(error => {
            hideTypingIndicator();
            console.error('Error sending message:', error);
            appendMessage('Sorry, an error occurred while sending the message.', 'ai');
            
            // Restore to normal mode
            toggleChatMode('normal');
        });
    }
    
    // Bind events
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            sendMessage(message);
        });
    }
    
    if (tipBtn) {
        tipBtn.addEventListener('click', function() {
            sendSpecialRequest('travel-tip');
        });
    }
    
    if (planBtn) {
        planBtn.addEventListener('click', function() {
            sendSpecialRequest('trip-plan');
        });
    }
    
    // Initialize scrolling to bottom
    chatContainer.scrollTop = chatContainer.scrollHeight;
});
