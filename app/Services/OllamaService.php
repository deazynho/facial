<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    protected string $baseUrl;

    public function __construct()
    {
        // Use OLLAMA_API_URL from Railway environment, fallback to OLLAMA_HOST, then localhost
        $this->baseUrl = env('OLLAMA_API_URL') 
            ?? ('http://' . env('OLLAMA_HOST', '127.0.0.1:11434'));
    }

    /**
     * Verifies facial similarity or extracts features using an Ollama Vision model.
     * 
     * @param string $imagePath The absolute path to the uploaded image.
     * @param string $prompt The prompt to ask the model (e.g., "Describe the face in this image.")
     * @return array|null
     */
    public function verifyFace(string $imagePath, string $prompt = "Are there any faces in this image? Describe them."): ?array
    {
        try {
            // Read image and encode to base64
            $imageData = base64_encode(file_get_contents($imagePath));
            
            // Use Mistral or configured vision model for vision tasks
            $model = env('OLLAMA_VISION_MODEL', 'mistral');
            
            // Ollama /api/generate endpoint for streaming text responses
            $response = Http::post("{$this->baseUrl}/api/generate", [
                'model' => $model,
                'prompt' => $prompt,
                'images' => [$imageData],
                'stream' => false,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Ollama API error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Ollama connection error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Chat with the Ollama model (supports tools/function calling with compatible models)
     * 
     * @param string $message The user message
     * @param array|null $tools Optional tools/functions for the model to use
     * @return array|null
     */
    public function chat(string $message, ?array $tools = null): ?array
    {
        try {
            $model = env('OLLAMA_MODEL', 'mistral');
            
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $message],
                ],
                'stream' => false,
            ];

            // Add tools if provided and model supports them (e.g., Mistral)
            if ($tools !== null) {
                $payload['tools'] = $tools;
            }

            $response = Http::post("{$this->baseUrl}/api/chat", $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Ollama chat API error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Ollama chat connection error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}

