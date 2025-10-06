<?php

return [
    'default_bot' => env('TELEGRAM_DEFAULT_BOT', 'default'),
    'bots' => [
        'default' => env('TELEGRAM_BOT_TOKEN'),
    ],
    'proxy' => [
        'enabled' => (bool) env('TELEGRAM_PROXY_ENABLED', false),
        'http' => env('TELEGRAM_PROXY_HTTP'),
        'https' => env('TELEGRAM_PROXY_HTTPS'),
    ],
    'request' => [
        'timeout' => (int) env('TELEGRAM_HTTP_TIMEOUT', 15),
        'connect_timeout' => (int) env('TELEGRAM_HTTP_CONNECT_TIMEOUT', 10),
    ],
    'webhook' => [
        'base_url' => env('TELEGRAM_WEBHOOK_BASE_URL'),
        'secret_token' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],
    'log_channel' => env('TELEGRAM_HUB_LOG_CHANNEL', 'stack'),
];