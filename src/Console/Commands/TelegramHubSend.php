<?php

namespace Amirkateb\TelegramHub\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Amirkateb\TelegramHub\Support\Payload;

class TelegramHubSend extends Command
{
    protected $signature = 'telegram-hub:send
        {--method=sendMessage}
        {--data=}
        {--kv=*}
        {--bot=}
        {--token=}
        {--id=}
    ';

    protected $description = 'Send any Telegram Bot API call via Telegram Hub (no routes required)';

    public function handle(): int
    {
        $method = $this->option('method') ?: 'sendMessage';
        $payload = Payload::fromOptions($this->option('kv') ?? [], $this->option('data'));

        $token = $this->resolveToken($this->option('bot'), $this->option('token'), $this->option('id'));
        if (!$token) {
            $this->error('Token not resolved. Use --bot=key, --token=xxx, or --id=botId (requires telegram_bots table).');
            return 1;
        }

        $res = app('telegram.hub')->call($method, $payload, $token);
        $this->line(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return 0;
    }

    protected function resolveToken(?string $botKey, ?string $token, ?string $id): ?string
    {
        if ($token) return $token;

        if ($botKey) {
            $cfg = config('telegram_hub.bots.' . $botKey);
            if ($cfg) return $cfg;
        }

        if ($id && Schema::hasTable('telegram_bots')) {
            $rec = DB::table('telegram_bots')->where('id', $id)->first();
            if ($rec && isset($rec->token)) return $rec->token;
        }

        if ($botKey && Schema::hasTable('telegram_bots')) {
            $rec = DB::table('telegram_bots')->where('key', $botKey)->orWhere('name', $botKey)->first();
            if ($rec && isset($rec->token)) return $rec->token;
        }

        $defaultKey = (string) config('telegram_hub.default_bot', 'default');
        $cfg = config('telegram_hub.bots.' . $defaultKey);
        return $cfg ?: null;
    }
}