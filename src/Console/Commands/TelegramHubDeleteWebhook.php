<?php

namespace Amirkateb\TelegramHub\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TelegramHubDeleteWebhook extends Command
{
    protected $signature = 'telegram-hub:webhook:delete {--bot=} {--token=} {--id=}';
    protected $description = 'Delete Telegram bot webhook';

    public function handle(): int
    {
        $tokenOpt = $this->option('token') ? (string) $this->option('token') : null;
        $botOpt = $this->option('bot') ? (string) $this->option('bot') : null;
        $idOpt = $this->option('id') ? (int) $this->option('id') : null;

        $dbRec = null;
        $token = null;
        if ($tokenOpt) {
            $token = $tokenOpt;
        } elseif ($idOpt) {
            $dbRec = DB::table('telegram_bots')->where('id', $idOpt)->first();
            $token = $dbRec->token ?? null;
        } elseif ($botOpt) {
            $dbRec = DB::table('telegram_bots')->where('key', $botOpt)->first();
            $token = $dbRec->token ?? config('telegram_hub.bots.' . $botOpt);
        } else {
            $key = (string) config('telegram_hub.default_bot', 'default');
            $dbRec = DB::table('telegram_bots')->where('key', $key)->first();
            $token = $dbRec->token ?? config('telegram_hub.bots.' . $key);
        }

        if (!$token) { $this->error('Bot token not found'); return 1; }

        $res = app('telegram.hub')->deleteWebhook([], $token);

        if (($res['ok'] ?? false) === true && $dbRec) {
            DB::table('telegram_bots')->where('id', $dbRec->id)->update([
                'webhook_url' => null,
                'updated_at' => now()
            ]);
        }

        $this->line(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return 0;
    }
}