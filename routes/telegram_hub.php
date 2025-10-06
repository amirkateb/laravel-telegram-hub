<?php

use Illuminate\Support\Facades\Route;
use Amirkateb\TelegramHub\Http\Controllers\WebhookController;

$prefix = config('telegram_hub.routes.prefix', 'telegram-hub');
$middleware = config('telegram_hub.routes.middleware', ['api']);

Route::middleware($middleware)
    ->prefix($prefix)
    ->group(function () {
        Route::post('/webhook/{bot}', [WebhookController::class, 'handle'])->name('telegram_hub.webhook.handle');
        Route::post('/webhook', [WebhookController::class, 'handleDefault'])->name('telegram_hub.webhook.handle_default');
    });