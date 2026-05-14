<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlackController;

// Slack routes
Route::get('/', [SlackController::class, 'index']);
Route::get('/slack-form', [SlackController::class, 'index']);
Route::post('/send-message', [SlackController::class, 'sendMessage']);
Route::get('/messages', [SlackController::class, 'list']);
Route::delete('/delete-message/{id}', [SlackController::class, 'delete']);
Route::get('/resend-message/{id}', [SlackController::class, 'resend']);
Route::delete('/bulk-delete', [SlackController::class, 'bulkDelete']);
Route::get('/export-messages', [SlackController::class, 'export']);
Route::get('/dashboard', [SlackController::class, 'dashboard']);