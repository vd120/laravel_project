@extends('layouts.app')

@section('title', 'AI Assistant - Nexus')

@section('content')
<style>
/* Override layout constraints for full width AI chat */
.app-layout, .main-content {
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
}

:root {
    --wa-bg: var(--bg, #111b21);
    --wa-panel: var(--surface, #202c33);
    --wa-panel-hover: var(--surface-hover, #2a3942);
    --wa-border: var(--border, #2f3b43);
    --wa-text: var(--text, #e9edef);
    --wa-text-muted: var(--text-muted, #8696a0);
    --wa-accent: var(--primary, #00a884);
    --wa-blue: var(--primary, #53bdeb);
    --wa-green: var(--success, #25d366);
    --wa-red: var(--error, #ef4444);
    --wa-yellow: var(--warning, #ffd60a);
}

* { box-sizing: border-box; }

.ai-page {
    height: calc(100vh - 64px);
    background: var(--wa-bg);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 100%;
}

/* Mobile - adjust height for mobile nav */
@media (max-width: 900px) {
    /* Hide mobile nav on AI page */
    .mobile-nav {
        display: none !important;
    }

    .back-btn {
        display: flex !important;
    }

    .ai-page {
        padding-top: 0;
    }

    .input-area {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--wa-panel);
        border-top: 1px solid var(--wa-border);
        z-index: 1000;
    }

    .chat-container {
        padding-bottom: 100px;
    }
}

/* Laptop view - full width */
@media (min-width: 1024px) {
    .ai-page {
        width: 100% !important;
        max-width: 100% !important;
    }

    .chat-container {
        padding: 24px 32px;
    }

    .message-bubble {
        max-width: 70%;
    }
}

@media (min-width: 1440px) {
    .chat-container {
        padding: 32px 48px;
    }

    .message-bubble {
        max-width: 65%;
    }
}

/* Header */
.ai-header {
    display: flex;
    align-items: center;
    padding: 12px 24px;
    background: var(--wa-panel);
    border-bottom: 1px solid var(--wa-border);
    gap: 12px;
    width: 100%;
}

.back-btn {
    display: none;
    width: 38px;
    height: 38px;
    border: none;
    background: transparent;
    color: var(--wa-text-muted);
    cursor: pointer;
    border-radius: 50%;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s;
}

.back-btn:hover {
    background: var(--wa-panel-hover);
    color: var(--wa-text);
}

.ai-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--wa-accent), var(--wa-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.ai-info {
    flex: 1;
    min-width: 0;
}

.ai-info h1 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--wa-text);
    display: flex;
    align-items: center;
    gap: 6px;
}

.ai-status {
    font-size: 12px;
    color: var(--wa-green);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: var(--wa-green);
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

.header-actions {
    display: flex;
    gap: 8px;
}

.icon-btn {
    width: 38px;
    height: 38px;
    border: none;
    background: transparent;
    color: var(--wa-text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
    font-size: 16px;
}

.icon-btn:hover {
    background: var(--wa-panel-hover);
    color: var(--wa-text);
}

/* Chat Messages */
.chat-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
    max-width: 100%;
}

.chat-container::-webkit-scrollbar {
    width: 6px;
}

.chat-container::-webkit-scrollbar-track {
    background: transparent;
}

.chat-container::-webkit-scrollbar-thumb {
    background: var(--wa-border);
    border-radius: 3px;
}

.message {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    animation: messageSlideIn 0.3s ease;
}

@keyframes messageSlideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message.user {
    flex-direction: row-reverse;
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.message.ai .message-avatar {
    background: linear-gradient(135deg, var(--wa-accent), var(--wa-blue));
    color: white;
}

.message.user .message-avatar {
    background: var(--wa-panel-hover);
    color: var(--wa-text);
}

.message-bubble {
    max-width: 75%;
    padding: 10px 14px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.5;
    position: relative;
}

.message.ai .message-bubble {
    background: var(--wa-panel);
    color: var(--wa-text);
    border-bottom-left-radius: 4px;
    border: 1px solid var(--wa-border);
}

.message.user .message-bubble {
    background: var(--wa-accent);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-bubble strong {
    font-weight: 600;
}

.message-bubble p {
    margin: 0 0 8px 0;
}

.message-bubble p:last-child {
    margin: 0;
}

/* Help List - Simple vertical list */
.message-bubble .help-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 12px;
    margin-bottom: 8px;
}

.message-bubble .help-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: var(--wa-bg);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.message-bubble .help-item:hover {
    background: var(--wa-panel-hover);
    transform: translateX(4px);
}

.message-bubble .help-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: var(--wa-accent);
    color: white;
    border-radius: 50%;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}

.message-bubble .help-text {
    font-size: 13px;
    color: var(--wa-text);
    font-weight: 500;
}

.message-time {
    font-size: 10px;
    color: var(--wa-text-muted);
    margin-top: 4px;
    text-align: right;
}

.user-bubble .message-time {
    color: rgba(255, 255, 255, 0.7);
}

/* Welcome Card */
.welcome-card {
    text-align: center;
    padding: 40px 20px;
    margin: 20px auto;
    max-width: 500px;
}

.welcome-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, var(--wa-accent), var(--wa-blue));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: white;
}

.welcome-card h2 {
    margin: 0 0 8px;
    font-size: 24px;
    font-weight: 600;
    color: var(--wa-text);
}

.welcome-card p {
    margin: 0 0 24px;
    color: var(--wa-text-muted);
    font-size: 14px;
}

/* Input Area */
.input-area {
    padding: 12px 24px;
    background: var(--wa-panel);
    border-top: 1px solid var(--wa-border);
    width: 100%;
}

.input-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--wa-bg);
    border-radius: 24px;
    padding: 8px 16px;
}

.input-wrapper input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    padding: 8px;
    font-size: 14px;
    color: var(--wa-text);
    font-family: inherit;
}

.input-wrapper input::placeholder {
    color: var(--wa-text-muted);
}

.send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--wa-accent);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: all 0.2s;
}

.send-btn:hover:not(:disabled) {
    opacity: 0.9;
    transform: scale(1.05);
}

.send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.stop-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.1);
    border: 2px solid var(--wa-red);
    color: var(--wa-red);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s;
}

.stop-btn:hover {
    background: var(--wa-red);
    color: white;
}

/* Typing Indicator */
.typing {
    display: flex;
    gap: 4px;
    padding: 12px 16px;
}

.typing span {
    width: 8px;
    height: 8px;
    background: var(--wa-text-muted);
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing span:nth-child(1) { animation-delay: -0.32s; }
.typing span:nth-child(2) { animation-delay: -0.16s; }
.typing span:nth-child(3) { animation-delay: 0s; }

@keyframes typing {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}

/* Mobile */
@media (max-width: 900px) {
    .chat-container {
        padding: 16px 12px;
    }

    .message {
        gap: 6px;
    }

    .message-avatar {
        width: 32px;
        height: 32px;
        font-size: 14px;
        flex-shrink: 0;
    }

    .message-bubble {
        max-width: 85%;
        padding: 10px 12px;
        font-size: 13px;
        border-radius: 14px;
    }

    .help-list {
        gap: 4px;
    }

    .help-item {
        padding: 6px 10px;
    }

    .help-number {
        width: 22px;
        height: 22px;
        font-size: 11px;
    }

    .help-text {
        font-size: 12px;
    }

    .message-time {
        font-size: 9px;
    }
}

@media (max-width: 600px) {
    .ai-header {
        padding: 10px 12px;
    }

    .ai-avatar {
        width: 38px;
        height: 38px;
        font-size: 18px;
    }

    .ai-info h1 {
        font-size: 14px;
    }

    .ai-status {
        font-size: 11px;
    }

    .chat-container {
        padding: 12px 8px 100px;
    }

    .message {
        gap: 6px;
    }

    .message-avatar {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }

    .message-bubble {
        max-width: 88%;
        padding: 8px 10px;
        font-size: 12px;
        border-radius: 12px;
    }

    .help-list {
        gap: 3px;
    }

    .help-item {
        padding: 5px 8px;
        gap: 8px;
    }

    .help-item:hover {
        transform: translateX(2px);
    }

    .help-number {
        width: 20px;
        height: 20px;
        font-size: 10px;
    }

    .help-text {
        font-size: 11px;
    }

    .message-time {
        font-size: 9px;
    }

    .input-area {
        padding: 10px 12px;
    }

    .input-wrapper {
        padding: 6px 10px;
    }

    .input-wrapper input {
        font-size: 13px;
        padding: 6px;
    }

    .send-btn, .stop-btn {
        width: 34px;
        height: 34px;
        font-size: 14px;
    }
}
</style>

<div class="ai-page">
    <header class="ai-header">
        <a href="{{ route('home') }}" class="back-btn" title="Back to Home">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="ai-avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="ai-info">
            <h1>
                Nexus AI
                <i class="fas fa-check-circle" style="color: var(--wa-blue); font-size: 14px;"></i>
            </h1>
            <div class="ai-status">
                <span class="status-dot"></span>
                Always Online
            </div>
        </div>
        <div class="header-actions">
            <button class="icon-btn" onclick="clearChat()" title="Clear chat">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </header>

    <div class="chat-container" id="chatContainer">
        <!-- Welcome Message as Regular Chat -->
        <div class="message ai" id="welcomeMessage">
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-bubble">
                <p><strong>🤖 Hi! I'm Nexus AI Assistant</strong></p>
                <p>Type a number (1-9) to get help:</p>
                
                <div class="help-list">
                    <div class="help-item" onclick="sendQuickMessage('1')">
                        <span class="help-number">1</span>
                        <span class="help-text">Help & Menu</span>
                    </div>
                    <div class="help-item" onclick="sendQuickMessage('2')">
                        <span class="help-number">2</span>
                        <span class="help-text">Writing Posts</span>
                    </div>
                    <div class="help-item" onclick="sendQuickMessage('3')">
                        <span class="help-number">3</span>
                        <span class="help-text">Follow Tips</span>
                    </div>
                    <div class="help-item" onclick="sendQuickMessage('4')">
                        <span class="help-number">4</span>
                        <span class="help-text">Trending Topics</span>
                    </div>
                    <div class="help-item" onclick="sendQuickMessage('5')">
                        <span class="help-number">5</span>
                        <span class="help-text">Privacy Guide</span>
                    </div>
                    <div class="help-item" onclick="sendQuickMessage('6')">
                        <span class="help-number">6</span>
                        <span class="help-text">Engagement Tips</span>
                    </div>
                    <div class="help-item" onclick="sendQuickMessage('7')">
                        <span class="help-number">7</span>
                        <span class="help-text">Stories Guide</span>
                    </div>
                    <div class="help-item" onclick="sendQuickMessage('8')">
                        <span class="help-number">8</span>
                        <span class="help-text">Profile Setup</span>
                    </div>
                    <div class="help-item" onclick="sendQuickMessage('9')">
                        <span class="help-number">9</span>
                        <span class="help-text">Search & Discover</span>
                    </div>
                </div>
                
                <div class="message-time">{{ now()->format('H:i') }}</div>
            </div>
        </div>
    </div>

    <div class="input-area">
        <div class="input-wrapper">
            <input type="text" id="chatInput" placeholder="Type a number (1-9)..." maxlength="1" autocomplete="off" inputmode="numeric" pattern="[1-9]">
            <button type="button" id="stopBtn" class="stop-btn" style="display:none;" title="Stop">
                <i class="fas fa-stop"></i>
            </button>
            <button type="button" id="sendBtn" class="send-btn" disabled title="Send">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<script>
const chatContainer = document.getElementById('chatContainer');
const chatInput = document.getElementById('chatInput');
const sendBtn = document.getElementById('sendBtn');
const stopBtn = document.getElementById('stopBtn');
const welcomeMessage = document.getElementById('welcomeMessage');

let isTyping = false;
let typingTimeout;
let welcomeHidden = false;

chatInput.addEventListener('input', function() {
    // Only allow numbers 1-9
    this.value = this.value.replace(/[^1-9]/g, '');
    sendBtn.disabled = !this.value.trim();
});

chatInput.addEventListener('keypress', function(e) {
    // Only allow numbers 1-9
    if (!/^[1-9]$/.test(e.key)) {
        e.preventDefault();
    }
    // Send on Enter
    if (e.key === 'Enter') {
        e.preventDefault();
        sendMessage();
    }
});

sendBtn.addEventListener('click', sendMessage);
stopBtn.addEventListener('click', stopGenerating);

window.sendQuickMessage = function(number) {
    chatInput.value = number;
    sendBtn.disabled = false;
    sendMessage();
};

function sendMessage() {
    const message = chatInput.value.trim();
    if (!message) return;

    // Hide welcome message on first user message
    if (!welcomeHidden && welcomeMessage) {
        welcomeMessage.style.display = 'none';
        welcomeHidden = true;
    }

    addMessage(message, 'user');
    chatInput.value = '';
    sendBtn.disabled = true;

    const typingIndicator = showTyping();

    fetch('/ai/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ message: message })
    })
    .then(res => res.json())
    .then(data => {
        hideTyping(typingIndicator);
        if (data.success && data.response) {
            addMessageWithTyping(data.response, 'ai');
        } else {
            addMessage('Sorry, I encountered an error.', 'ai');
        }
    })
    .catch(err => {
        console.error('AI Error:', err);
        hideTyping(typingIndicator);
        addMessage('Sorry, I\'m having trouble connecting.', 'ai');
    });
}

function addMessage(text, type) {
    const div = document.createElement('div');
    div.className = `message ${type}`;

    const time = new Date().toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });

    const avatar = type === 'ai'
        ? '<i class="fas fa-robot"></i>'
        : '<i class="fas fa-user"></i>';

    const content = type === 'ai' ? formatResponse(text) : escapeHtml(text);

    div.innerHTML = `
        <div class="message-avatar">${avatar}</div>
        <div class="message-bubble ${type === 'user' ? 'user-bubble' : ''}">
            <p>${content}</p>
            <div class="message-time">${time}</div>
        </div>
    `;

    chatContainer.appendChild(div);
    scrollToBottom();
}

function addMessageWithTyping(fullText, type) {
    const div = document.createElement('div');
    div.className = `message ${type}`;

    const time = new Date().toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });

    div.innerHTML = `
        <div class="message-avatar"><i class="fas fa-robot"></i></div>
        <div class="message-bubble">
            <p class="typing-text"></p>
            <div class="message-time">${time}</div>
        </div>
    `;

    chatContainer.appendChild(div);
    scrollToBottom();

    // Start typing animation
    typeText(div, fullText);
}

function typeText(messageDiv, fullText) {
    const textElement = messageDiv.querySelector('.typing-text');
    let currentIndex = 0;
    isTyping = true;

    // Show stop button
    stopBtn.style.display = 'flex';
    
    stopBtn.onclick = function() {
        isTyping = false;
        clearTimeout(typingTimeout);
        stopBtn.style.display = 'none';
        textElement.innerHTML = formatResponse(fullText);
        setTimeout(scrollToBottom, 50);
    };

    function typeCharacter() {
        if (currentIndex < fullText.length && isTyping) {
            const char = fullText[currentIndex];
            textElement.innerHTML = formatResponse(fullText.substring(0, currentIndex + 1));
            currentIndex++;
            scrollToBottom();

            // Variable typing speed
            const typingSpeed = getTypingSpeed(char);
            typingTimeout = setTimeout(typeCharacter, typingSpeed);
        } else {
            stopBtn.style.display = 'none';
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

function stopGenerating() {
    isTyping = false;
    clearTimeout(typingTimeout);
    
}

function showTyping() {
    const div = document.createElement('div');
    div.className = 'message ai';
    div.id = 'typingIndicator';
    div.innerHTML = `
        <div class="message-avatar"><i class="fas fa-robot"></i></div>
        <div class="message-bubble">
            <div class="typing">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    `;
    chatContainer.appendChild(div);
    scrollToBottom();
    return div;
}

function hideTyping(indicator) {
    if (indicator && indicator.parentNode) {
        indicator.remove();
    }
}

function formatResponse(text) {
    return text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\n/g, '<br>');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function scrollToBottom() {
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function clearChat() {
    if (!confirm('Clear all messages?')) return;
    
    // Remove all messages except welcome
    const messages = chatContainer.querySelectorAll('.message:not(#welcomeMessage)');
    messages.forEach(msg => msg.remove());
    
    // Show welcome message
    if (welcomeMessage) {
        welcomeMessage.style.display = 'flex';
        welcomeHidden = false;
    }
    
    // Reset input
    chatInput.value = '';
    sendBtn.disabled = true;
}
</script>
@endsection
