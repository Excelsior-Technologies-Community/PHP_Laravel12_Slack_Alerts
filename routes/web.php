<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlackController;

/*
|--------------------------------------------------------------------------
| Slack Routes
|--------------------------------------------------------------------------
*/

// Main routes
Route::get('/', [SlackController::class, 'index'])->name('home');
Route::get('/slack-form', [SlackController::class, 'index'])->name('slack.form');
Route::post('/send-message', [SlackController::class, 'sendMessage'])->name('slack.send');

// Message management
Route::get('/messages', [SlackController::class, 'list'])->name('slack.messages');
Route::delete('/delete-message/{id}', [SlackController::class, 'delete'])->name('slack.delete');
Route::get('/resend-message/{id}', [SlackController::class, 'resend'])->name('slack.resend');
Route::delete('/bulk-delete', [SlackController::class, 'bulkDelete'])->name('slack.bulk.delete');
Route::get('/export-messages', [SlackController::class, 'export'])->name('slack.export');
Route::get('/dashboard', [SlackController::class, 'dashboard'])->name('slack.dashboard');

// Template management
Route::get('/templates', [SlackController::class, 'templates'])->name('slack.templates');
Route::post('/templates', [SlackController::class, 'storeTemplate'])->name('slack.templates.store');
Route::delete('/delete-template/{id}', [SlackController::class, 'deleteTemplate'])->name('slack.templates.delete');
Route::get('/toggle-template/{id}', [SlackController::class, 'toggleTemplate'])->name('slack.templates.toggle');