@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold">Chats</h1>
        <a href="{{ route('chats.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            New Chat
        </a>
    </div>

    @if($chats->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($chats as $chat)
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                    <h2 class="text-lg font-semibold mb-2 text-gray-800">
                        <a href="{{ route('chats.show', $chat) }}" class="hover:text-blue-500">
                            {{ $chat->title }}
                        </a>
                    </h2>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ $chat->messages->count() }} message{{ $chat->messages->count() !== 1 ? 's' : '' }}
                    </p>
                    <p class="text-xs text-gray-500 mb-4">
                        Created {{ $chat->created_at->diffForHumans() }}
                    </p>
                    <div class="flex gap-2">
                        <a href="{{ route('chats.show', $chat) }}" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded text-center">
                            Open
                        </a>
                        <form action="{{ route('chats.destroy', $chat) }}" method="POST" onsubmit="return confirm('Delete this chat?');" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $chats->links() }}
    @else
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <p class="text-gray-600 mb-4">No chats yet. Start a new conversation!</p>
            <a href="{{ route('chats.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded">
                Create First Chat
            </a>
        </div>
    @endif
</div>
@endsection

