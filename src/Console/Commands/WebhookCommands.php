<?php

namespace Amirkateb\TelegramHub\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TelegramHubSetWebhook extends Command
{
    protected $signature = 'telegram-hub:webhook:set {--bot=} {--token=} {--id=} {--url=} {--allowed-updates=} {--drop-pending-updates=} {--secret-token=}';
    protected $description = 'Set Telegram webhook for a bot';

    public function handle(): int
    {
        $token = $this->resolveToken($this->option('bot'), $this->option('token'), $this->option('id'));
        if (!$token) {
            $this->error('Token not resolved');
            return 1;
        }

        $botKey = $this->option('bot');
        $url = $this->option('url') ?: $this->buildWebhookUrl($botKey);
        $allowedUpdates = $this->option('allowed-updates') ? array_values(array_filter(array_map('trim', explode(',', (string) $this->option('allowed-updates'))))) : [];
        $dropPending = $this->option('drop-pending-updates') !== null ? filter_var($this->option('drop-pending-updates'), FILTER_VALIDATE_BOOLEAN) : false;
        $secret = $this->option('secret-token') ?: config('telegram_hub.webhook.secret_token');

        $res = app('telegram.hub')->setWebhook($url, [
            'allowed_updates' => $allowedUpdates,
            'drop_pending_updates' => $dropPending,
            'secret_token' => $secret
        ], $token);

        $this->line(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return 0;
    }

    protected function resolveToken(?string $botKey, ?string $token, ?string $id): ?string
    {
        if ($token) {
            return $token;
        }
        if ($botKey) {
            $cfg = config('telegram_hub.bots.' . $botKey);
            if ($cfg) {
                return $cfg;
            }
        }
        if ($id && Schema::hasTable('telegram_bots')) {
            $rec = DB::table('telegram_bots')->where('id', $id)->first();
            if ($rec && isset($rec->token)) {
                return $rec->token;
            }
        }
        if ($botKey && Schema::hasTable('telegram_bots')) {
            $rec = DB::table('telegram_bots')->where('key', $botKey)->orWhere('name', $botKey)->first();
            if ($rec && isset($rec->token)) {
                return $rec->token;
            }
        }
        $defaultKey = (string) config('telegram_hub.default_bot', 'default');
        $cfg = config('telegram_hub.bots.' . $defaultKey);
        return $cfg ?: null;
    }

    protected function buildWebhookUrl(?string $botKey): string
    {
        $base = rtrim((string) config('telegram_hub.webhook.base_url'), '/');
        if ($botKey) {
            $path = parse_url(route('telegram_hub.webhook.handle', ['bot' => $botKey], false), PHP_URL_PATH);
        } else {
            $path = parse_url(route('telegram_hub.webhook.handle_default', [], false), PHP_URL_PATH);
        }
        return $base . $path;
    }
}

class TelegramHubDeleteWebhook extends Command
{
    protected $signature = 'telegram-hub:webhook:delete {--bot=} {--token=} {--id=} {--drop-pending-updates=}';
    protected $description = 'Delete Telegram webhook for a bot';

    public function handle(): int
    {
        $token = $this->resolveToken($this->option('bot'), $this->option('token'), $this->option('id'));
        if (!$token) {
            $this->error('Token not resolved');
            return 1;
        }
        $dropPending = $this->option('drop-pending-updates') !== null ? filter_var($this->option('drop-pending-updates'), FILTER_VALIDATE_BOOLEAN) : false;
        $res = app('telegram.hub')->deleteWebhook(['drop_pending_updates' => $dropPending], $token);
        $this->line(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return 0;
    }

    protected function resolveToken(?string $botKey, ?string $token, ?string $id): ?string
    {
        if ($token) {
            return $token;
        }
        if ($botKey) {
            $cfg = config('telegram_hub.bots.' . $botKey);
            if ($cfg) {
                return $cfg;
            }
        }
        if ($id && Schema::hasTable('telegram_bots')) {
            $rec = DB::table('telegram_bots')->where('id', $id)->first();
            if ($rec && isset($rec->token)) {
                return $rec->token;
            }
        }
        if ($botKey && Schema::hasTable('telegram_bots')) {
            $rec = DB::table('telegram_bots')->where('key', $botKey)->orWhere('name', $botKey)->first();
            if ($rec && isset($rec->token)) {
                return $rec->token;
            }
        }
        $defaultKey = (string) config('telegram_hub.default_bot', 'default');
        $cfg = config('telegram_hub.bots.' . $defaultKey);
        return $cfg ?: null;
    }
}

class TelegramHubWebhookInfo extends Command
{
    protected $signature = 'telegram-hub:webhook:info {--bot=} {--token=} {--id=}';
    protected $description = 'Get Telegram webhook info for a bot';

    public function handle(): int
    {
        $token = $this->resolveToken($this->option('bot'), $this->option('token'), $this->option('id'));
        if (!$token) {
            $this->error('Token not resolved');
            return 1;
        }
        $res = app('telegram.hub')->getWebhookInfo($token);
        $this->line(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return 0;
    }

    protected function resolveToken(?string $botKey, ?string $token, ?string $id): ?string
    {
        if ($token) {
            return $token;
        }
        if ($botKey) {
            $cfg = config('telegram_hub.bots.' . $botKey);
            if ($cfg) {
                return $cfg;
            }
        }
        if ($id && Schema::hasTable('telegram_bots')) {
            $rec = DB::table('telegram_bots')->where('id', $id)->first();
            if ($rec && isset($rec->token)) {
                return $rec->token;
            }
        }
        if ($botKey && Schema::hasTable('telegram_bots')) {
            $rec = DB::table('telegram_bots')->where('key', $botKey)->orWhere('name', $botKey)->first();
            if ($rec && isset($rec->token)) {
                return $rec->token;
            }
        }
        $defaultKey = (string) config('telegram_hub.default_bot', 'default');
        $cfg = config('telegram_hub.bots.' . $defaultKey);
        return $cfg ?: null;
    }
}