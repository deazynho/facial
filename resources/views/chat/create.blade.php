@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8 max-w-md">
    <div class="bg-white rounded-lg shadow p-8">
        <h1 class="text-3xl font-bold mb-6">Start New Chat</h1>
        
        <form action="{{ route('chats.store') }}" method="POST">
            @csrf
            
            <p class="text-gray-600 mb-6">
                Begin a new conversation with our AI assistant. Messages are saved for future reference.
            </p>

            <button 
                type="submit" 
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded"
            >
                Create Chat
            </button>

            <a 
                href="{{ route('chats.index') }}" 
                class="block w-full text-center mt-4 text-blue-500 hover:text-blue-600"
            >
                Back to Chats
            </a>
        </form>
    </div>
</div>
@endsection

