<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\VerificationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/verify', [VerificationController::class, 'verify']);

Route::post('/ollama/pull-models', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('ollama:pull-models');
        return response()->json([
            'success' => true,
            'message' => 'Models pulled successfully',
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/chats/message/send', [ChatMessageController::class, 'send']);
    Route::get('/chats/{chat}/messages', [ChatMessageController::class, 'getMessages']);
});

