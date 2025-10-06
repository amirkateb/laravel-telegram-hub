<?php

namespace Amirkateb\TelegramHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function handle(Request $request, string $bot)
    {
        $payload = $request->all();
        Log::channel(config('telegram_hub.log_channel'))->info('telegram_hub.webhook', ['bot' => $bot, 'payload' => $payload]);

        try {
            DB::table('telegram_logs')->insert([
                'direction' => 'inbound',
                'bot_key' => $bot,
                'bot_id' => null,
                'chat_id' => isset($payload['message']['chat']['id']) ? (string) $payload['message']['chat']['id'] : null,
                'message_id' => isset($payload['message']['message_id']) ? (string) $payload['message']['message_id'] : null,
                'method' => 'webhook',
                'status_code' => 200,
                'ok' => true,
                'error_code' => null,
                'error_description' => null,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'response' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
        }

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

        try {
            DB::table('telegram_logs')->insert([
                'direction' => 'inbound',
                'bot_key' => null,
                'bot_id' => null,
                'chat_id' => isset($payload['message']['chat']['id']) ? (string) $payload['message']['chat']['id'] : null,
                'message_id' => isset($payload['message']['message_id']) ? (string) $payload['message']['message_id'] : null,
                'method' => 'webhook',
                'status_code' => 200,
                'ok' => true,
                'error_code' => null,
                'error_description' => null,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'response' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
        }

        return response()->json(['ok' => true]);
    }
}