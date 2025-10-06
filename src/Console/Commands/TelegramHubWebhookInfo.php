<?php

namespace Amirkateb\TelegramHub\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TelegramHubWebhookInfo extends Command
{
    protected $signature = 'telegram-hub:webhook:info {--bot=} {--token=} {--id=}';
    protected $description = 'Get Telegram bot webhook info';

    public function handle(): int
    {
        $tokenOpt = $this->option('token') ? (string) $this->option('token') : null;
        $botOpt = $this->option('bot') ? (string) $this->option('bot') : null;
        $idOpt = $this->option('id') ? (int) $this->option('id') : null;

        $token = null;
        if ($tokenOpt) {
            $token = $tokenOpt;
        } elseif ($idOpt) {
            $rec = DB::table('telegram_bots')->where('id', $idOpt)->first();
            $token = $rec->token ?? null;
        } elseif ($botOpt) {
            $rec = DB::table('telegram_bots')->where('key', $botOpt)->first();
            $token = $rec->token ?? config('telegram_hub.bots.' . $botOpt);
        } else {
            $key = (string) config('telegram_hub.default_bot', 'default');
            $rec = DB::table('telegram_bots')->where('key', $key)->first();
            $token = $rec->token ?? config('telegram_hub.bots.' . $key);
        }

        if (!$token) { $this->error('Bot token not found'); return 1; }

        $res = app('telegram.hub')->getWebhookInfo($token);
        $this->line(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return 0;
    }
}