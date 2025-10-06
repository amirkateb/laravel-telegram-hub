<?php

namespace Amirkateb\TelegramHub\Console\Commands;

use Illuminate\Console\Command;

class TelegramHubSendTest extends Command
{
    protected $signature = 'telegram-hub:send:test {--chat=} {--text=} {--bot=} {--token=}';
    protected $description = 'Send a test message via Telegram Hub';

    public function handle(): int
    {
        $chat = $this->option('chat');
        $text = $this->option('text') ?: 'Hello from Laravel Telegram Hub';
        $botKey = $this->option('bot');
        $token = $this->option('token');

        if (!$chat) {
            $this->error('Missing --chat');
            return 1;
        }

        $explicitToken = $token ?: ($botKey ? config('telegram_hub.bots.' . $botKey) : null);
        $res = app('telegram.hub')->sendMessage([
            'chat_id' => $chat,
            'text' => $text
        ], $explicitToken);

        $this->line(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return 0;
    }
}