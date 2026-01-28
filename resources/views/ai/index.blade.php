@extends('layouts.app')

@section('title', 'AI Assistant - Laravel Social')

@section('content')
<div class="w-full px-4 py-6">
    <div class="max-w-full mx-auto">
        <!-- Page Header - Ultra Minimized -->
        <div class="ai-header mb-2">
            <h1 class="text-lg font-medium text-gray-600 mb-0">
                <i class="fas fa-robot text-blue-400 text-sm mr-1"></i>
                AI Assistant
            </h1>
            <button class="clear-chat-header-btn" onclick="clearChat()" title="Clear all messages">
                <i class="fas fa-trash-alt"></i>
                Clear Chat
            </button>
        </div>

        <!-- Chat Interface - Full Width -->
        <div class="chat-container bg-white rounded-xl shadow-lg border border-gray-200 relative" style="padding-bottom: 120px;">
            <!-- Chat Messages Area - Full Width -->
            <div class="chat-messages" id="chatMessages" style="height: calc(100vh - 200px); padding: 20px; overflow-y: auto;">
                <div class="welcome-message">
                    <div class="message ai">
                        <div class="ai-avatar-small">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-bubble ai-bubble">
                            <div class="message-content">
                                <p>🤖 <strong>Laravel Social AI Assistant</strong></p>
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

        <!-- Fixed Chat Input - Bottom of Page -->
        <div class="chat-input-fixed">
            <div class="input-container">
                <input
                    type="text"
                    id="chatInput"
                    placeholder="Type a number (1-9) or ask me anything..."
                    maxlength="200"
                    autocomplete="off"
                >
                <button type="button" id="sendButton" class="send-button" disabled>
                    <i class="fas fa-paper-plane"></i>
                </button>
                <button type="button" id="ai-stop-button" class="ai-stop-button" style="display: none;">
                    <i class="fas fa-stop"></i>
                </button>
            </div>
            <div class="input-footer">
                <!-- Removed character counter and input hint -->
            </div>
        </div>
    </div>
</div>

<style>
/* AI Header Layout */
.ai-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

.clear-chat-header-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: var(--error-color);
    color: white;
    border: none;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(244, 33, 46, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.clear-chat-header-btn:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(244, 33, 46, 0.4);
}

.clear-chat-header-btn:active {
    transform: translateY(0);
}

.clear-chat-header-btn i {
    font-size: 11px;
}

/* Chat Container */
.chat-container {
    background: var(--bg-primary);
    border-color: var(--border-primary);
}

/* Chat Interface Styles */
.chat-messages {
    display: flex;
    flex-direction: column;
    gap: 16px;
    scroll-behavior: smooth;
}

.welcome-message {
    margin-bottom: 20px;
}

/* Message Styles */
.message {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    width: 100%;
}

.message.user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message.ai {
    align-self: flex-start;
}

.ai-avatar-small {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3B82F6 0%, #8B5CF6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    flex-shrink: 0;
    margin-top: 2px;
}

.message-bubble {
    padding: 12px 16px;
    border-radius: 18px;
    font-size: 14px;
    line-height: 1.5;
    word-wrap: break-word;
    position: relative;
    max-width: 100%;
    box-shadow: var(--shadow-primary);
}

.message-bubble:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-hover);
}

.ai-bubble {
    background: var(--bg-message-ai);
    color: var(--text-ai);
    border-bottom-left-radius: 4px;
    border: 2px solid rgba(59, 130, 246, 0.3);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
    position: relative;
}

.stop-typing-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    color: #3B82F6;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    z-index: 10;
    opacity: 0;
    transform: scale(0.8);
    animation: fadeIn 0.3s ease forwards;
}

.stop-typing-btn:hover {
    background: white;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.stop-typing-btn i {
    font-size: 12px;
}

@keyframes fadeIn {
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.user-bubble {
    background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
    color: var(--text-user);
    border-bottom-right-radius: 4px;
}

.message-content {
    position: relative;
    z-index: 1;
}

.message-time {
    font-size: 10px;
    color: var(--text-tertiary);
    margin-top: 4px;
    text-align: right;
    opacity: 0.8;
}

.menu-options {
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: var(--bg-tertiary);
    border-radius: 12px;
    font-size: 13px;
    color: var(--text-accent);
    line-height: 1.4;
    border: 1px solid var(--border-secondary);
}

.menu-icon {
    font-size: 14px;
}

.menu-text {
    flex: 1;
}

.typing-text {
    display: inline;
}

.typing-cursor {
    display: inline-block;
    color: var(--text-accent);
    font-family: 'Courier New', monospace;
    font-weight: bold;
    animation: cursorBlink 0.8s infinite;
}

@keyframes cursorBlink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

/* Chat Input */
.chat-input {
    background: var(--bg-primary);
    padding: 16px 20px;
    border-top: 1px solid var(--border-primary);
}

.input-container {
    display: flex;
    align-items: center;
    background: var(--bg-secondary);
    border-radius: 24px;
    padding: 0 16px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.input-container:focus-within {
    border-color: var(--border-focus);
    background: var(--bg-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.input-container input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    padding: 12px 8px;
    font-size: 16px;
    color: var(--text-primary);
    font-family: inherit;
    min-height: 20px;
}

.input-container input::placeholder {
    color: var(--text-tertiary);
    font-style: italic;
}

.send-button {
    background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    margin-left: 8px;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-button);
}

.send-button:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.6);
}

.send-button:active {
    transform: scale(0.95);
}

.send-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
}

.send-button i {
    font-size: 16px;
    position: relative;
    z-index: 1;
}

.ai-stop-button {
    background: rgba(255, 255, 255, 0.9);
    border: 2px solid #3B82F6;
    color: #3B82F6;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    margin-left: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    opacity: 0;
    transform: scale(0.8);
    animation: fadeIn 0.3s ease forwards;
}

.ai-stop-button:hover {
    background: white;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    border-color: #2563EB;
}

.ai-stop-button:active {
    transform: scale(1);
}

.ai-stop-button i {
    font-size: 14px;
    position: relative;
    z-index: 1;
}

.ai-stop-button[style*="display: flex"] {
    opacity: 1;
    transform: scale(1);
}

.input-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    padding: 0 4px;
}

.char-count {
    font-size: 11px;
    color: var(--text-tertiary);
    font-weight: 500;
}

.input-hint {
    font-size: 11px;
    color: var(--text-tertiary);
    font-style: italic;
}

/* Enhanced Scrollbar */
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 3px;
    transition: background-color 0.2s ease;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.5);
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
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--text-tertiary);
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

/* Fixed Chat Input at Bottom */
.chat-input-fixed {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--card-bg);
    border-top: 1px solid var(--border-color);
    padding: 16px 20px;
    z-index: 1000;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
}

.chat-input-fixed .input-container {
    max-width: 1200px;
    margin: 0 auto;
    background: var(--bg-secondary);
    border-radius: 24px;
    padding: 0 16px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.chat-input-fixed .input-container:focus-within {
    border-color: var(--border-focus);
    background: var(--card-bg);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.chat-input-fixed .input-container input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    padding: 12px 8px;
    font-size: 16px;
    color: var(--text-primary);
    font-family: inherit;
    min-height: 20px;
}

.chat-input-fixed .input-container input::placeholder {
    color: var(--text-tertiary);
    font-style: italic;
}

.chat-input-fixed .send-button {
    background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    margin-left: 8px;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-button);
}

.chat-input-fixed .send-button:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.6);
}

.chat-input-fixed .send-button:active {
    transform: scale(0.95);
}

.chat-input-fixed .send-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
}

.chat-input-fixed .send-button i {
    font-size: 16px;
    position: relative;
    z-index: 1;
}

.chat-input-fixed .input-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    padding: 0 4px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}

.chat-input-fixed .char-count {
    font-size: 11px;
    color: var(--text-tertiary);
    font-weight: 500;
}

.chat-input-fixed .input-hint {
    font-size: 11px;
    color: var(--text-tertiary);
    font-style: italic;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .chat-messages {
        padding: 16px;
        gap: 12px;
        height: calc(100vh - 160px) !important;
    }

    .chat-container {
        padding-bottom: 100px !important;
    }

    .chat-input-fixed {
        padding: 12px 16px;
    }

    .chat-input-fixed .input-container {
        padding: 0 12px;
    }

    .chat-input-fixed .input-container input {
        padding: 10px 6px;
        font-size: 16px; /* Prevents zoom on iOS */
    }

    .chat-input-fixed .send-button {
        width: 32px;
        height: 32px;
    }

    .chat-input-fixed .send-button i {
        font-size: 14px;
    }

    .message {
        width: 100%;
    }

    .message-bubble {
        font-size: 14px;
        padding: 10px 14px;
    }

    .ai-avatar-small {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatInput = document.getElementById('chatInput');
    const sendButton = document.getElementById('sendButton');
    const charCount = document.getElementById('charCount');
    const chatMessages = document.getElementById('chatMessages');

    // Enable/disable send button based on input
    chatInput.addEventListener('input', function() {
        const hasText = this.value.trim().length > 0;
        sendButton.disabled = !hasText;
        sendButton.style.opacity = hasText ? '1' : '0.6';
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
                // Add AI response
                addMessage(data.response, 'ai');
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
                    <p>${escapeHtml(text)}</p>
                </div>
                <div class="message-time">${timestamp}</div>
            `;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        } else {
            // AI messages with typing effect
            messageDiv.innerHTML = `
                <div class="ai-avatar-small">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-bubble ai-bubble">
                    <div class="message-content">
                        <span class="typing-text"></span>
                        <span class="typing-cursor">|</span>
                    </div>
                </div>
                <div class="message-time">${timestamp}</div>
            `;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Start typing animation
            typeText(messageDiv, text);
        }
    }

        function typeText(messageDiv, fullText) {
            const textElement = messageDiv.querySelector('.typing-text');
            const cursorElement = messageDiv.querySelector('.typing-cursor');
            const contentElement = messageDiv.querySelector('.message-content');

            let currentIndex = 0;
            let currentText = '';
            let isTyping = true;
            let typingTimeout;

            // Different typing speeds for different characters
            function getTypingSpeed(char) {
                if (char === ' ' || char === '\n' || char === '\t') {
                    return 30 + Math.random() * 20; // Faster for whitespace
                } else if (char.match(/[a-z]/)) {
                    return 25 + Math.random() * 15; // Fast for lowercase
                } else if (char.match(/[A-Z]/)) {
                    return 35 + Math.random() * 15; // Medium-fast for uppercase
                } else if (char.match(/[0-9]/)) {
                    return 20 + Math.random() * 10; // Very fast for numbers
                } else if (char.match(/[.,!?;:]/)) {
                    return 80 + Math.random() * 40; // Slower for punctuation
                } else {
                    return 40 + Math.random() * 20; // Medium for symbols
                }
            }

            // Cursor blink animation
            const cursorInterval = setInterval(() => {
                if (cursorElement) {
                    cursorElement.style.opacity = cursorElement.style.opacity === '0' ? '1' : '0';
                }
            }, 100);

            function typeCharacter() {
                if (currentIndex < fullText.length && isTyping) {
                    const char = fullText[currentIndex];
                    currentText += char;
                    textElement.innerHTML = formatAIResponse(currentText);

                    currentIndex++;

                    // Auto-scroll to keep typing visible
                    chatMessages.scrollTop = chatMessages.scrollHeight;

                    // Continue typing with realistic speed
                    const typingSpeed = getTypingSpeed(char);
                    typingTimeout = setTimeout(typeCharacter, typingSpeed);
                } else {
                    // Typing complete
                    clearInterval(cursorInterval);
                    if (cursorElement) {
                        cursorElement.style.display = 'none';
                    }
                    isTyping = false;
                    // Remove stop button when typing is complete
                    const stopButton = document.getElementById('ai-stop-button');
                    if (stopButton) {
                        stopButton.style.display = 'none';
                    }
                }
            }

            // Show stop button in input bar
            const stopButton = document.getElementById('ai-stop-button');
            if (stopButton) {
                stopButton.style.display = 'flex';
                stopButton.onclick = function() {
                    stopTyping(messageDiv, textElement, cursorElement, cursorInterval, typingTimeout, fullText);
                };
            }

            // Start typing
            setTimeout(typeCharacter, 100);

            return { stopButton, cursorInterval, typingTimeout };
        }

        function stopTyping(messageDiv, textElement, cursorElement, cursorInterval, typingTimeout, fullText) {
            // Clear typing timeout
            if (typingTimeout) {
                clearTimeout(typingTimeout);
            }

            // Stop cursor animation
            clearInterval(cursorInterval);
            if (cursorElement) {
                cursorElement.style.display = 'none';
            }

            // Hide stop button
            const stopButton = document.getElementById('ai-stop-button');
            if (stopButton) {
                stopButton.style.display = 'none';
            }

            // Show full text immediately
            textElement.innerHTML = formatAIResponse(fullText);

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

    function formatAIResponse(text) {
        // Convert markdown-style formatting to HTML
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/•/g, '<br>•')
            .replace(/\n/g, '<br>')
            .replace(/<br><br>•/g, '<br>•'); // Fix bullet points
    }

    function showTypingIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'message ai typing-indicator';
        indicator.innerHTML = `
            <div class="ai-avatar-small">
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
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return indicator;
    }

    function hideTypingIndicator(indicator) {
        if (indicator && indicator.parentNode) {
            indicator.parentNode.removeChild(indicator);
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Clear chat functionality
    window.clearChat = function() {
        if (!confirm('Are you sure you want to clear all messages in this chat? This action cannot be undone.')) {
            return;
        }

        // Clear all messages except the welcome message
        const messages = chatMessages.querySelectorAll('.message:not(.welcome-message)');
        messages.forEach(message => message.remove());

        // Reset input
        chatInput.value = '';
        sendButton.disabled = true;
        sendButton.style.opacity = '0.6';

        // Scroll to top
        chatMessages.scrollTop = 0;

        // Show confirmation
        showToast('Chat cleared successfully!', 'info', 2000);
    };
});
</script>
@endsection