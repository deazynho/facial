<?php

namespace App\Http\Controllers\Api;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class ChatMessageController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string|max:2000',
        ]);

        $chat = Chat::findOrFail($validated['chat_id']);
        $this->authorize('view', $chat);

        // Save user message
        $userMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // Get Ollama response
        try {
            $response = $this->getOllamaResponse($validated['message'], $chat);

            // Save assistant message
            $assistantMessage = ChatMessage::create([
                'chat_id' => $chat->id,
                'role' => 'assistant',
                'content' => $response,
            ]);

            // Update chat title if this is the first message
            if ($chat->messages()->count() === 2) {
                $chat->update([
                    'title' => substr($validated['message'], 0, 50) . (strlen($validated['message']) > 50 ? '...' : '')
                ]);
            }

            return response()->json([
                'success' => true,
                'user_message' => $userMessage,
                'assistant_message' => $assistantMessage,
            ]);
        } catch (\Exception $e) {
            $userMessage->delete();
            return response()->json([
                'success' => false,
                'error' => 'Failed to get response: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getOllamaResponse(string $message, Chat $chat): string
    {
        $baseUrl = env('OLLAMA_API_URL') ?? ('http://' . env('OLLAMA_HOST', '127.0.0.1:11434'));

        // Get conversation history
        $messages = $chat->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        // Build prompt with conversation history
        $prompt = '';
        foreach ($messages as $msg) {
            if ($msg['role'] === 'user') {
                $prompt .= "User: {$msg['content']}\n";
            } else {
                $prompt .= "Assistant: {$msg['content']}\n";
            }
        }
        $prompt .= "User: {$message}\nAssistant:";

        try {
            $response = Http::timeout(120)
                ->post("{$baseUrl}/api/generate", [
                    'model' => env('OLLAMA_MODEL', 'qwen3:4b'),
                    'prompt' => $prompt,
                    'stream' => false,
                    'temperature' => 0.7,
                    'top_p' => 0.9,
                    'top_k' => 40,
                ])
                ->throw()
                ->json();

            return trim($response['response'] ?? '');
        } catch (\Exception $e) {
            throw new \Exception('Ollama API error: ' . $e->getMessage());
        }
    }

    public function getMessages(Chat $chat)
    {
        $this->authorize('view', $chat);

        $messages = $chat->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }
}

