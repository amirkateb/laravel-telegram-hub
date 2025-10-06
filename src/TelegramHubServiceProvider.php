<?php

namespace Amirkateb\TelegramHub;

use GuzzleHttp\Client;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class TelegramHubServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/telegram_hub.php', 'telegram_hub');

        $this->app->singleton('telegram.hub', function ($app) {
            return new TelegramHub($app['config']);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/telegram_hub.php' => config_path('telegram_hub.php'),
        ], 'config');
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
        if ($token) {
            return $token;
        }
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

        $timeout = (int) $this->config->get('telegram_hub.request.timeout', 15);
        $connectTimeout = (int) $this->config->get('telegram_hub.request.connect_timeout', 10);

        return new Client([
            'base_uri' => 'https://api.telegram.org/bot' . $token . '/',
            'timeout' => $timeout,
            'connect_timeout' => $connectTimeout,
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
        Log::channel($this->logChannel())->info('telegram_hub.request', ['method' => $method, 'params' => $params]);
        $res = $client->post($method, ['form_params' => $params]);
        $body = (string) $res->getBody();
        Log::channel($this->logChannel())->info('telegram_hub.response', ['method' => $method, 'body' => $body]);
        $json = json_decode($body, true) ?: [];
        return $json;
    }

    public function sendMessage(array $params, ?string $token = null): array
    {
        return $this->call('sendMessage', $params, $token);
    }

    public function sendPhoto(array $params, ?string $token = null): array
    {
        return $this->call('sendPhoto', $params, $token);
    }

    public function sendDocument(array $params, ?string $token = null): array
    {
        return $this->call('sendDocument', $params, $token);
    }

    public function sendLocation(array $params, ?string $token = null): array
    {
        return $this->call('sendLocation', $params, $token);
    }

    public function editMessageText(array $params, ?string $token = null): array
    {
        return $this->call('editMessageText', $params, $token);
    }

    public function deleteMessage(array $params, ?string $token = null): array
    {
        return $this->call('deleteMessage', $params, $token);
    }

    public function answerCallbackQuery(array $params, ?string $token = null): array
    {
        return $this->call('answerCallbackQuery', $params, $token);
    }

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

    public function getWebhookInfo(?string $token = null): array
    {
        return $this->call('getWebhookInfo', [], $token);
    }
}