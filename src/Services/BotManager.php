<?php

namespace Amirkateb\TelegramHub\Services;

use Illuminate\Support\Facades\DB;
use Amirkateb\TelegramHub\TelegramHub;

class BotManager
{
    protected TelegramHub $hub;

    public function __construct(TelegramHub $hub)
    {
        $this->hub = $hub;
    }

    public function setWebhookAndPersist(string $key, string $token, ?string $name = null, ?string $url = null, ?string $secret = null, array $allowedUpdates = [], bool $enabled = true): array
    {
        if (!$url) {
            $base = rtrim((string) config('telegram_hub.webhook.base_url'), '/');
            $prefix = trim((string) config('telegram_hub.routes.prefix', 'telegram-hub'), '/');
            $url = $base . '/' . $prefix . '/webhook/' . $key;
        }
        if (!$secret) {
            $secret = (string) config('telegram_hub.webhook.secret_token');
        }
        $allowed = $this->normalizeAllowedUpdates($allowedUpdates);

        $res = $this->hub->setWebhook($url, [
            'secret_token' => $secret,
            'allowed_updates' => $allowed,
            'drop_pending_updates' => false
        ], $token);

        if (($res['ok'] ?? false) === true) {
            $exists = DB::table('telegram_bots')->where('key', $key)->first();
            $data = [
                'name' => $name,
                'token' => $token,
                'webhook_url' => $url,
                'secret_token' => $secret,
                'enabled' => $enabled,
                'allowed_updates' => $allowed ? json_encode($allowed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'updated_at' => now()
            ];
            if ($exists) {
                DB::table('telegram_bots')->where('key', $key)->update($data);
                $botId = $exists->id;
            } else {
                $data['key'] = $key;
                $data['created_at'] = now();
                $botId = DB::table('telegram_bots')->insertGetId($data);
            }
            $res['bot_id'] = $botId;
            $res['webhook_url'] = $url;
        }

        return $res;
    }

    public function deleteWebhookAndPersist(string $key, ?string $token = null): array
    {
        if (!$token) {
            $rec = DB::table('telegram_bots')->where('key', $key)->first();
            $token = $rec->token ?? null;
        }
        if (!$token) {
            return ['ok' => false, 'description' => 'Bot token not found'];
        }
        $res = $this->hub->deleteWebhook([], $token);
        if (($res['ok'] ?? false) === true) {
            DB::table('telegram_bots')->where('key', $key)->update([
                'webhook_url' => null,
                'updated_at' => now()
            ]);
        }
        return $res;
    }

    public function info(string $key, ?string $token = null): array
    {
        if (!$token) {
            $rec = DB::table('telegram_bots')->where('key', $key)->first();
            $token = $rec->token ?? null;
        }
        if (!$token) {
            return ['ok' => false, 'description' => 'Bot token not found'];
        }
        return $this->hub->getWebhookInfo($token);
    }

    protected function normalizeAllowedUpdates(array $items): array
    {
        $clean = [];
        foreach ($items as $v) {
            if ($v === null) continue;
            if (is_string($v)) {
                $v = trim($v);
                if ($v === '') continue;
                $clean[] = $v;
            }
        }
        return array_values(array_unique($clean));
    }
}