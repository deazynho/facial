<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Chat::where('user_id', auth()->id())
            ->with('messages')
            ->latest()
            ->paginate(10);

        return view('chat.index', compact('chats'));
    }

    public function show(Chat $chat)
    {
        $this->authorize('view', $chat);
        
        $messages = $chat->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('chat.show', compact('chat', 'messages'));
    }

    public function create()
    {
        return view('chat.create');
    }

    public function store(Request $request)
    {
        $chat = Chat::create([
            'user_id' => auth()->id(),
            'title' => 'New Chat'
        ]);

        return redirect()->route('chats.show', $chat);
    }

    public function destroy(Chat $chat)
    {
        $this->authorize('delete', $chat);
        
        $chat->messages()->delete();
        $chat->delete();

        return redirect()->route('chats.index')->with('success', 'Chat deleted');
    }
}

