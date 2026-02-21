@extends('layouts.app')

@section('title', 'AI Assistant - Nexus')

@section('content')
<div class="ai-page">
    <div class="ai-header">
        <h1>
            <i class="fas fa-robot"></i>
            AI Assistant
        </h1>
        <button class="clear-chat-btn" onclick="clearChat()" title="Clear all messages">
            <i class="fas fa-trash-alt"></i>
            Clear Chat
        </button>
    </div>

    <!-- Chat Interface -->
    <div class="chat-container">
        <!-- Chat Messages Area -->
        <div class="chat-messages" id="chatMessages">
            <div class="welcome-message">
                <div class="message ai">
                    <div class="ai-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-bubble ai-bubble">
                        <div class="message-content">
                            <p>🤖 <strong>Nexus AI Assistant</strong></p>
                            <p>Welcome! Choose an option by typing the number:</p>
                            <div class="menu-options">
                                <div class="menu-item">
                                    <span class="menu-icon">1️⃣</span>
                                    <span class="menu-text">Help & Menu</span>
                                </div>
                                <div class="menu-item">
                                    <span class="menu-icon">2️⃣</span>
                                    <span class="menu-text">Writing Posts</span>
                                </div>
                                <div class="menu-item">
                                    <span class="menu-icon">3️⃣</span>
                                    <span class="menu-text">Follow Suggestions</span>
                                </div>
                                <div class="menu-item">
                                    <span class="menu-icon">4️⃣</span>
                                    <span class="menu-text">Trending Topics</span>
                                </div>
                                <div class="menu-item">
                                    <span class="menu-icon">5️⃣</span>
                                    <span class="menu-text">Privacy Guide</span>
                                </div>
                                <div class="menu-item">
                                    <span class="menu-icon">6️⃣</span>
                                    <span class="menu-text">Engagement Tips</span>
                                </div>
                                <div class="menu-item">
                                    <span class="menu-icon">7️⃣</span>
                                    <span class="menu-text">Stories Guide</span>
                                </div>
                                <div class="menu-item">
                                    <span class="menu-icon">8️⃣</span>
                                    <span class="menu-text">Profile Setup</span>
                                </div>
                                <div class="menu-item">
                                    <span class="menu-icon">9️⃣</span>
                                    <span class="menu-text">Search & Discover</span>
                                </div>
                            </div>
                            <p><em>What would you like help with? Just type a number!</em></p>
                        </div>
                        <div class="message-time">{{ now()->format('H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Input - Static for mobile, above mobile nav -->
    <div class="chat-input-container">
        <div class="input-container">
            <input
                type="text"
                id="chatInput"
                placeholder="Type a number (1-9)..."
                maxlength="1"
                autocomplete="off"
                inputmode="numeric"
                pattern="[1-9]"
            >
            <button type="button" id="sendButton" class="send-button" disabled>
                <i class="fas fa-paper-plane"></i>
            </button>
            <button type="button" id="ai-stop-button" class="ai-stop-button" style="display: none;">
                <i class="fas fa-stop"></i>
            </button>
        </div>
    </div>
</div>

<style>
.ai-page {
    max-width: 100%;
    margin: 0 auto;
    padding: 20px;
    padding-bottom: 100px;
    width: 100%;
}

/* Laptop view - expand chat area to fill available space */
@media (min-width: 1024px) {
    .ai-page {
        max-width: 100%;
        padding: 24px 40px;
    }

    .chat-container {
        max-width: 100%;
        border-radius: 20px;
    }

    .chat-messages {
        padding: 32px 48px;
    }

    .message-bubble {
        max-width: 70%;
    }

    .input-container {
        max-width: 1200px;
    }
}

@media (min-width: 1440px) {
    .ai-page {
        padding: 32px 80px;
    }

    .chat-messages {
        padding: 40px 64px;
    }

    .message-bubble {
        max-width: 65%;
    }
}

.ai-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
    position: sticky;
    top: 0;
    background: var(--card-bg);
    z-index: 100;
}

.ai-header h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: var(--twitter-dark);
    display: flex;
    align-items: center;
    gap: 12px;
}

.ai-header h1 i {
    color: var(--twitter-blue);
}

.clear-chat-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--error-color);
    color: white;
    border: none;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(244, 33, 46, 0.3);
}

.clear-chat-btn:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(244, 33, 46, 0.4);
}

.clear-chat-btn i {
    font-size: 12px;
}

/* Chat Container */
.chat-container {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

/* Chat Messages */
.chat-messages {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 24px;
    height: calc(100vh - 280px);
    min-height: 400px;
    overflow-y: auto;
    scroll-behavior: smooth;
}

.welcome-message {
    margin-bottom: 20px;
}

/* Message Styles */
.message {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    width: 100%;
    animation: messageSlideIn 0.3s ease-out;
}

.message.user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message.ai {
    align-self: flex-start;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.ai-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--twitter-blue) 0%, #8B5CF6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    flex-shrink: 0;
    margin-top: 2px;
}

.message-bubble {
    padding: 14px 18px;
    border-radius: 18px;
    font-size: 14px;
    line-height: 1.5;
    word-wrap: break-word;
    position: relative;
    max-width: 80%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.ai-bubble {
    background: var(--twitter-light);
    color: var(--twitter-dark);
    border-bottom-left-radius: 4px;
    border: 1px solid var(--border-color);
}

.user-bubble {
    background: var(--twitter-blue);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-content p {
    margin: 0 0 8px 0;
}

.message-content p:last-child {
    margin-bottom: 0;
}

.message-time {
    font-size: 11px;
    color: var(--twitter-gray);
    margin-top: 6px;
    text-align: right;
}

.user-bubble .message-time {
    color: rgba(255,255,255,0.8);
}

.menu-options {
    margin: 16px 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: var(--card-bg);
    border-radius: 10px;
    font-size: 14px;
    color: var(--twitter-dark);
    border: 1px solid var(--border-color);
    transition: all 0.2s ease;
}

.menu-item:hover {
    background: var(--hover-bg);
    transform: translateX(4px);
}

.menu-icon {
    font-size: 16px;
}

.menu-text {
    flex: 1;
    font-weight: 500;
}

.typing-text {
    display: inline;
}

.typing-cursor {
    display: inline-block;
    color: var(--twitter-blue);
    font-weight: bold;
    animation: cursorBlink 0.8s infinite;
}

@keyframes cursorBlink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

/* Chat Input Container - Fixed above mobile nav for mobile */
.chat-input-container {
    background: var(--card-bg);
    border-top: 1px solid var(--border-color);
    padding: 16px 20px;
    margin-top: 16px;
    position: fixed;
    bottom: 60px;
    left: 0;
    right: 0;
    z-index: 1001;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
}

/* Chat Input Fixed - Desktop only */
@media (min-width: 901px) {
    .chat-input-container {
        position: fixed;
        bottom: 10px;
        left: 0;
        right: 0;
        z-index: 1000;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
        margin-top: 0;
    }
}

.input-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    background: var(--twitter-light);
    border-radius: 24px;
    padding: 0 16px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.input-container:focus-within {
    border-color: var(--twitter-blue);
    background: var(--card-bg);
    box-shadow: 0 0 0 3px rgba(29, 161, 242, 0.1);
}

.input-container input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    padding: 14px 12px;
    font-size: 16px;
    color: var(--twitter-dark);
    font-family: inherit;
}

.input-container input::placeholder {
    color: var(--twitter-gray);
}

.send-button {
    background: var(--twitter-blue);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    margin-left: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(29, 161, 242, 0.3);
}

.send-button:hover:not(:disabled) {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(29, 161, 242, 0.4);
}

.send-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.send-button i {
    font-size: 16px;
}

.ai-stop-button {
    background: var(--card-bg);
    border: 2px solid var(--twitter-blue);
    color: var(--twitter-blue);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    margin-left: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.ai-stop-button:hover {
    background: var(--twitter-blue);
    color: white;
    transform: scale(1.05);
}

.ai-stop-button i {
    font-size: 14px;
}

/* Enhanced Scrollbar */
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: transparent;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: var(--twitter-gray);
}

/* Typing indicator */
.typing-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 0;
}

.typing-dots {
    display: flex;
    gap: 4px;
}

.typing-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--twitter-gray);
    animation: typingBounce 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) { animation-delay: -0.32s; }
.typing-dots span:nth-child(2) { animation-delay: -0.16s; }
.typing-dots span:nth-child(3) { animation-delay: 0s; }

@keyframes typingBounce {
    0%, 80%, 100% {
        transform: scale(0.8);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Mobile Responsive */
@media (max-width: 900px) {
    .ai-page {
        padding: 0;
        margin: 0;
        width: 100%;
        max-width: 100%;
    }

    .ai-header {
        flex-direction: row;
        gap: 8px;
        padding: 12px 16px;
        margin-bottom: 8px;
    }

    .ai-header h1 {
        font-size: 16px;
    }

    .clear-chat-btn {
        padding: 6px 10px;
        font-size: 11px;
    }

    .clear-chat-btn i {
        display: none;
    }

    .chat-container {
        border-radius: 0;
        margin: 0;
        height: calc(100vh - 240px);
    }

    .chat-messages {
        padding: 12px;
        height: calc(100vh - 300px);
        min-height: auto;
        gap: 12px;
    }

    .message {
        gap: 8px;
    }

    .ai-avatar {
        width: 28px;
        height: 28px;
        font-size: 11px;
    }

    .message-bubble {
        max-width: 85%;
        padding: 10px 12px;
        font-size: 13px;
        border-radius: 14px;
    }

    .menu-options {
        gap: 6px;
    }

    .menu-item {
        padding: 8px 10px;
        font-size: 12px;
    }

    .menu-icon {
        font-size: 14px;
    }

    .chat-input-container {
        padding: 12px 16px;
    }

    .input-container {
        padding: 0 12px;
        border-radius: 20px;
    }

    .input-container input {
        padding: 10px 8px;
        font-size: 14px;
    }

    .input-container input::placeholder {
        font-size: 13px;
    }

    .send-button,
    .ai-stop-button {
        width: 32px;
        height: 32px;
    }

    .send-button i,
    .ai-stop-button i {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .ai-page {
        padding-bottom: 70px;
    }

    .ai-header h1 {
        font-size: 16px;
    }

    .ai-header h1 i {
        font-size: 18px;
    }

    .clear-chat-btn {
        font-size: 11px;
        padding: 6px 10px;
    }

    .chat-messages {
        padding: 10px;
        height: calc(100vh - 280px);
    }

    .message-bubble {
        max-width: 90%;
        padding: 8px 10px;
        font-size: 12px;
    }

    .message-time {
        font-size: 10px;
    }

    .menu-item {
        padding: 6px 8px;
        font-size: 11px;
    }

    .welcome-message .message-content p {
        font-size: 12px;
    }

    .welcome-message strong {
        font-size: 13px;
    }

    .input-container input {
        padding: 8px 6px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatInput = document.getElementById('chatInput');
    const sendButton = document.getElementById('sendButton');
    const chatMessages = document.getElementById('chatMessages');

    // Enable/disable send button based on input
    chatInput.addEventListener('input', function() {
        // Only allow numbers 1-9
        this.value = this.value.replace(/[^1-9]/g, '');
        
        const hasText = this.value.trim().length > 0;
        sendButton.disabled = !hasText;
    });

    // Prevent non-numeric characters
    chatInput.addEventListener('keypress', function(e) {
        if (!/^[1-9]$/.test(e.key)) {
            e.preventDefault();
        }
    });

    // Handle Enter key
    chatInput.addEventListener('keypress', function(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    // Send button click
    sendButton.addEventListener('click', sendMessage);

    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        // Add user message
        addMessage(message, 'user');
        chatInput.value = '';
        sendButton.disabled = true;

        // Show typing indicator
        const typingIndicator = showTypingIndicator();

        // Send to AI endpoint
        fetch('/ai/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Hide typing indicator
            hideTypingIndicator(typingIndicator);

            if (data.success && data.response) {
                // Add AI response with typing effect
                addMessageWithTyping(data.response, 'ai');
            } else {
                addMessage('Sorry, I encountered an error. Please try again.', 'ai');
            }
        })
        .catch(error => {
            console.error('AI chat error:', error);
            hideTypingIndicator(typingIndicator);
            addMessage('Sorry, I\'m having trouble connecting right now. Please try again later.', 'ai');
        });
    }

    function addMessage(text, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message ' + type;

        const timestamp = new Date().toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });

        if (type === 'user') {
            messageDiv.innerHTML = `
                <div class="message-bubble user-bubble">
                    <div class="message-content">
                        <p>${escapeHtml(text)}</p>
                    </div>
                    <div class="message-time">${timestamp}</div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="ai-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-bubble ai-bubble">
                    <div class="message-content">
                        <p>${formatAIResponse(text)}</p>
                    </div>
                    <div class="message-time">${timestamp}</div>
                </div>
            `;
        }

        chatMessages.appendChild(messageDiv);
        scrollToBottom();
    }

    function addMessageWithTyping(fullText, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message ' + type;

        const timestamp = new Date().toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });

        messageDiv.innerHTML = `
            <div class="ai-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-bubble ai-bubble">
                <div class="message-content">
                    <span class="typing-text"></span>
                    <span class="typing-cursor">|</span>
                </div>
                <div class="message-time">${timestamp}</div>
            </div>
        `;

        chatMessages.appendChild(messageDiv);
        scrollToBottom();

        // Start typing animation
        typeText(messageDiv, fullText);
    }

    function typeText(messageDiv, fullText) {
        const textElement = messageDiv.querySelector('.typing-text');
        const cursorElement = messageDiv.querySelector('.typing-cursor');
        let currentIndex = 0;
        let isTyping = true;
        let typingTimeout;

        // Show stop button
        const stopButton = document.getElementById('ai-stop-button');
        stopButton.style.display = 'flex';
        stopButton.onclick = function() {
            isTyping = false;
            clearTimeout(typingTimeout);
            stopButton.style.display = 'none';
            if (cursorElement) {
                cursorElement.style.display = 'none';
            }
            textElement.innerHTML = formatAIResponse(fullText);
            // Scroll to bottom after stopping
            setTimeout(scrollToBottom, 50);
        };

        function typeCharacter() {
            if (currentIndex < fullText.length && isTyping) {
                const char = fullText[currentIndex];
                textElement.innerHTML = formatAIResponse(fullText.substring(0, currentIndex + 1));
                currentIndex++;
                scrollToBottom();

                // Variable typing speed
                const typingSpeed = getTypingSpeed(char);
                typingTimeout = setTimeout(typeCharacter, typingSpeed);
            } else {
                // Typing complete
                if (cursorElement) {
                    cursorElement.style.display = 'none';
                }
                stopButton.style.display = 'none';
            }
        }

        typeCharacter();
    }

    function getTypingSpeed(char) {
        if (char === ' ' || char === '\n') return 30;
        if (char.match(/[a-z]/)) return 25;
        if (char.match(/[A-Z]/)) return 35;
        if (char.match(/[0-9]/)) return 20;
        if (char.match(/[.,!?;:]/)) return 80;
        return 40;
    }

    function formatAIResponse(text) {
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/•/g, '<br>•')
            .replace(/\n/g, '<br>');
    }

    function showTypingIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'message ai typing-indicator';
        indicator.innerHTML = `
            <div class="ai-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-bubble ai-bubble">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;

        chatMessages.appendChild(indicator);
        scrollToBottom();
        return indicator;
    }

    function hideTypingIndicator(indicator) {
        if (indicator && indicator.parentNode) {
            indicator.parentNode.removeChild(indicator);
        }
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Clear chat functionality
    window.clearChat = function() {
        if (!confirm('Are you sure you want to clear all messages? This action cannot be undone.')) {
            return;
        }

        // Keep only welcome message
        const messages = chatMessages.querySelectorAll('.message:not(.welcome-message .message)');
        messages.forEach(message => message.remove());

        // Reset input
        chatInput.value = '';
        sendButton.disabled = true;

        // Scroll to top
        chatMessages.scrollTop = 0;

        // Show toast notification
        showToast('Chat cleared successfully!', 'success');
    };

    // Toast notification function
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        // Use a solid dark background that works in both themes
        const isLight = document.body.classList.contains('light-theme');
        const bgColor = isLight ? '#ffffff' : '#1a1a2e';
        const textColor = isLight ? '#1e293b' : '#ffffff';
        
        toast.style.cssText = `
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: ${bgColor};
            color: ${textColor};
            padding: 12px 24px;
            border-radius: 24px;
            font-size: 14px;
            z-index: 10000;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease-out;
            border: 1px solid rgba(255,255,255,0.1);
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
</script>
@endsection
