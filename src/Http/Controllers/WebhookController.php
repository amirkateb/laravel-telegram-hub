<?php

namespace Amirkateb\TelegramHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function handle(Request $request, string $bot)
    {
        $db = DB::table('telegram_bots')->where('key', $bot)->first();
        if ($db && isset($db->enabled) && !$db->enabled) {
            return response()->json(['ok' => false, 'description' => 'Bot disabled'], 403);
        }

        $expected = $db && $db->secret_token ? $db->secret_token : config('telegram_hub.webhook.secret_token');
        $provided = $request->header('X-Telegram-Bot-Api-Secret-Token');
        if ($expected && $provided !== $expected) {
            return response()->json(['ok' => false], 403);
        }

        $payload = $request->all();
        $ids = $this->extractIds($payload);

        $this->logInbound($bot, $db ? $db->id : null, $ids['chat_id'], $ids['message_id'], $payload);

        $token = $db->token ?? config('telegram_hub.bots.' . $bot);
        if (!$token) {
            return response()->json(['ok' => false, 'description' => 'Bot token not found'], 422);
        }

        $this->autoRespond($payload, $token);

        return response()->json(['ok' => true]);
    }

    public function handleDefault(Request $request)
    {
        $defaultKey = (string) config('telegram_hub.default_bot', 'default');
        $db = DB::table('telegram_bots')->where('key', $defaultKey)->first();
        if ($db && isset($db->enabled) && !$db->enabled) {
            return response()->json(['ok' => false, 'description' => 'Bot disabled'], 403);
        }

        $expected = $db && $db->secret_token ? $db->secret_token : config('telegram_hub.webhook.secret_token');
        $provided = $request->header('X-Telegram-Bot-Api-Secret-Token');
        if ($expected && $provided !== $expected) {
            return response()->json(['ok' => false], 403);
        }

        $payload = $request->all();
        $ids = $this->extractIds($payload);

        $this->logInbound($defaultKey, $db ? $db->id : null, $ids['chat_id'], $ids['message_id'], $payload);

        $token = $db->token ?? config('telegram_hub.bots.' . $defaultKey);
        if (!$token) {
            return response()->json(['ok' => false, 'description' => 'Bot token not found'], 422);
        }

        $this->autoRespond($payload, $token);

        return response()->json(['ok' => true]);
    }

    protected function extractIds(array $payload): array
    {
        $chatId = null;
        $messageId = null;

        if (isset($payload['message'])) {
            $chatId = $payload['message']['chat']['id'] ?? null;
            $messageId = $payload['message']['message_id'] ?? null;
        } elseif (isset($payload['edited_message'])) {
            $chatId = $payload['edited_message']['chat']['id'] ?? null;
            $messageId = $payload['edited_message']['message_id'] ?? null;
        } elseif (isset($payload['channel_post'])) {
            $chatId = $payload['channel_post']['chat']['id'] ?? null;
            $messageId = $payload['channel_post']['message_id'] ?? null;
        } elseif (isset($payload['edited_channel_post'])) {
            $chatId = $payload['edited_channel_post']['chat']['id'] ?? null;
            $messageId = $payload['edited_channel_post']['message_id'] ?? null;
        } elseif (isset($payload['callback_query']['message'])) {
            $chatId = $payload['callback_query']['message']['chat']['id'] ?? null;
            $messageId = $payload['callback_query']['message']['message_id'] ?? null;
        }

        return ['chat_id' => $chatId, 'message_id' => $messageId];
        }

    protected function logInbound(?string $botKey, ?int $botId, $chatId, $messageId, array $payload): void
    {
        try {
            DB::table('telegram_logs')->insert([
                'direction' => 'inbound',
                'bot_key' => $botKey,
                'bot_id' => $botId,
                'chat_id' => $chatId ? (string) $chatId : null,
                'message_id' => $messageId ? (string) $messageId : null,
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
    }

    protected function autoRespond(array $payload, string $token): void
    {
        if (isset($payload['callback_query']['id'])) {
            app('telegram.hub')->answerCallbackQuery([
                'callback_query_id' => $payload['callback_query']['id'],
                'text' => 'OK',
                'show_alert' => false
            ], $token);
        }

        if (isset($payload['message']['chat']['id']) && isset($payload['message']['text'])) {
            $chatId = $payload['message']['chat']['id'];
            app('telegram.hub')->sendMessage([
                'chat_id' => $chatId,
                'text' => 'پیام دریافت شد'
            ], $token);
        }
    }
}