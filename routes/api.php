<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/verify', [\App\Http\Controllers\Api\VerificationController::class, 'verify']);
Route::post('/chat', [\App\Http\Controllers\Api\ChatController::class, 'chat']);
