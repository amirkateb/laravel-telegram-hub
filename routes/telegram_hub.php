<?php

use Illuminate\Support\Facades\Route;
use Amirkateb\TelegramHub\Http\Controllers\WebhookController;

Route::middleware(['api'])
    ->prefix('telegram-hub')
    ->group(function () {
        Route::post('/webhook/{bot}', [WebhookController::class, 'handle'])->name('telegram_hub.webhook.handle');
        Route::post('/webhook', [WebhookController::class, 'handleDefault'])->name('telegram_hub.webhook.handle_default');
    });