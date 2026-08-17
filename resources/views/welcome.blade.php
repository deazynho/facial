<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ollama Chat</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Tailwind CDN -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a; /* Slate 900 */
        }
        
        /* Custom scrollbar for chat */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        .typing-indicator span {
            display: inline-block;
            width: 6px;
            height: 6px;
            background-color: #94a3b8;
            border-radius: 50%;
            margin: 0 2px;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
        .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
    </style>
</head>
<body class="text-gray-100 antialiased h-full flex flex-col overflow-hidden selection:bg-indigo-500 selection:text-white">

    <!-- Header -->
    <header class="bg-slate-900 border-b border-slate-800 p-4 flex items-center justify-center shrink-0 z-10 shadow-sm">
        <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-fuchsia-400 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            Ollama AI Chat
        </h1>
    </header>

    <!-- Chat History Area -->
    <main id="chatContainer" class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-6 bg-slate-900 relative">
        
        <!-- Welcome Message -->
        <div class="flex flex-col items-center justify-center h-full text-center opacity-70" id="emptyState">
            <div class="w-20 h-20 bg-indigo-600/20 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold mb-2">How can I help you today?</h2>
            <p class="text-slate-400 max-w-md">I am your local AI assistant running on Ollama. Ask me anything.</p>
        </div>

    </main>

    <!-- Input Area -->
    <div class="bg-slate-900 border-t border-slate-800 p-4 sm:p-6 shrink-0 relative">
        <div class="max-w-4xl mx-auto relative">
            
            <form id="chatForm" class="relative flex items-end bg-slate-800 rounded-2xl shadow-lg border border-slate-700 transition-colors focus-within:border-indigo-500/50 focus-within:ring-1 focus-within:ring-indigo-500/50">
                
                <textarea 
                    id="messageInput" 
                    rows="1" 
                    placeholder="Message Ollama..." 
                    class="w-full bg-transparent border-none focus:ring-0 text-slate-100 placeholder-slate-400 p-4 pl-5 resize-none max-h-48 rounded-2xl outline-none"
                    style="min-height: 56px;"
                ></textarea>
                
                <button type="submit" id="sendBtn" class="absolute right-2 bottom-2 p-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </form>
            
            <div class="text-center mt-3 text-xs text-slate-500">
                AI models can make mistakes. Consider verifying important information.
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chatForm = document.getElementById('chatForm');
            const messageInput = document.getElementById('messageInput');
            const chatContainer = document.getElementById('chatContainer');
            const emptyState = document.getElementById('emptyState');
            const sendBtn = document.getElementById('sendBtn');

            // Auto-resize textarea
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
                if(this.value.trim() === '') {
                    sendBtn.disabled = true;
                } else {
                    sendBtn.disabled = false;
                }
            });

            // Handle Enter key to send (Shift+Enter for new line)
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if(this.value.trim() !== '') {
                        chatForm.dispatchEvent(new Event('submit'));
                    }
                }
            });

            sendBtn.disabled = true; // Initially disabled

            function scrollToBottom() {
                chatContainer.scrollTo({
                    top: chatContainer.scrollHeight,
                    behavior: 'smooth'
                });
            }

            function appendMessage(role, content) {
                if (emptyState && emptyState.style.display !== 'none') {
                    emptyState.style.display = 'none';
                }

                const wrapperDiv = document.createElement('div');
                wrapperDiv.className = `flex w-full max-w-4xl mx-auto ${role === 'user' ? 'justify-end' : 'justify-start'}`;

                const messageDiv = document.createElement('div');
                
                if (role === 'user') {
                    messageDiv.className = 'bg-indigo-600 text-white px-5 py-3 rounded-2xl rounded-br-sm max-w-[85%] shadow-md whitespace-pre-wrap';
                    messageDiv.textContent = content;
                } else {
                    messageDiv.className = 'bg-slate-800 text-slate-200 border border-slate-700 px-5 py-4 rounded-2xl rounded-bl-sm max-w-[90%] shadow-md whitespace-pre-wrap leading-relaxed';
                    // Very basic parsing for paragraphs (can be replaced with marked.js for full markdown)
                    messageDiv.innerHTML = content.replace(/\n\n/g, '<br><br>');
                }

                wrapperDiv.appendChild(messageDiv);
                chatContainer.appendChild(wrapperDiv);
                scrollToBottom();

                return messageDiv; // Return element so we can modify it later if needed
            }

            function appendTypingIndicator() {
                const wrapperDiv = document.createElement('div');
                wrapperDiv.id = 'typingIndicator';
                wrapperDiv.className = `flex w-full max-w-4xl mx-auto justify-start`;

                const indicatorDiv = document.createElement('div');
                indicatorDiv.className = 'bg-slate-800 border border-slate-700 px-5 py-4 rounded-2xl rounded-bl-sm shadow-md flex items-center typing-indicator';
                indicatorDiv.innerHTML = '<span></span><span></span><span></span>';

                wrapperDiv.appendChild(indicatorDiv);
                chatContainer.appendChild(wrapperDiv);
                scrollToBottom();
                
                return wrapperDiv;
            }

            chatForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const text = messageInput.value.trim();
                if (!text) return;

                // 1. Show user message
                appendMessage('user', text);
                
                // 2. Reset input
                messageInput.value = '';
                messageInput.style.height = 'auto';
                sendBtn.disabled = true;

                // 3. Show typing indicator
                const typingEl = appendTypingIndicator();

                try {
                    // 4. Call API
                    const response = await fetch('/api/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ message: text })
                    });

                    const data = await response.json();
                    
                    // 5. Remove typing indicator
                    typingEl.remove();

                    if (!response.ok) {
                        throw new Error(data.message || 'API Error');
                    }

                    // 6. Show AI response
                    appendMessage('ai', data.response || "No response received.");

                } catch (err) {
                    typingEl.remove();
                    appendMessage('ai', 'Error: ' + err.message);
                }
            });
        });
    </script>
</body>
</html>
