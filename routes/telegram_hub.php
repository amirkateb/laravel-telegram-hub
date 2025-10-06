<?php

use Illuminate\Support\Facades\Route;
use Amirkateb\TelegramHub\Http\Controllers\WebhookController;
use Amirkateb\TelegramHub\Http\Controllers\WebhookSetupController;

$prefix = config('telegram_hub.routes.prefix', 'telegram-hub');
$middleware = config('telegram_hub.routes.middleware', ['api']);

Route::middleware($middleware)
    ->prefix($prefix)
    ->group(function () {
        Route::post('/webhook/{bot}', [WebhookController::class, 'handle'])
            ->name('telegram_hub.webhook.handle');

        Route::post('/webhook', [WebhookController::class, 'handleDefault'])
            ->name('telegram_hub.webhook.handle_default');

        Route::post('/set-webhook/{bot}', [WebhookSetupController::class, 'set'])
            ->name('telegram_hub.webhook.set');

        Route::delete('/delete-webhook/{bot}', [WebhookSetupController::class, 'delete'])
            ->name('telegram_hub.webhook.delete');

        Route::get('/webhook-info/{bot}', [WebhookSetupController::class, 'info'])
            ->name('telegram_hub.webhook.info');
    });