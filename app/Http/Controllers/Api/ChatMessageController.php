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

        // Get Ollama response asynchronously (don't block the request)
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
                'error' => 'Failed to get response from Ollama: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getOllamaResponse(string $message, Chat $chat): string
    {
        $baseUrl = env('OLLAMA_API_URL', 'http://ollama.railway.internal:11434');
        $model = env('OLLAMA_MODEL', 'qwen3:4b');

        try {
            // Simple prompt without history for faster response
            $response = Http::timeout(300)
                ->connectTimeout(10)
                ->post("{$baseUrl}/api/generate", [
                    'model' => $model,
                    'prompt' => $message,
                    'stream' => false,
                    'temperature' => 0.7,
                ])
                ->throw()
                ->json();

            return trim($response['response'] ?? 'No response received');
        } catch (\Illuminate\Http\Client\ConnectException $e) {
            throw new \Exception('Cannot connect to Ollama at ' . $baseUrl . ': ' . $e->getMessage());
        } catch (\Illuminate\Http\Client\RequestException $e) {
            throw new \Exception('Ollama request failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Error: ' . $e->getMessage());
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

