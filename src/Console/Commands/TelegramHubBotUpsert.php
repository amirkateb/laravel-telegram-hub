<?php

namespace Amirkateb\TelegramHub\Console\Commands;

use Illuminate\Console\Command;
use Amirkateb\TelegramHub\Services\BotManager;

class TelegramHubBotUpsert extends Command
{
    protected $signature = 'telegram-hub:bot:upsert
        {--key=}
        {--token=}
        {--name=}
        {--url=}
        {--secret=}
        {--allowed-updates=}
        {--enabled=1}
    ';

    protected $description = 'Set webhook for a bot and persist it in DB';

    public function handle(): int
    {
        $key = (string) $this->option('key');
        $token = (string) $this->option('token');
        if ($key === '' || $token === '') {
            $this->error('Missing --key or --token');
            return 1;
        }

        $name = $this->option('name');
        $url = $this->option('url');
        $secret = $this->option('secret');

        $enabledOpt = $this->option('enabled');
        $enabled = !in_array(strtolower((string) $enabledOpt), ['0','false','no','off'], true);

        $allowed = [];
        if ($this->option('allowed-updates')) {
            $allowed = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('allowed-updates')))));
        }

        $res = app(BotManager::class)->setWebhookAndPersist($key, $token, $name, $url, $secret, $allowed, $enabled);
        $this->line(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return 0;
    }
}