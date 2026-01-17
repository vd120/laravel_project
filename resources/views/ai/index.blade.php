@extends('layouts.app')

@section('title', 'AI Assistant - Laravel Social')

@section('content')
<div class="ai-assistant">
    <button class="close-button" onclick="closeAIAssistant()">
        <i class="fas fa-times"></i>
    </button>


    <!-- Chat Interface -->
    <div class="ai-chat">
        <div class="chat-messages" id="chatMessages">
            <div class="welcome-message">
                <div class="ai-avatar-small">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-bubble ai-bubble">
                    <p>👋 Hi! I'm your AI assistant for Laravel Social. Choose an option by typing the number or ask me anything!</p>
                    <div class="menu-options">
                        <div class="menu-item">1️- Help & Menu</div>
                        <div class="menu-item">2️- Writing Posts</div>
                        <div class="menu-item">3️- Find Friends</div>
                        <div class="menu-item">4️- Stories Guide</div>
                        <div class="menu-item">5️- Privacy Help</div>
                        <div class="menu-item">6️- Profile Tips</div>
                        <div class="menu-item">7️- Messaging</div>
                        <div class="menu-item">8️- Account Settings</div>
                        <div class="menu-item">9️- Getting Started</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chat-input">
            <div class="input-container">
                <input
                    type="text"
                    id="chatInput"
                    placeholder="Ask me anything..."
                    maxlength="200"
                >
            </div>
            <button type="button" id="sendButton" onclick="sendMessage()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<style>
.ai-assistant {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg,
        #0a0a0a 0%,
        #1a0a1a 25%,
        #0a0a2a 50%,
        #1a1a1a 75%,
        #0a0a0a 100%
    );
    background-attachment: fixed;
    overflow: hidden;
    z-index: 1000;
}

.close-button {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.3s ease;
    z-index: 1001;
}

.close-button:hover {
    background: rgba(244, 33, 46, 0.8);
    transform: scale(1.1);
    box-shadow: 0 0 20px rgba(244, 33, 46, 0.5);
}

.ai-assistant::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background:
        radial-gradient(circle at 20% 30%, rgba(139, 92, 246, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(255, 107, 107, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 60% 10%, rgba(29, 161, 242, 0.15) 0%, transparent 50%);
    animation: backgroundShift 60s ease-in-out infinite;
    z-index: -1;
}

@keyframes backgroundShift {
    0%, 100% {
        transform: scale(1) rotate(0deg);
        opacity: 0.3;
    }
    50% {
        transform: scale(1.02) rotate(0.2deg);
        opacity: 0.4;
    }
}

.ai-header {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    z-index: 10;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 16px 24px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
}

.ai-header-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.ai-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--twitter-blue) 0%, #7C3AED 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    box-shadow: 0 0 20px rgba(139, 92, 246, 0.5);
    animation: avatarGlow 3s ease-in-out infinite alternate;
}

@keyframes avatarGlow {
    0% { box-shadow: 0 0 20px rgba(139, 92, 246, 0.5); }
    100% { box-shadow: 0 0 30px rgba(139, 92, 246, 0.8), 0 0 60px rgba(139, 92, 246, 0.3); }
}

.ai-info h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    background: linear-gradient(135deg, var(--twitter-blue) 0%, #7C3AED 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
}

.ai-info p {
    margin: 0;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.8);
    opacity: 0.9;
}

.ai-chat {
    position: absolute;
    top: 120px;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-messages {
    position: absolute;
    top: 120px;
    left: 0;
    right: 0;
    bottom: 120px;
    padding: 20px;
    overflow-y: auto;
    background: transparent;
}

.welcome-message {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.ai-avatar-small {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--twitter-blue) 0%, #7C3AED 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
    flex-shrink: 0;
    margin-top: 2px;
}

.message {
    margin-bottom: 20px;
    display: flex;
    align-items: flex-end;
    gap: 10px;
    position: relative;
    animation: messageFloatIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    min-height: 40px;
    width: 100%;
    max-width: 70%;
}

.message.user {
    flex-direction: row-reverse;
    justify-content: flex-end;
    margin-left: auto;
    margin-right: 0;
    width: fit-content;
    max-width: 60%;
}

.message.ai {
    justify-content: flex-start;
    margin-right: auto;
    margin-left: 0;
    width: fit-content;
    max-width: 60%;
    transform: translateX(-20px);
}

.message.ai .ai-avatar-small {
    order: -1;
    align-self: flex-start;
    margin-top: 4px;
    margin-bottom: 8px;
    flex-shrink: 0;
    z-index: 2;
}

.message.user .ai-avatar-small {
    display: none;
}

.message:nth-child(even).ai {
    margin-left: 8%;
}

.message:nth-child(odd).ai {
    margin-left: 2%;
}

.message:nth-child(3n).ai {
    margin-left: 12%;
}

.message:nth-child(4n).user {
    margin-right: 8%;
}

.message:nth-child(5n).user {
    margin-right: 12%;
}

@keyframes messageFloatIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.message-bubble {
    padding: 14px 18px;
    border-radius: 20px;
    font-size: 15px;
    line-height: 1.5;
    word-wrap: break-word;
    position: relative;
    z-index: 1;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow:
        0 4px 16px rgba(0, 0, 0, 0.15),
        0 0 32px rgba(139, 92, 246, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    max-width: 100%;
}

.message-bubble:hover {
    transform: translateY(-1px);
    box-shadow:
        0 6px 24px rgba(0, 0, 0, 0.2),
        0 0 48px rgba(139, 92, 246, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

.ai-bubble {
    background: linear-gradient(135deg,
        rgba(255, 255, 255, 0.15) 0%,
        rgba(255, 255, 255, 0.08) 50%,
        rgba(139, 92, 246, 0.1) 100%);
    color: var(--twitter-dark);
    border-bottom-left-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.user-bubble {
    background: linear-gradient(135deg,
        var(--twitter-blue) 0%,
        #7C3AED 50%,
        var(--twitter-blue) 100%);
    color: white;
    border-bottom-right-radius: 6px;
    border: 1px solid rgba(255, 107, 107, 0.3);
    box-shadow:
        0 4px 16px rgba(139, 92, 246, 0.3),
        0 0 32px rgba(139, 92, 246, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.user-bubble:hover {
    box-shadow:
        0 6px 24px rgba(139, 92, 246, 0.4),
        0 0 48px rgba(139, 92, 246, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.25);
}

.typing-text {
    margin: 0;
    line-height: 1.4;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.typing-cursor {
    display: inline-block;
    margin-left: 2px;
    font-weight: bold;
    color: var(--twitter-blue);
    animation: blink 1s infinite;
    font-size: 14px;
}

@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

.chat-input {
    padding: 16px 20px;
    background: rgba(255, 255, 255, 0.05);
    border-top: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.input-container {
    flex: 1;
    display: flex;
    align-items: center;
    background: transparent;
    border: none;
    border-radius: 0;
    padding: 0;
    margin: 0;
    box-shadow: none;
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
}

.input-container:focus-within {
    background: transparent;
    border: none;
    box-shadow: none;
    transform: none;
}

.input-container input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: var(--twitter-dark);
    font-size: 16px;
    padding: 4px 0;
    font-family: inherit;
}

.input-container input::placeholder {
    color: var(--twitter-gray);
}

.send-button {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg,
        var(--twitter-blue) 0%,
        #7C3AED 20%,
        #FF6B6B 50%,
        #FF1744 80%,
        var(--twitter-blue) 100%);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow:
        0 8px 32px rgba(139, 92, 246, 0.6),
        0 0 64px rgba(139, 92, 246, 0.4),
        0 0 96px rgba(255, 107, 107, 0.3),
        0 0 128px rgba(255, 23, 68, 0.2),
        inset 0 3px 0 rgba(255, 255, 255, 0.25),
        inset 0 -3px 0 rgba(0, 0, 0, 0.1);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    margin-left: 16px;
    transform: scale(1);
    filter: brightness(1.1) saturate(1.2);
}

.send-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg,
        transparent 0%,
        rgba(255, 255, 255, 0.2) 30%,
        rgba(255, 255, 255, 0.4) 50%,
        rgba(255, 255, 255, 0.2) 70%,
        transparent 100%
    );
    transition: left 0.8s ease;
}

.send-button:hover::before {
    left: 100%;
}

.send-button:hover {
    transform: translateY(-4px) scale(1.1) rotate(5deg);
    box-shadow:
        0 12px 40px rgba(139, 92, 246, 0.8),
        0 0 80px rgba(139, 92, 246, 0.4),
        0 0 120px rgba(255, 107, 107, 0.3),
        inset 0 2px 0 rgba(255, 255, 255, 0.2);
    animation-play-state: paused;
}

.send-button:active {
    transform: translateY(-2px) scale(1.05) rotate(2deg);
    box-shadow:
        0 6px 20px rgba(139, 92, 246, 0.9),
        0 0 40px rgba(139, 92, 246, 0.5),
        inset 0 2px 6px rgba(0, 0, 0, 0.1);
}

.send-button i {
    font-size: 20px;
    transition: all 0.4s ease;
    filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.3));
    z-index: 1;
    position: relative;
}

.send-button:hover i {
    transform: scale(1.2) rotate(10deg);
    filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.5));
}

@keyframes buttonPulse {
    0%, 100% {
        box-shadow:
            0 4px 20px rgba(139, 92, 246, 0.5),
            0 0 40px rgba(139, 92, 246, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }
    50% {
        box-shadow:
            0 4px 25px rgba(139, 92, 246, 0.7),
            0 0 60px rgba(139, 92, 246, 0.3),
            0 0 20px rgba(255, 107, 107, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }
}

.send-button i {
    font-size: 20px;
    transition: all 0.3s ease;
}

/* Removed typing indicator and input hints styles */

.chat-messages::-webkit-scrollbar {
    width: 4px;
}

.chat-messages::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: var(--twitter-blue);
    border-radius: 2px;
}

/* Laptop and Desktop Optimizations (All laptop sizes) */
@media (min-width: 768px) {
    .message.user {
        max-width: 70%;
        margin-left: auto;
        margin-right: 5%;
    }

    .message.ai {
        max-width: 70%;
        margin-right: auto;
        margin-left: 5%;
    }

    .message:nth-child(even).ai {
        margin-left: 10%;
    }

    .message:nth-child(odd).ai {
        margin-left: 3%;
    }

    .message:nth-child(3n).ai {
        margin-left: 15%;
    }

    .message:nth-child(4n).user {
        margin-right: 10%;
    }

    .message:nth-child(5n).user {
        margin-right: 15%;
    }

    .message-bubble {
        font-size: 15px;
        padding: 14px 18px;
        max-width: 600px;
    }

    .ai-avatar-small {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }

    .chat-messages {
        padding: 30px 40px;
    }

    /* Enhanced input bar for ALL laptop views - BOTTOM */
    .chat-input {
        padding: 20px 40px;
        background: rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        max-width: 900px;
        margin: 0 auto;
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100vw !important;
        z-index: 1000 !important;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
    }

    /* Adjust chat messages area for fixed input bar */
    .chat-messages {
        bottom: 120px !important;
        padding-bottom: 140px !important; /* Extra space for fixed input */
    }

    .ai-chat {
        bottom: 0 !important;
    }
}

/* Tablet Optimizations */
@media (min-width: 769px) and (max-width: 1023px) {
    .message.user {
        max-width: 75%;
        margin-left: auto;
        margin-right: 3%;
    }

    .message.ai {
        max-width: 75%;
        margin-right: auto;
        margin-left: 3%;
    }

    .message:nth-child(even).ai {
        margin-left: 8%;
    }

    .message:nth-child(odd).ai {
        margin-left: 2%;
    }

    .message:nth-child(3n).ai {
        margin-left: 12%;
    }

    .message:nth-child(4n).user {
        margin-right: 8%;
    }

    .message:nth-child(5n).user {
        margin-right: 12%;
    }

    .message-bubble {
        font-size: 14px;
        padding: 12px 16px;
    }

    .ai-avatar-small {
        width: 38px;
        height: 38px;
        font-size: 17px;
    }

    .chat-messages {
        padding: 25px 30px;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .ai-assistant {
        padding: 8px;
        min-height: calc(100vh - 80px);
    }

    .ai-header {
        padding: 16px;
        margin-bottom: 16px;
        border-radius: 12px;
    }

    .ai-avatar {
        width: 48px;
        height: 48px;
        font-size: 20px;
    }

    .ai-info h1 {
        font-size: 20px;
        margin-bottom: 6px;
    }

    .ai-info p {
        font-size: 14px;
    }

    .ai-chat {
        height: calc(100vh - 200px);
        border-radius: 12px;
        margin-bottom: 8px;
    }

    .chat-messages {
        padding: 12px;
        height: calc(100% - 80px);
        max-height: calc(100vh - 280px);
    }

    .message {
        margin-bottom: 12px;
    }

    .message-bubble {
        max-width: 85%;
        font-size: 14px;
        padding: 10px 14px;
    }

    .ai-avatar-small {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }

    .chat-input {
        padding: 10px 12px;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100vw;
        background: var(--card-bg);
        border-top: 1px solid var(--border-color);
        border-radius: 16px 16px 0 0;
        z-index: 1000;
        margin: 0;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform: translateZ(0);
        will-change: transform;
        padding-bottom: env(safe-area-inset-bottom);
        padding-bottom: calc(10px + env(safe-area-inset-bottom));
    }

    .input-container input:focus,
    .input-container:focus-within {
        transform: translateY(-2px);
    }

    @media (max-width: 768px) and (orientation: portrait) {
        .chat-input {
            bottom: max(0px, calc(100vh - 100vh));
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
        }

        .input-container input:focus,
        .input-container:focus-within {
            transform: translateY(-5px);
            margin-bottom: 10px;
        }
    }

    @media (max-width: 768px) and (orientation: landscape) {
        .chat-input {
            padding: 8px 12px;
        }
    }

@media (max-width: 360px) {
    .ai-assistant {
        padding: 2px;
    }

    .ai-header {
        padding: 8px;
        margin-bottom: 8px;
    }

    .ai-info h1 {
        font-size: 16px;
    }

    .ai-info p {
        font-size: 12px;
    }

    .ai-chat {
        height: calc(100vh - 160px);
    }

    .chat-messages {
        padding: 6px;
        max-height: calc(100vh - 240px);
        padding-bottom: 60px;
    }

    .message-bubble {
        font-size: 12px;
        padding: 6px 10px;
    }

    .chat-input {
        padding: 6px 8px;
    }
}

@media (max-height: 500px) and (orientation: landscape) {
    .ai-chat {
        height: calc(100vh - 120px);
    }

    .chat-messages {
        max-height: calc(100vh - 200px);
        padding-bottom: 40px;
    }

    .chat-input {
        padding: 6px 12px;
    }

    .input-container input {
        font-size: 14px;
    }
}

@media (hover: none) and (pointer: coarse) {
    .input-container button {
        min-height: 44px;
    }

    .input-container input {
        font-size: 16px;
    }
}
</style>

<script>
// Chat Interface
document.addEventListener('DOMContentLoaded', function() {
    // Set up input event listeners
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.addEventListener('keypress', handleKeyPress);
        chatInput.addEventListener('input', handleNumericInput);
        chatInput.addEventListener('input', updateCharCount);
    }

    // Handle mobile viewport height issues
    function setVH() {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }

    setVH();
    window.addEventListener('resize', setVH);
    window.addEventListener('orientationchange', setVH);
});

// Handle menu selection by number
function handleMenuSelection(number) {
    const menuOptions = {
        '1': 'help',
        '2': 'writing',
        '3': 'friends',
        '4': 'stories',
        '5': 'privacy',
        '6': 'profile',
        '7': 'messaging',
        '8': 'settings',
        '9': 'getting-started'
    };

    const option = menuOptions[number];
    if (option) {
    // Add user selection to chat
    addMessage(number, 'user');

    // Get response and always include menu
    setTimeout(() => {
        const response = getMenuResponse(option) + getMenuText();
        addMessage(response, 'ai');
    }, 800);
    }
}

function getQuickResponse(question) {
    // Simple keyword matching for responses
    const responses = {
        'how do i create a post': `📝 **Creating Posts - Quick Guide**

1. **Click the "+" button** in the top navigation
2. **Write your text** (max 280 characters)
3. **Add media** (photos/videos) if desired
4. **Add hashtags** like #LaravelSocial
5. **Click "Post"** to share!

💡 **Pro Tip**: Use @mentions to tag friends and add relevant hashtags to reach more people!`,

        'how do i find friends': `🔍 **Finding Friends & People**

🌟 **Ways to Discover People:**
• **Explore page** - Browse trending posts
• **Search** - Look for usernames or keywords
• **Who to Follow** - Check personalized suggestions
• **Hashtags** - Find communities with shared interests

📱 **Quick Actions:**
• Tap search icon in navigation
• Use the explore tab
• Check "Suggested for you" section

👥 **Connection Tips:**
• Follow people you find interesting
• Engage with their posts first
• Send personalized messages`,

        'how do stories work': `📱 **Stories - Quick Start Guide**

🎬 **How to Create Stories:**
1. **Click your avatar** in the top navigation
2. **Tap "Create Story"** or take a photo/video
3. **Add text, stickers, or effects**
4. **Share** - stories last 24 hours

🎨 **Story Features:**
• **Text overlays** - Add captions
• **Stickers** - Emojis and fun elements
• **Polls & Questions** - Engage viewers
• **Highlights** - Save stories permanently

⏰ **Story Tips:**
• Post daily for best engagement
• Use stories for behind-the-scenes content
• Save important stories as highlights`,

        'how do i change my privacy settings': `🔒 **Privacy Settings - Quick Setup**

🛡️ **Essential Privacy Steps:**
1. **Go to your profile** → click "Edit Profile"
2. **Account Privacy** → toggle "Private Account"
3. **Story Settings** → control who sees your stories
4. **Blocked Users** → manage blocked accounts

🔐 **Security Tips:**
• Use a strong, unique password
• Enable two-factor authentication
• Review app permissions regularly
• Be cautious with personal information

⚙️ **Advanced Settings:**
• Control who can message you
• Manage tag approvals
• Set up close friends list`,

        'how do i improve my profile': `👤 **Profile Optimization - Quick Wins**

🎯 **Profile Essentials:**
• **Profile Photo**: Clear, friendly face photo
• **Bio**: Tell people who you are (80-160 characters)
• **Cover Image**: Add visual appeal
• **Link**: Include your website or important link

📊 **Optimization Tips:**
• Use keywords in your bio for discoverability
• Add emojis for personality
• Keep bio updated and relevant
• Choose a consistent theme for posts

🚀 **Next Steps:**
• Complete all profile sections
• Add profile highlights/stories
• Engage consistently with your audience`,

        'how does messaging work': `💬 **Messaging System - Quick Guide**

📨 **How to Message:**
1. **Go to Messages** in the navigation
2. **Click "New Message"** or search for a user
3. **Type your message** and send
4. **Use emoji reactions** on messages

💡 **Messaging Features:**
• **Real-time chat** with online indicators
• **Photo sharing** in conversations
• **Message reactions** with emojis
• **Group chats** (up to 50 people)

🔒 **Privacy Controls:**
• Control who can message you
• Block unwanted conversations
• Report inappropriate messages`
    };

    // Find matching response (case insensitive partial match)
    const questionLower = question.toLowerCase();
    for (const [key, response] of Object.entries(responses)) {
        if (questionLower.includes(key)) {
            return response;
        }
    }

    // Default response if no match found
    return `🤔 I understand you're asking about "${question}". 

Here are some popular topics I can help with:
• How to create engaging posts
• Finding and connecting with friends  
• Using Stories effectively
• Privacy and security settings
• Optimizing your profile

Try clicking one of the buttons above or ask me something specific!`;
}

function getMenuOptionText(option) {
    const optionTexts = {
        help: "I need help with the platform",
        writing: "Help me write better posts",
        discover: "Help me discover new people to follow",
        trends: "What are the current trending topics?",
        engage: "How can I increase my engagement?",
        analytics: "Show me my analytics and growth",
        profile: "Help me optimize my profile",
        privacy: "Privacy and security tips",
        stories: "How do I use stories effectively?",
        media: "Tips for posting photos and videos",
        chat: "How does the messaging system work?",
        settings: "Help with account settings"
    };
    return optionTexts[option] || option;
}

function getAIResponses() {
    return {
        help: `🤖 **Welcome to Laravel Social!** Here's how I can help you:

📝 **Getting Started**
• Complete your profile with a photo and bio
• Follow friends and interesting people
• Explore trending posts and topics

✍️ **Creating Content**
• Write engaging posts (max 280 characters)
• Add photos, videos, or links
• Use @mentions to tag people
• Add hashtags for discoverability

👥 **Connecting**
• Follow users you find interesting
• Like and comment on posts
• Send private messages
• Create and share stories

📊 **Analytics**
• Check your profile stats
• See post engagement metrics
• Track follower growth

🔒 **Privacy & Security**
• Control who sees your posts
• Block unwanted users
• Report inappropriate content

💡 **Pro Tips**
• Post consistently for better engagement
• Use relevant hashtags
• Engage with comments on your posts
• Share valuable content

What would you like to learn more about?`,

        writing: `📝 **Writing Better Posts - Pro Tips**

🎯 **Content Strategy**
• **Know your audience**: Write for your followers' interests
• **Value first**: Share helpful, entertaining, or insightful content
• **Consistency**: Post regularly to stay visible
• **Timing**: Post when your audience is most active

✍️ **Writing Techniques**
• **Hook immediately**: Start with a question, fact, or story
• **Keep it concise**: 280 characters maximum - be punchy!
• **Use emojis**: 😊 Add personality and visual interest
• **Ask questions**: Encourage engagement and comments

📸 **Visual Content**
• **High-quality images**: Clear, well-lit photos
• **Videos**: Short, engaging clips (15-60 seconds)
• **Stories**: Behind-the-scenes, polls, Q&A sessions

🔍 **Optimization**
• **Hashtags**: Use 2-3 relevant hashtags per post
• **Keywords**: Include searchable terms naturally
• **Mentions**: @tag people and brands when relevant

📊 **Best Practices**
• **Engage first**: Like and comment before posting
• **Cross-promote**: Share content across platforms
• **Analyze**: Check what posts perform best
• **Experiment**: Try different content types

💡 **Example Post Structure:**
"🚀 Just launched my new project! So excited to share this journey with you all. The feedback has been amazing already. What's one project you've been working on? #Entrepreneur #ProjectLaunch #Tech"

Try writing a post now and I can help you improve it!`,

        discover: `🔍 **Discover New People & Content**

🌟 **Finding People to Follow**
• **Explore Page**: Browse trending posts and discover new users
• **Search**: Use keywords, usernames, or hashtags
• **Who to Follow**: Check suggestions based on your interests
• **Mutual Connections**: See who your friends follow

🔍 **Search Strategies**
• **Keywords**: Search for topics you're interested in
• **Hashtags**: Find communities around specific topics
• **Usernames**: Look for specific people you know
• **Locations**: Discover local communities

📈 **Growing Your Network**
• **Follow back**: Engage with people who follow you
• **Quality over quantity**: Better to have engaged followers
• **Niche communities**: Join groups with shared interests
• **Collaborate**: Partner with complementary creators

💡 **Discovery Tips**
• **Trending hashtags**: Explore what's popular right now
• **Related users**: Check who similar people follow
• **Saved posts**: Create collections of inspiring content
• **Notifications**: Get alerts when people you follow engage

🔧 **Advanced Features**
• **Lists**: Organize people into custom groups
• **Muted words**: Filter out unwanted content
• **Blocked users**: Control who can interact with you

Start exploring! Who are you looking to connect with?`,

        trends: `📈 **Trending Topics & Viral Content**

🔥 **Understanding Trends**
• **Real-time data**: See what's popular right now
• **Regional trends**: Location-based trending topics
• **Hashtag challenges**: Community-driven movements
• **Breaking news**: Current events and discussions

📊 **Trending Categories**
• **Entertainment**: Movies, music, celebrities
• **Sports**: Games, athletes, championships
• **Technology**: New gadgets, apps, innovations
• **Politics**: Current events and discussions
• **Lifestyle**: Fashion, food, travel trends

🎯 **How to Use Trends**
• **Timing**: Post when trends are peaking
• **Authenticity**: Only join trends that fit your brand
• **Originality**: Add your unique perspective
• **Hashtags**: Use trending hashtags strategically

💡 **Trending Strategies**
• **Early adoption**: Jump on trends before they peak
• **Local trends**: Participate in location-specific trends
• **Create trends**: Start your own hashtag challenges
• **Cross-platform**: Share trending content everywhere

📱 **Trend Types**
• **Challenge trends**: Dance, cooking, fitness challenges
• **Discussion trends**: Important conversations
• **Meme trends**: Viral humor and reactions
• **Product trends**: New releases and launches

⚠️ **Trend Best Practices**
• **Research first**: Understand trend context
• **Quality content**: Don't sacrifice quality for trends
• **Engagement**: Trends work best with community interaction
• **Analytics**: Track which trends perform for you

What's trending that interests you right now?`,

        engage: `🚀 **Boost Your Engagement - Expert Strategies**

💬 **Comment Engagement**
• **Respond promptly**: Reply within 24 hours
• **Personal responses**: Use names and be specific
• **Ask questions**: Encourage further discussion
• **Thread conversations**: Keep discussions going

❤️ **Like Strategy**
• **Authentic likes**: Only like content you genuinely enjoy
• **Strategic timing**: Like posts from people you want to notice you
• **Comment + like**: Combine for maximum impact

🔄 **Interaction Techniques**
• **Follow then engage**: Build relationships before asking for follows
• **Share others' content**: Give credit and add value
• **Collaborate**: Partner with complementary accounts
• **User-generated content**: Feature your community

📊 **Engagement Analytics**
• **Track metrics**: Monitor likes, comments, shares
• **Best posting times**: Find when your audience is active
• **Content performance**: See what works best
• **Growth rate**: Monitor follower increases

🎯 **Advanced Tactics**
• **Stories engagement**: Polls, questions, Q&A sessions
• **Live sessions**: Real-time interaction opportunities
• **Contests & giveaways**: Boost participation
• **Behind-the-scenes**: Build personal connections

📈 **Growth Hacks**
• **Consistent posting**: 3-5 times per week minimum
• **Content variety**: Mix photos, videos, text, links
• **Hashtag strategy**: Use relevant, trending hashtags
• **Cross-promotion**: Share content on other platforms

💡 **Pro Tips**
• **Quality over quantity**: Better engagement than many followers
• **Authenticity matters**: Be genuine in all interactions
• **Value exchange**: Give before you ask
• **Community building**: Create a loyal following

What's your biggest engagement challenge?`,

        analytics: `📊 **Analytics & Growth Tracking**

📈 **Key Metrics to Monitor**
• **Follower growth**: Track daily/weekly increases
• **Engagement rate**: Likes + comments per post
• **Reach**: How many people see your content
• **Impressions**: Total content views

📱 **Post Performance**
• **Best performing content**: Photos vs videos vs text
• **Optimal posting times**: When your audience is active
• **Hashtag effectiveness**: Which tags drive most engagement
• **Content themes**: What topics resonate most

👥 **Audience Insights**
• **Demographics**: Age, location, interests
• **Top followers**: Most engaged users
• **New vs returning**: Fresh audience growth
• **Engagement patterns**: When people interact most

📊 **Growth Analytics**
• **Follower milestones**: Track progress toward goals
• **Engagement trends**: Improving or declining
• **Content reach**: Expanding or contracting
• **Competitor comparison**: How you stack up

🛠️ **Tools & Features**
• **Built-in analytics**: Check your profile stats
• **Post insights**: Individual post performance
• **Story analytics**: View completion rates
• **Export data**: Download your metrics

🎯 **Using Analytics for Growth**
• **Content optimization**: Double down on what works
• **Posting schedule**: Time posts for maximum reach
• **Audience targeting**: Create content for your core audience
• **Trend analysis**: Spot patterns and opportunities

💡 **Analytics Best Practices**
• **Regular monitoring**: Check stats weekly at minimum
• **Goal setting**: Define measurable growth targets
• **A/B testing**: Experiment with different approaches
• **Long-term tracking**: Monitor trends over months

📋 **Action Items**
1. Set specific growth goals
2. Track your posting consistency
3. Analyze top-performing content
4. Adjust strategy based on data
5. Celebrate milestones!

Ready to check your analytics?`,

        profile: `👤 **Profile Optimization Guide**

🎯 **Profile Photo**
• **High quality**: Clear, well-lit, professional image
• **Facial recognition**: Show your face for better connections
• **Branding**: Consistent with your content theme
• **Square format**: Works best across platforms

📝 **Bio Writing**
• **Clear value proposition**: What you offer followers
• **Keywords**: Include searchable terms
• **Call to action**: Encourage follows/engagement
• **Emojis**: Add personality and visual interest
• **Length**: 80-160 characters for optimal display

🔗 **Link Strategy**
• **Link in bio**: Direct to your most important content
• **Consistent branding**: Match your online presence
• **Call to action**: Make it clear what you want visitors to do
• **Track performance**: Use link tracking tools

📍 **Location & Contact**
• **Accurate location**: Help local people find you
• **Contact info**: Email/website if appropriate
• **Business hours**: For local businesses
• **Time zone**: Set for scheduling posts

🎨 **Visual Consistency**
• **Color scheme**: Consistent brand colors
• **Filters**: Use consistent photo editing
• **Themes**: Stick to 2-3 content categories
• **Grid layout**: Plan your profile's visual flow

📊 **Profile Analytics**
• **Profile visits**: How many people view your profile
• **Link clicks**: Track bio link performance
• **Audience demographics**: Understand who follows you
• **Content performance**: Which posts drive follows

💡 **Profile Optimization Checklist**
✅ Professional profile photo
✅ Compelling, keyword-rich bio
✅ Working link in bio
✅ Consistent visual theme
✅ Complete profile information
✅ Regular content posting
✅ Active engagement with followers

🚀 **Advanced Profile Tips**
• **Stories highlight**: Create pinned story collections
• **Custom emoji**: Add personality to your name
• **Location tags**: Help with local discoverability
• **Collaborations**: Partner with similar accounts

Your profile is your digital storefront - make it count!`,

        privacy: `🔒 **Privacy & Security Guide**

🛡️ **Account Security**
• **Strong password**: Use complex, unique passwords
• **Two-factor authentication**: Enable 2FA when available
• **Login alerts**: Monitor account access
• **App permissions**: Review connected applications

👀 **Privacy Settings**
• **Private account**: Control who sees your posts
• **Story privacy**: Choose who can view your stories
• **Message controls**: Manage who can message you
• **Tag approvals**: Review tags before they appear

🚫 **Blocking & Reporting**
• **Block users**: Prevent unwanted interactions
• **Report abuse**: Flag inappropriate content
• **Restrict accounts**: Limit problematic users
• **Muted words**: Filter unwanted content

🔐 **Data Protection**
• **Download data**: Export your information
• **Account deletion**: Permanently remove your account
• **Privacy policy**: Understand data usage
• **Third-party access**: Control app permissions

💡 **Privacy Best Practices**
• **Think before posting**: Consider long-term consequences
• **Location sharing**: Be cautious with location data
• **Personal information**: Avoid sharing sensitive details
• **Photo tagging**: Review photo tags carefully
• **Public vs private**: Use private accounts for personal use

🚨 **Safety Tips**
• **Recognize scams**: Be wary of suspicious accounts
• **Phishing awareness**: Don't click suspicious links
• **Meeting people**: Use caution when meeting online contacts
• **Cyberbullying**: Report and block abusive users
• **Mental health**: Take breaks from social media

📱 **Device Security**
• **App updates**: Keep apps and OS updated
• **Secure connections**: Use HTTPS and secure WiFi
• **Device locking**: Use PIN/password/biometric locks
• **Backup data**: Regularly backup important content

🔧 **Advanced Privacy Features**
• **Close friends**: Share with select followers only
• **Custom audiences**: Create specific follower groups
• **Time limits**: Set screen time limits
• **Notification controls**: Manage what notifications you receive

Remember: Your privacy is in your hands!`,

        stories: `📱 **Stories - Complete Usage Guide**

🎬 **Creating Stories**
• **Photo stories**: Single images with text overlays
• **Video stories**: 15-second clips for dynamic content
• **Multi-photo**: Combine multiple images in one story
• **Boomerang**: Short looping videos

🎨 **Story Features**
• **Text overlays**: Add text with various fonts and colors
• **Stickers**: Emojis, GIFs, location tags, mentions
• **Drawing tools**: Freehand drawing with colors
• **Music**: Add trending audio to videos
• **Polls**: Ask questions and get instant feedback
• **Questions**: Let followers ask you questions
• **Quizzes**: Create interactive quizzes

📊 **Story Analytics**
• **View counts**: See who viewed your stories
• **Completion rate**: Track engagement percentage
• **Reply insights**: See what questions you get
• **Poll results**: Analyze audience preferences

🎯 **Story Strategies**
• **Behind-the-scenes**: Show your daily life
• **Teasers**: Build excitement for upcoming content
• **Polls & questions**: Increase audience interaction
• **User-generated content**: Feature community submissions
• **Live sessions**: Real-time audience engagement

⏰ **Best Practices**
• **Post regularly**: Daily or every other day
• **24-hour window**: Stories disappear after 24 hours
• **Highlights**: Save important stories permanently
• **Consistent branding**: Maintain visual consistency

💡 **Advanced Tips**
• **Story series**: Create multi-part story sequences
• **Collaborations**: Tag friends for joint stories
• **Location stories**: Show you're at events
• **Countdown**: Build anticipation for launches
• **Swipe up**: Direct traffic to external links

📈 **Growing with Stories**
• **Cross-promotion**: Share story content in posts
• **Story highlights**: Create profile sections
• **Engagement boost**: Stories increase profile visits
• **Algorithm boost**: Active stories improve visibility

🎨 **Design Tips**
• **Brand colors**: Use consistent color schemes
• **Readable text**: Choose contrasting colors
• **Vertical format**: Optimize for mobile viewing
• **High quality**: Use good lighting and clear images

Start creating amazing stories today!`,

        media: `📸 **Media Content - Photo & Video Tips**

📷 **Photography Basics**
• **Lighting**: Natural light is always best
• **Composition**: Rule of thirds, leading lines, symmetry
• **Focus**: Sharp subjects, clean backgrounds
• **Angles**: Experiment with perspectives

🎥 **Video Content**
• **Short & engaging**: 15-60 seconds for maximum impact
• **High quality**: Steady camera, good audio
• **Hook early**: Grab attention in first 3 seconds
• **Clear message**: One main point per video

🖼️ **Image Optimization**
• **Resolution**: High quality but optimized file size
• **Aspect ratio**: 1:1 for square, 4:5 for vertical
• **File formats**: JPEG for photos, PNG for graphics
• **Alt text**: Describe images for accessibility

🎨 **Editing & Filters**
• **Consistency**: Use same filter style across posts
• **Enhancement**: Adjust brightness, contrast, saturation
• **Text overlays**: Add captions directly on images
• **Branding**: Include logos or watermarks

📱 **Mobile Photography**
• **Camera quality**: Use rear camera for better quality
• **Stabilization**: Keep camera steady or use tripods
• **Lighting apps**: Use phone flash creatively
• **Editing apps**: Lightroom Mobile, Snapseed, VSCO

🎭 **Content Types**
• **Flat lays**: Product photography, food, objects
• **Portraits**: People, pets, self-portraits
• **Landscapes**: Nature, cityscapes, travel
• **Action shots**: Sports, events, activities

📊 **Performance Tips**
• **First impression**: High-quality images get more engagement
• **Color psychology**: Different colors evoke different emotions
• **Text in images**: 80% of users read text in photos
• **Carousel posts**: Tell stories with multiple images

🔧 **Technical Specs**
• **Image size**: 1080x1080px minimum for square posts
• **Video format**: MP4 with H.264 codec
• **File size**: Under 15MB for images, 100MB for videos
• **Frame rate**: 30fps for smooth playback

💡 **Pro Tips**
• **Golden hour**: Shoot during morning/evening light
• **Negative space**: Use empty space for visual impact
• **Patterns**: Find and photograph interesting patterns
• **Reflections**: Creative use of mirrors, water, glass

📈 **Growing with Media**
• **User-generated content**: Feature community photos
• **Photo series**: Create themed collections
• **Challenges**: Photo challenges with hashtags
• **Collaborations**: Partner with photographers

Ready to create stunning visual content?`,

        chat: `💬 **Messaging System - Complete Guide**

📨 **Private Messaging**
• **One-on-one chats**: Direct conversations with individuals
• **Group chats**: Up to 50 people in group conversations
• **Message reactions**: React with emojis to messages
• **Message replies**: Reply to specific messages in threads

🎨 **Message Features**
• **Text messages**: Regular text with emoji support
• **Photo sharing**: Send images in conversations
• **Voice messages**: Record and send audio clips
• **GIF support**: Express yourself with animated GIFs

🔒 **Privacy Controls**
• **Message requests**: Control who can message you
• **Block users**: Prevent unwanted conversations
• **Report messages**: Flag inappropriate content
• **Mute conversations**: Silence notification for specific chats

💡 **Messaging Best Practices**
• **Quick responses**: Reply promptly to build relationships
• **Personal touch**: Use names and reference previous conversations
• **Value exchange**: Share helpful information and resources
• **Professional tone**: Maintain appropriate communication style

🚀 **Business Messaging**
• **Customer service**: Handle inquiries professionally
• **Collaboration**: Coordinate with team members
• **Networking**: Connect with industry professionals
• **Lead generation**: Convert conversations to opportunities

📱 **Mobile Experience**
• **Push notifications**: Get notified of new messages
• **Offline access**: Messages sync when you reconnect
• **Typing indicators**: See when others are typing
• **Read receipts**: Know when messages are seen

🔧 **Advanced Features**
• **Message search**: Find specific conversations quickly
• **Conversation pinning**: Keep important chats at top
• **Message scheduling**: Plan messages for later
• **Auto-responses**: Set up automated replies

📊 **Analytics & Insights**
• **Response times**: Track how quickly you reply
• **Conversation volume**: Monitor message frequency
• **Popular topics**: See what people message about most
• **Engagement rates**: Measure conversation quality

💼 **Professional Communication**
• **Clear communication**: Be concise and specific
• **Follow up**: Send reminders when needed
• **Meeting coordination**: Schedule calls and meetings
• **File sharing**: Exchange documents and resources

🎯 **Growing Your Network**
• **Initial outreach**: Personalized connection requests
• **Value first**: Offer help before asking for favors
• **Follow up**: Stay in touch with valuable connections
• **Group participation**: Join industry-specific groups

Start connecting with your network today!`
    };
}

// Chat Functions
function handleKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
    updateCharCount();
}

function handleNumericInput(event) {
    const input = event.target;
    const value = input.value;
    // Only allow numbers (0-9)
    const numericValue = value.replace(/[^0-9]/g, '');
    input.value = numericValue;
    updateCharCount();
}

function updateCharCount() {
    const input = document.getElementById('chatInput');
    const counter = document.getElementById('charCount');
    if (input && counter) {
        const count = input.value.length;
        counter.textContent = count + '/200';
    }
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();

    if (!message) return;

    // Check if it's a number selection (1-9)
    const numberMatch = message.match(/^(\d)$/);
    if (numberMatch && parseInt(numberMatch[1]) >= 1 && parseInt(numberMatch[1]) <= 9) {
        handleMenuSelection(numberMatch[1]);
        input.value = '';
        updateCharCount();
        return;
    }

    // Add user message
    addMessage(message, 'user');

    // Clear input
    input.value = '';

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
    .then(response => response.json())
    .then(data => {
        hideTyping();
        if (data.success) {
            addMessage(data.response + getMenuText(), 'ai');
        } else {
            addMessage('Sorry, I encountered an error. Please try again.' + getMenuText(), 'ai');
        }
    })
    .catch(error => {
        hideTyping();
        console.error('AI chat error:', error);
        addMessage('Sorry, I\'m having trouble connecting right now. Please try again later.' + getMenuText(), 'ai');
    });
}

function getMenuResponse(option) {
    const responses = {
        'help': `\n📋 **Help & Menu**\n\nWelcome to Laravel Social! Here's what I can help you with:\n\n• Platform navigation and features\n• Account setup and management\n• Content creation and posting\n• Privacy and security settings\n• Community engagement tips\n\nChoose another option or ask me anything!`,

        'writing': `\n📝 **Writing Better Posts**\n\n🎯 **Content Strategy Tips:**\n• Start with a hook (question, fact, or story)\n• Keep posts under 280 characters\n• Use emojis for personality\n• End with a call-to-action\n\n📸 **Visual Content:**\n• High-quality images work best\n• Use relevant hashtags (2-3 per post)\n• Tag people with @mentions\n\n💡 **Pro Tip:** Post consistently and engage with comments to grow your audience!`,

        'friends': `\n👥 **Finding Friends & People**\n\n🔍 **Discovery Methods:**\n• Explore trending posts\n• Search by interests or location\n• Check "Who to Follow" suggestions\n• Browse hashtag communities\n\n📱 **Connection Tips:**\n• Follow people you find interesting\n• Engage with their content first\n• Send personalized messages\n• Join niche communities\n\n🌟 **Growth Strategy:** Quality over quantity - focus on engaged followers!`,

        'stories': `\n📱 **Stories Guide**\n\n🎬 **Creating Stories:**\n• Tap your profile picture\n• Take photo/video or upload\n• Add text, stickers, effects\n• Share (lasts 24 hours)\n\n🎨 **Features:**\n• Text overlays and colors\n• Polls and questions\n• Music and effects\n• Save as highlights\n\n⏰ **Best Practices:**\n• Post daily for engagement\n• Use for behind-the-scenes\n• Interactive elements work great`,

        'privacy': `\n🔒 **Privacy & Security**\n\n🛡️ **Account Protection:**\n• Use strong, unique passwords\n• Enable 2FA when available\n• Review app permissions\n• Monitor login activity\n\n👀 **Privacy Controls:**\n• Set account to private\n• Control story visibility\n• Manage message requests\n• Block unwanted users\n\n💡 **Safety First:** Think before posting, be cautious with personal information!`,

        'profile': `\n👤 **Profile Optimization**\n\n🎯 **Essential Elements:**\n• Professional profile photo\n• Compelling bio (80-160 chars)\n• Link in bio\n• Consistent theme\n\n📊 **Tips:**\n• Use keywords for discoverability\n• Add personality with emojis\n• Update regularly\n• Complete all sections\n\n🚀 **Advanced:** Add story highlights and collaborate with others!`,

        'messaging': `\n💬 **Messaging System**\n\n📨 **How to Message:**\n• Go to Messages tab\n• Start new conversation\n• Search for users\n• Send text, photos, reactions\n\n🎨 **Features:**\n• Real-time chat\n• Online indicators\n• Message reactions\n• Group chats (up to 50)\n\n💡 **Best Practices:**\n• Respond promptly\n• Personalize messages\n• Use professionally\n• Respect privacy settings`,

        'settings': `\n⚙️ **Account Settings**\n\n🔧 **General Settings:**\n• Change password regularly\n• Update email and notifications\n• Manage connected apps\n• Set language preferences\n\n🎨 **Appearance:**\n• Dark/light mode\n• Font size and display\n• Media quality settings\n\n🔔 **Notifications:**\n• Control what you see\n• Mute specific content\n• Manage push notifications\n• Set quiet hours`,

        'getting-started': `\n🚀 **Getting Started Guide**\n\n📋 **Quick Setup:**\n1. Complete your profile\n2. Upload a profile photo\n3. Write a compelling bio\n4. Follow friends and interests\n\n📱 **First Steps:**\n• Explore the platform\n• Create your first post\n• Try stories feature\n• Connect with community\n\n💡 **Pro Tips:**\n• Post consistently\n• Engage with others\n• Use relevant hashtags\n• Be authentic and helpful`
    };

    return responses[option] || `\n🤔 I don't have specific information about "${option}" yet, but I can help with general questions about Laravel Social!`;
}

function getMenuText() {
    return `\n\n---\n\n**Choose an option:**\n1️⃣ Help & Menu\n2️⃣ Writing Posts\n3️⃣ Find Friends\n4️⃣ Stories Guide\n5️⃣ Privacy Help\n6️⃣ Profile Tips\n7️⃣ Messaging\n8️⃣ Account Settings\n9️⃣ Getting Started\n\nOr just type your question!`;
}

function addMessage(text, type) {
    const container = document.getElementById('chatMessages');
    if (!container) return;

    const messageDiv = document.createElement('div');
    messageDiv.className = 'message ' + (type === 'user' ? 'user' : 'ai');

    if (type === 'user') {
        // User messages appear instantly
        messageDiv.innerHTML = `
            <div class="message-bubble user-bubble">
                <p>${escapeHtml(text)}</p>
            </div>
        `;
        container.appendChild(messageDiv);
        container.scrollTop = container.scrollHeight;
    } else {
        // AI messages use typing effect
        messageDiv.innerHTML = `
            <div class="ai-avatar-small">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-bubble ai-bubble">
                <p class="typing-text"></p>
                <span class="typing-cursor">|</span>
            </div>
        `;
        container.appendChild(messageDiv);
        container.scrollTop = container.scrollHeight;

        // Start typing animation
        typeText(messageDiv, text);
    }
}

function typeText(messageDiv, fullText) {
    const textElement = messageDiv.querySelector('.typing-text');
    const cursorElement = messageDiv.querySelector('.typing-cursor');
    let currentIndex = 0;
    let typingSpeed = 25; // milliseconds per character
    let currentText = '';

    // Start cursor blinking
    const blinkInterval = setInterval(() => {
        if (cursorElement) {
            cursorElement.style.opacity = cursorElement.style.opacity === '0' ? '1' : '0';
        }
    }, 530);

    function typeCharacter() {
        if (currentIndex < fullText.length) {
            // Add next character
            const char = fullText[currentIndex];
            currentText += char;

            // Update the display
            textElement.innerHTML = escapeHtml(currentText);

            currentIndex++;

            // Auto-scroll to keep typing visible
            const container = document.getElementById('chatMessages');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }

            // Continue typing with slight random variation for realism
            const randomDelay = Math.random() * 10 - 5; // -5 to +5 ms variation
            setTimeout(typeCharacter, typingSpeed + randomDelay);
        } else {
            // Typing complete - stop blinking and hide cursor
            clearInterval(blinkInterval);
            if (cursorElement) {
                cursorElement.style.opacity = '0';
                setTimeout(() => {
                    cursorElement.style.display = 'none';
                }, 500);
            }
        }
    }

    // Start typing animation with a slight delay
    setTimeout(typeCharacter, 300);
}

/* Removed showTyping() and hideTyping() functions */

function performQuickSearch() {
    const input = document.getElementById('quickSearch');
    const query = input.value.trim();

    if (!query) return;

    // Show chat and send search query
    showChatInterface();
    addMessage(`Search: ${query}`, 'user');

    setTimeout(() => {
        addMessage(`🔍 Searching for "${query}"...\n\nI found some relevant information about your query. Here are the key points:\n\n• Point 1\n• Point 2\n• Point 3\n\nFor more specific help, try selecting a category from the menu above!`, 'ai');
    }, 500);

    input.value = '';
}

function autoResizeTextarea(textarea) {
    // Reset height to auto to get the correct scrollHeight
    textarea.style.height = 'auto';

    // Calculate the minimum height (1 line) and maximum height (5 lines)
    const lineHeight = parseInt(getComputedStyle(textarea).lineHeight);
    const minHeight = lineHeight * 1; // 1 line minimum
    const maxHeight = lineHeight * 5; // 5 lines maximum
    const scrollHeight = textarea.scrollHeight;

    // Set the height within bounds
    const newHeight = Math.min(Math.max(scrollHeight, minHeight), maxHeight);
    textarea.style.height = newHeight + 'px';

    // Update character count
    updateCharCount();
}

function updateCharCount() {
    const textarea = document.getElementById('chatInput');
    const counter = document.getElementById('charCount');
    if (textarea && counter) {
        const count = textarea.value.length;
        const max = parseInt(textarea.getAttribute('maxlength')) || 500;
        counter.textContent = count + '/' + max;

        // Add warning class for near limit
        if (count > max * 0.9) {
            counter.classList.add('warning');
        } else {
            counter.classList.remove('warning');
        }
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close AI Assistant
function closeAIAssistant() {
    const aiAssistant = document.querySelector('.ai-assistant');
    if (aiAssistant) {
        aiAssistant.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => {
            window.location.href = '/';
        }, 300);
    }
}

// Add fade out animation
const fadeOutStyle = document.createElement('style');
fadeOutStyle.textContent = `
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
`;
document.head.appendChild(fadeOutStyle);

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set up initial state
    showMainMenu();

    // Handle mobile viewport height issues
    function setVH() {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }

    setVH();
    window.addEventListener('resize', setVH);
    window.addEventListener('orientationchange', setVH);
});
</script>
@endsection
