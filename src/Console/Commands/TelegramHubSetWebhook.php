<?php

namespace Amirkateb\TelegramHub\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TelegramHubSetWebhook extends Command
{
    protected $signature = 'telegram-hub:webhook:set
        {--url=}
        {--bot=}
        {--token=}
        {--id=}
        {--secret=}
        {--allowed-updates=}
        {--drop-pending-updates=}
    ';

    protected $description = 'Set Telegram bot webhook';

    public function handle(): int
    {
        $url = (string) $this->option('url');
        if ($url === '') { $this->error('Missing --url'); return 1; }

        $tokenOpt = $this->option('token') ? (string) $this->option('token') : null;
        $botOpt = $this->option('bot') ? (string) $this->option('bot') : null;
        $idOpt = $this->option('id') ? (int) $this->option('id') : null;

        $secret = $this->option('secret') ? (string) $this->option('secret') : null;
        $allowed = [];
        if ($this->option('allowed-updates')) {
            $allowed = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('allowed-updates')))));
        }
        $drop = null;
        if (!is_null($this->option('drop-pending-updates'))) {
            $v = strtolower((string) $this->option('drop-pending-updates'));
            $drop = in_array($v, ['1','true','yes','on'], true);
        }

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

        if (!$secret) { $secret = $dbRec->secret_token ?? (string) config('telegram_hub.webhook.secret_token'); }
        if (!$allowed && $dbRec && $dbRec->allowed_updates) {
            $allowed = (array) json_decode($dbRec->allowed_updates, true);
        }

        $payload = ['secret_token' => $secret];
        if ($allowed) $payload['allowed_updates'] = $allowed;
        if (!is_null($drop)) $payload['drop_pending_updates'] = $drop;

        $res = app('telegram.hub')->setWebhook($url, $payload, $token);

        if (($res['ok'] ?? false) === true && $dbRec) {
            DB::table('telegram_bots')->where('id', $dbRec->id)->update([
                'webhook_url' => $url,
                'secret_token' => $secret,
                'allowed_updates' => $allowed ? json_encode($allowed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'updated_at' => now()
            ]);
        }

        $this->line(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return 0;
    }
}