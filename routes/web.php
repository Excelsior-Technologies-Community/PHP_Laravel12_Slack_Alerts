<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlackController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/slack-form', [SlackController::class, 'index']);
Route::post('/send-message', [SlackController::class, 'sendMessage']);
Route::get('/messages', [SlackController::class, 'list']);
