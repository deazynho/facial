<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/chats');
});

Route::get('/chats', function () {
    return view('welcome');
});
