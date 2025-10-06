<?php

namespace Amirkateb\TelegramHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, string $bot)
    {
        $payload = $request->all();
        Log::channel(config('telegram_hub.log_channel'))->info('telegram_hub.webhook', ['bot' => $bot, 'payload' => $payload]);

        if (isset($payload['message']['chat']['id'])) {
            $chatId = $payload['message']['chat']['id'];
            app('telegram.hub')->sendMessage([
                'chat_id' => $chatId,
                'text' => 'OK',
            ], config('telegram_hub.bots.' . $bot));
        }

        return response()->json(['ok' => true]);
    }

    public function handleDefault(Request $request)
    {
        $payload = $request->all();
        Log::channel(config('telegram_hub.log_channel'))->info('telegram_hub.webhook_default', ['payload' => $payload]);

        return response()->json(['ok' => true]);
    }
}