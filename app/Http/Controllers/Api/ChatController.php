<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OllamaService;

class ChatController extends Controller
{
    protected OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $response = $this->ollamaService->chat($request->input('message'));

        if ($response && isset($response['response'])) {
            return response()->json([
                'success' => true,
                'response' => $response['response'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to communicate with Ollama or no valid response.',
        ], 500);
    }
}
