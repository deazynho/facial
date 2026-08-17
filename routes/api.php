<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/verify', [\App\Http\Controllers\Api\VerificationController::class, 'verify']);

Route::post('/ollama/pull-models', function () {
    try {
        Artisan::call('ollama:pull-models');
        return response()->json([
            'success' => true,
            'message' => 'Models pulled successfully',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

