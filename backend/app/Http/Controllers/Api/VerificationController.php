<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    protected OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function verify(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Store temporarily
            $path = $image->store('temp_faces', 'local');
            $absolutePath = Storage::disk('local')->path($path);

            // Let's ask Ollama's vision model to extract features or describe the face
            $prompt = "Analyze this image for facial verification. Describe the facial features in detail. If there is no face, state 'No face detected'.";
            $response = $this->ollamaService->verifyFace($absolutePath, $prompt);

            // Clean up temporary image
            Storage::disk('local')->delete($path);

            if ($response && isset($response['response'])) {
                return response()->json([
                    'success' => true,
                    'verification_result' => $response['response'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to communicate with Ollama or no valid response.',
            ], 500);
        }

        return response()->json([
            'success' => false,
            'message' => 'Image upload failed.',
        ], 400);
    }
}
