<?php

namespace Amirkateb\TelegramHub;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Amirkateb\TelegramHub\Console\Commands\TelegramHubSetWebhook;
use Amirkateb\TelegramHub\Console\Commands\TelegramHubDeleteWebhook;
use Amirkateb\TelegramHub\Console\Commands\TelegramHubWebhookInfo;
use Amirkateb\TelegramHub\Console\Commands\TelegramHubSendTest;
use Amirkateb\TelegramHub\Console\Commands\TelegramHubSend;
use Amirkateb\TelegramHub\Console\Commands\TelegramHubBotUpsert;
use Amirkateb\TelegramHub\Console\Commands\TelegramHubBotDeleteWebhook;
use Amirkateb\TelegramHub\Console\Commands\TelegramHubBotInfo;
use Amirkateb\TelegramHub\Apis\MessageApi;
use Amirkateb\TelegramHub\Apis\MediaApi;
use Amirkateb\TelegramHub\Apis\ChatAdminApi;
use Amirkateb\TelegramHub\Support\Http;
use Amirkateb\TelegramHub\Services\BotManager;

class TelegramHubServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/telegram_hub.php', 'telegram_hub');
        $this->app->singleton('telegram.hub', function ($app) { return new TelegramHub($app['config']); });
        $this->app->singleton(MessageApi::class, fn($app) => new MessageApi($app->make('telegram.hub')));
        $this->app->singleton(MediaApi::class, fn($app) => new MediaApi($app->make('telegram.hub')));
        $this->app->singleton(ChatAdminApi::class, fn($app) => new ChatAdminApi($app->make('telegram.hub')));
        $this->app->singleton(BotManager::class, fn($app) => new BotManager($app->make('telegram.hub')));
    }

    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/telegram_hub.php' => config_path('telegram_hub.php')], 'config');
        $this->publishes([__DIR__ . '/../database/migrations/' => database_path('migrations')], 'migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/telegram_hub.php');
        if ($this->app->runningInConsole()) {
            $this->commands([
                TelegramHubSetWebhook::class,
                TelegramHubDeleteWebhook::class,
                TelegramHubWebhookInfo::class,
                TelegramHubSendTest::class,
                TelegramHubSend::class,
                TelegramHubBotUpsert::class,
                TelegramHubBotDeleteWebhook::class,
                TelegramHubBotInfo::class,
            ]);
        }
    }
}

class TelegramHub
{
    protected ConfigRepository $config;

    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
    }

    protected function token(?string $token = null): string
    {
        if ($token) return $token;
        $bots = (array) $this->config->get('telegram_hub.bots', []);
        $defaultKey = (string) $this->config->get('telegram_hub.default_bot', 'default');
        return (string) ($bots[$defaultKey] ?? '');
    }

    protected function client(string $token): Client
    {
        $proxyEnabled = (bool) $this->config->get('telegram_hub.proxy.enabled', false);
        $proxy = $proxyEnabled ? array_filter([
            'http' => $this->config->get('telegram_hub.proxy.http'),
            'https' => $this->config->get('telegram_hub.proxy.https'),
        ]) : null;

        return new Client([
            'base_uri' => 'https://api.telegram.org/bot' . $token . '/',
            'timeout' => (int) $this->config->get('telegram_hub.request.timeout', 15),
            'connect_timeout' => (int) $this->config->get('telegram_hub.request.connect_timeout', 10),
            'proxy' => $proxy,
        ]);
    }

    protected function logChannel(): string
    {
        return (string) $this->config->get('telegram_hub.log_channel', 'stack');
    }

    public function call(string $method, array $params = [], ?string $token = null): array
    {
        $token = $this->token($token);
        $client = $this->client($token);
        $options = Http::buildOptions($params);
        Log::channel($this->logChannel())->info('telegram_hub.request', ['method' => $method]);

        try {
            $res = $client->post($method, $options);
            $json = json_decode((string) $res->getBody(), true) ?: [];
            Log::channel($this->logChannel())->info('telegram_hub.response', ['method' => $method, 'body' => $json]);

            try {
                DB::table('telegram_logs')->insert([
                    'direction' => 'outbound',
                    'bot_key' => null,
                    'bot_id' => null,
                    'chat_id' => $params['chat_id'] ?? null,
                    'message_id' => $json['result']['message_id'] ?? null,
                    'method' => $method,
                    'status_code' => $res->getStatusCode(),
                    'ok' => $json['ok'] ?? false,
                    'error_code' => $json['error_code'] ?? null,
                    'error_description' => $json['description'] ?? null,
                    'payload' => json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'response' => json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {}

            return $json;
        } catch (\Throwable $e) {
            Log::channel($this->logChannel())->error('telegram_hub.exception', ['method' => $method, 'error' => $e->getMessage()]);
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    public function sendMessage(array $params, ?string $token = null): array { return $this->call('sendMessage', $params, $token); }
    public function sendPhoto(array $params, ?string $token = null): array { return $this->call('sendPhoto', $params, $token); }
    public function sendDocument(array $params, ?string $token = null): array { return $this->call('sendDocument', $params, $token); }
    public function sendVideo(array $params, ?string $token = null): array { return $this->call('sendVideo', $params, $token); }
    public function sendAudio(array $params, ?string $token = null): array { return $this->call('sendAudio', $params, $token); }
    public function sendVoice(array $params, ?string $token = null): array { return $this->call('sendVoice', $params, $token); }
    public function sendAnimation(array $params, ?string $token = null): array { return $this->call('sendAnimation', $params, $token); }
    public function sendVideoNote(array $params, ?string $token = null): array { return $this->call('sendVideoNote', $params, $token); }
    public function sendMediaGroup(array $params, ?string $token = null): array { return $this->call('sendMediaGroup', $params, $token); }
    public function editMessageMedia(array $params, ?string $token = null): array { return $this->call('editMessageMedia', $params, $token); }
    public function editMessageText(array $params, ?string $token = null): array { return $this->call('editMessageText', $params, $token); }
    public function editMessageCaption(array $params, ?string $token = null): array { return $this->call('editMessageCaption', $params, $token); }
    public function editMessageReplyMarkup(array $params, ?string $token = null): array { return $this->call('editMessageReplyMarkup', $params, $token); }
    public function deleteMessage(array $params, ?string $token = null): array { return $this->call('deleteMessage', $params, $token); }
    public function answerCallbackQuery(array $params, ?string $token = null): array { return $this->call('answerCallbackQuery', $params, $token); }
    public function setWebhook(string $url, array $options = [], ?string $token = null): array
    {
        $payload = array_merge([
            'url' => $url,
            'secret_token' => $this->config->get('telegram_hub.webhook.secret_token'),
            'drop_pending_updates' => false,
            'allowed_updates' => [],
        ], $options);
        return $this->call('setWebhook', $payload, $token);
    }
    public function deleteWebhook(array $options = [], ?string $token = null): array { return $this->call('deleteWebhook', $options, $token); }
    public function getWebhookInfo(?string $token = null): array { return $this->call('getWebhookInfo', [], $token); }
}