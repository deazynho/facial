<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    protected string $baseUrl;

    public function __construct()
    {
        // Default to localhost if not specified in env
        $this->baseUrl = env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434');
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
            
            // Vision models in Ollama like llava can accept images
            $response = Http::post("{$this->baseUrl}/api/generate", [
                'model' => env('OLLAMA_VISION_MODEL', 'llava'),
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
}
