/* AI Chat Page JavaScript */

document.addEventListener('DOMContentLoaded', function() {
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

// Expose sendQuickMessage globally for inline onclick handlers
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
            <div class="typing-indicator">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </div>
            <div class="message-time">${time}</div>
        </div>
    `;

    chatContainer.appendChild(div);
    scrollToBottom();

    // Start typing effect after a short delay (simulating AI "thinking")
    setTimeout(() => {
        startTypewriterEffect(div, fullText);
    }, 400);
}

function startTypewriterEffect(messageDiv, fullText) {
    const bubble = messageDiv.querySelector('.message-bubble');
    const contentP = document.createElement('p');
    contentP.className = 'message-content';
    contentP.style.minHeight = '20px';

    // Replace typing indicator with content paragraph
    const typingIndicator = bubble.querySelector('.typing-indicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
    bubble.insertBefore(contentP, bubble.querySelector('.message-time'));

    // Add cursor
    const cursor = document.createElement('span');
    cursor.className = 'cursor';
    contentP.appendChild(cursor);

    let charIndex = 0;
    isTyping = true;

    // Show stop button
    stopBtn.style.display = 'flex';

    stopBtn.onclick = function() {
        isTyping = false;
        clearTimeout(typingTimeout);
        stopBtn.style.display = 'none';
        cursor.remove();
        contentP.innerHTML = formatResponse(fullText);
        setTimeout(scrollToBottom, 50);
    };

    function typeNextChar() {
        if (!isTyping) return;

        if (charIndex < fullText.length) {
            const remaining = fullText.substring(charIndex);
            let chunk = '';
            let charsToSkip = 1;

            // Check for emoji first (before processing other chars)
            const emojiMatch = remaining.match(/^[\p{Emoji}]/u);
            if (emojiMatch) {
                chunk = emojiMatch[0];
                charsToSkip = 1;
            }
            // If we're at a line break, add it
            else if (fullText[charIndex] === '\n') {
                chunk = '<br>';
                charsToSkip = 1;
            }
            // If we're at bold marker, add full bold section
            else if (remaining.startsWith('**')) {
                const endBold = remaining.indexOf('**', 2);
                if (endBold !== -1) {
                    const boldText = remaining.substring(2, endBold);
                    chunk = '<strong>' + boldText + '</strong>';
                    charsToSkip = endBold + 2;
                } else {
                    chunk = remaining[0];
                    charsToSkip = 1;
                }
            }
            // Otherwise add next character
            else {
                chunk = remaining[0];
                if (chunk === ' ') chunk = '&nbsp;';
                else if (chunk === '<') chunk = '&lt;';
                else if (chunk === '>') chunk = '&gt;';
                charsToSkip = 1;
            }

            // Insert before cursor
            const tempSpan = document.createElement('span');
            tempSpan.innerHTML = chunk;
            while (tempSpan.firstChild) {
                contentP.insertBefore(tempSpan.firstChild, cursor);
            }

            charIndex += charsToSkip;
            scrollToBottom();

            // Natural typing speed with variation
            const speed = getTypingSpeed(fullText[charIndex - 1]) + Math.random() * 10;
            typingTimeout = setTimeout(typeNextChar, speed);
        } else {
            // Finished
            stopBtn.style.display = 'none';
            cursor.remove();
            // Apply final formatting
            contentP.innerHTML = formatResponse(fullText);
        }
    }

    typeNextChar();
}

function getTypingSpeed(char) {
    // Fast, natural AI typing speeds (like ChatGPT/Claude)
    if (char === '\n') return 50;      // Pause slightly at line breaks
    if (char === ' ') return 25;       // Faster for spaces
    if (char.match(/[a-z0-9]/i)) return 15;  // Very fast for letters/numbers
    if (char.match(/[.,!?;:]/)) return 80;   // Pause at punctuation
    if (char.match(/[\u0600-\u06FF]/)) return 20;  // Arabic characters
    return 20;  // Default fast speed
}

function stopGenerating() {
    isTyping = false;
    clearTimeout(typingTimeout);
    // Remove cursor if it exists
    const cursor = document.querySelector('.cursor');
    if (cursor) cursor.remove();
    stopBtn.style.display = 'none';
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
    // Escape HTML first
    let formatted = escapeHtml(text);

    // Wrap emojis in span tags for proper display
    const emojiRegex = /[\p{Emoji}]/gu;
    formatted = formatted.replace(emojiRegex, '<span class="emoji">$&</span>');

    // Apply bold formatting
    formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

    // Handle line breaks
    formatted = formatted.replace(/\n/g, '<br>');

    return formatted;
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
    if (!confirm('Are you sure you want to clear the chat?')) return;

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

// Expose clearChat globally for inline onclick handlers
window.clearChat = clearChat;
});
