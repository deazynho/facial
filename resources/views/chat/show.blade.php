@extends('layouts.app')

@section('content')
<div class="container mx-auto h-screen flex flex-col py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{{ $chat->title }}</h1>
        <a href="{{ route('chats.index') }}" class="text-blue-500 hover:text-blue-600">
            ← Back to Chats
        </a>
    </div>

    <!-- Messages Container -->
    <div id="messages-container" class="flex-1 bg-white rounded-lg shadow mb-4 p-6 overflow-y-auto">
        @forelse($messages as $message)
            <div class="mb-4 {{ $message->role === 'user' ? 'text-right' : 'text-left' }}">
                <div class="inline-block max-w-md px-4 py-2 rounded-lg {{ $message->role === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }}">
                    <p class="text-sm font-semibold mb-1">{{ $message->role === 'user' ? 'You' : 'Assistant' }}</p>
                    <p class="whitespace-pre-wrap">{{ $message->content }}</p>
                    <p class="text-xs {{ $message->role === 'user' ? 'text-blue-100' : 'text-gray-600' }} mt-1">
                        {{ $message->created_at->format('H:i') }}
                    </p>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-600 py-12">
                <p>No messages yet. Start typing to begin the conversation!</p>
            </div>
        @endforelse
    </div>

    <!-- Input Form -->
    <form id="message-form" class="flex gap-2">
        @csrf
        <input 
            type="hidden" 
            name="chat_id" 
            value="{{ $chat->id }}"
        >
        <textarea 
            id="message-input"
            name="message" 
            placeholder="Type your message..." 
            class="flex-1 border border-gray-300 rounded-lg p-3 resize-none" 
            rows="3"
            required
        ></textarea>
        <button 
            type="submit" 
            id="send-btn"
            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded h-full"
        >
            Send
        </button>
    </form>
</div>

<script>
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    const messagesContainer = document.getElementById('messages-container');

    messageForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        if (!message) return;

        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending...';

        try {
            const response = await fetch('/api/chats/message/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({
                    chat_id: messageForm.chat_id.value,
                    message: message,
                }),
            });

            const data = await response.json();

            if (data.success) {
                // Add user message
                addMessageToUI(data.user_message.content, 'user', data.user_message.created_at);
                
                // Add assistant message
                addMessageToUI(data.assistant_message.content, 'assistant', data.assistant_message.created_at);

                messageInput.value = '';
                messageInput.focus();
            } else {
                alert('Error: ' + data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to send message');
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send';
        }
    });

    function addMessageToUI(content, role, timestamp) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `mb-4 ${role === 'user' ? 'text-right' : 'text-left'}`;
        
        const time = new Date(timestamp);
        const timeStr = time.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

        messageDiv.innerHTML = `
            <div class="inline-block max-w-md px-4 py-2 rounded-lg ${role === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'}">
                <p class="text-sm font-semibold mb-1">${role === 'user' ? 'You' : 'Assistant'}</p>
                <p class="whitespace-pre-wrap">${content}</p>
                <p class="text-xs ${role === 'user' ? 'text-blue-100' : 'text-gray-600'} mt-1">${timeStr}</p>
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Scroll to bottom on load
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
</script>
@endsection

