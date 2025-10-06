# 📦 Laravel Telegram Hub
سازنده: **@amirkateb**  
نسخه: 1.0

---

## فهرست مطالب
- [معرفی](#-معرفی)
- [پیش‌نیازها](#-پیشنیازها)
- [نصب](#-نصب)
- [پیکربندی](#-پیکربندی)
- [ساختار فایل‌ها (مانیفست)](#-ساختار-فایلها-مانیفست)
- [دیتابیس](#-دیتابیس)
- [مسیرهای HTTP (Routes)](#-مسیرهای-http-routes)
- [امنیت وبهوک](#-امنیت-وبهوک)
- [مدیریت وبهوک (CLI / API / Service)](#-مدیریت-وبهوک-cli--api--service)
- [دریافت پیام و اتصال منطق پروژه](#-دریافت-پیام-و-اتصال-منطق-پروژه)
- [ارسال پیام‌ها (همه انواع رایج)](#-ارسال-پیامها-همه-انواع-رایج)
- [سرویس‌های تفکیک‌شده (Apis)](#-سرویسهای-تفکیکشده-apis)
- [لاگ‌ها](#-لاگها)
- [عیب‌یابی و نکات عملیاتی](#-عیبیابی-و-نکات-عملیاتی)
- [نقشه راه پیشنهادی (Optional)](#-نقشه-راه-پیشنهادی-optional)
- [پشتیبانی، مجوز](#-پشتیبانی-مجوز)

---

## 🧠 معرفی
**Laravel Telegram Hub** یک پکیج جامع برای ادغام کامل با **Telegram Bot API** در Laravel است. تمرکز پکیج بر **Multi-Bot واقعی**، **وبهوک امن**، **آپلود مدیا از دیسک (multipart)**، **مدیریت از دیتابیس** و **لاگ‌گیری کامل** است.

ویژگی‌ها:
- ست/حذف/استعلام وبهوک با Secret Token
- ارسال انواع پیام: متن، عکس، ویدیو، سند، ویس/آدیو، انیمیشن، ویدئونوت، گروه مدیا، لوکیشن، … + ویرایش/حذف
- آپلود مستقیم فایل از دیسک با `Support\InputFile`
- مدیریت بات‌ها از **config** یا **دیتابیس** (جدول `telegram_bots`)
- لاگ کامل inbound/outbound در جدول `telegram_logs`
- CLI Commands و API Endpoints برای مدیریت
- تنظیم Proxy و Timeout‌ها

---

## ✅ پیش‌نیازها
- PHP 8.2+
- Laravel 10 یا 11
- ext-curl, ext-json
- HTTPS برای وبهوک

---

## 🚀 نصب
```bash
composer require amirkateb/laravel-telegram-hub
php artisan vendor:publish --provider="Amirkateb\TelegramHub\TelegramHubServiceProvider" --tag=config
php artisan vendor:publish --provider="Amirkateb\TelegramHub\TelegramHubServiceProvider" --tag=migrations
php artisan migrate
```

---

## ⚙️ پیکربندی

### .env
```env
TELEGRAM_DEFAULT_BOT=default
TELEGRAM_BOT_TOKEN=123456:ABC
TELEGRAM_WEBHOOK_SECRET=your-long-secret
TELEGRAM_HUB_ROUTE_PREFIX=telegram-hub
TELEGRAM_HUB_ROUTE_MIDDLEWARE=api
TELEGRAM_PROXY_ENABLED=false
TELEGRAM_PROXY_HTTP=
TELEGRAM_PROXY_HTTPS=
TELEGRAM_HTTP_TIMEOUT=15
TELEGRAM_HTTP_CONNECT_TIMEOUT=10
TELEGRAM_HUB_LOG_CHANNEL=stack
TELEGRAM_WEBHOOK_BASE_URL=https://your-domain.com
```

### config/telegram_hub.php (گزیده)
```php
return [
    'default_bot' => env('TELEGRAM_DEFAULT_BOT', 'default'),

    'bots' => [
        'default' => env('TELEGRAM_BOT_TOKEN'),
        // 'sales' => env('TELEGRAM_BOT_TOKEN_SALES'),
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

    'routes' => [
        'prefix' => env('TELEGRAM_HUB_ROUTE_PREFIX', 'telegram-hub'),
        'middleware' => array_filter(array_map('trim', explode(',', (string) env('TELEGRAM_HUB_ROUTE_MIDDLEWARE', 'api')))),
    ],

    'log_channel' => env('TELEGRAM_HUB_LOG_CHANNEL', 'stack'),
];
```

---

## 🗂️ ساختار فایل‌ها (مانیفست)
```
amirkateb/laravel-telegram-hub
├─ config/telegram_hub.php
├─ routes/telegram_hub.php
├─ src/
│  ├─ TelegramHubServiceProvider.php
│  ├─ Http/Controllers/
│  │  ├─ WebhookController.php
│  │  └─ WebhookSetupController.php
│  ├─ Services/
│  │  └─ BotManager.php
│  ├─ Console/Commands/
│  │  ├─ TelegramHubSetWebhook.php
│  │  ├─ TelegramHubDeleteWebhook.php
│  │  ├─ TelegramHubWebhookInfo.php
│  │  ├─ TelegramHubSend.php
│  │  ├─ TelegramHubSendTest.php
│  │  ├─ TelegramHubBotUpsert.php
│  │  ├─ TelegramHubBotDeleteWebhook.php
│  │  └─ TelegramHubBotInfo.php
│  ├─ Apis/
│  │  ├─ MessageApi.php
│  │  ├─ MediaApi.php
│  │  └─ ChatAdminApi.php
│  └─ Support/
│     ├─ Http.php
│     └─ InputFile.php
└─ database/migrations/
   ├─ xxxx_xx_xx_xxxxxx_create_telegram_bots_table.php
   └─ xxxx_xx_xx_xxxxxx_create_telegram_logs_table.php
```

---

## 🧩 دیتابیس

### جدول `telegram_bots`
| ستون | توضیح |
|------|------|
| key | کلید یکتای بات (مثال: sales) |
| name | نام نمایشی |
| token | توکن BotFather |
| webhook_url | آدرس وبهوک فعلی |
| secret_token | توکن امنیتی وبهوک |
| enabled | فعال/غیرفعال |
| allowed_updates | JSON از نوع آپدیت‌های مجاز |
| timestamps | زمان‌ها |

نمونه درج:
```php
DB::table('telegram_bots')->insert([
  'key' => 'sales',
  'name' => 'ربات فروش',
  'token' => '123456789:ABC_DEF',
  'webhook_url' => 'https://your-domain.com/telegram-hub/webhook/sales',
  'secret_token' => 'sales-secret',
  'enabled' => true,
  'allowed_updates' => json_encode(['message','callback_query']),
  'created_at' => now(),
  'updated_at' => now(),
]);
```

### جدول `telegram_logs`
برای ثبت inbound/outbound با فیلدهای: `direction, bot_key, bot_id, chat_id, message_id, method, status_code, ok, error_code, error_description, payload, response, timestamps`

---

## 🔗 مسیرهای HTTP (Routes)

| متد | مسیر | توضیح |
|-----|------|------|
| POST | `/{prefix}/webhook/{bot}` | دریافت آپدیت‌های بات مشخص |
| POST | `/{prefix}/webhook` | دریافت آپدیت‌های بات پیش‌فرض |
| POST | `/{prefix}/set-webhook/{bot}` | ست وبهوک + امکان ذخیره از طریق BotManager در DB |
| DELETE | `/{prefix}/delete-webhook/{bot}` | حذف وبهوک + به‌روزرسانی DB |
| GET | `/{prefix}/webhook-info/{bot}` | دریافت وضعیت وبهوک |

> مقادیر `prefix` و `middleware` از کانفیگ خوانده می‌شود.  
> امنیت: بررسی `X-Telegram-Bot-Api-Secret-Token` در `WebhookController` انجام می‌شود (از DB یا config).

---

## 🛡️ امنیت وبهوک
- `TELEGRAM_WEBHOOK_SECRET` را تنظیم و هنگام `setWebhook` ارسال کنید.
- وبهوک فقط از HTTPS خوانده شود.
- برای روت‌های مدیریتی (set/delete/info) می‌توانید Middleware احراز هویت اضافه کنید.
- امکان غیرفعال‌سازی بات از طریق فیلد `enabled` در DB وجود دارد.

---

## 📡 مدیریت وبهوک (CLI / API / Service)

### 1) CLI ساده (بدون Persist)
```bash
php artisan telegram-hub:webhook:set --url="https://your-domain.com/telegram-hub/webhook/default" --bot=default --secret="$TELEGRAM_WEBHOOK_SECRET" --allowed-updates=message,callback_query
php artisan telegram-hub:webhook:info --bot=default
php artisan telegram-hub:webhook:delete --bot=default
```

### 2) BotManager (ست و ذخیره در DB)
```php
use Amirkateb\TelegramHub\Services\BotManager;

$res = app(BotManager::class)->setWebhookAndPersist(
  'sales',
  '123456789:ABC_DEF',
  'ربات فروش',
  null,
  'sales-secret',
  ['message','callback_query'],
  true
);
```

### 3) CLI با Persist
```bash
php artisan telegram-hub:bot:upsert --key=sales --token=123456789:ABC_DEF --name="ربات فروش" --secret="sales-secret" --allowed-updates=message,callback_query --enabled=1
php artisan telegram-hub:bot:info --key=sales
php artisan telegram-hub:bot:delete-webhook --key=sales
```

### 4) API داخلی
```http
POST   /{prefix}/set-webhook/{bot}      (body: { token?, name?, url?, secret?, allowed_updates?, enabled? })
DELETE /{prefix}/delete-webhook/{bot}
GET    /{prefix}/webhook-info/{bot}
```

---

## 💬 دریافت پیام و اتصال منطق پروژه

### روش توصیه‌شده: Middleware روی روت وبهوک پکیج
1) بسازید: `app/Http/Middleware/TelegramHubInbound.php`
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TelegramHubInbound
{
    public function handle(Request $request, Closure $next)
    {
        $update = $request->all();

        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'] ?? null;
            $text   = $update['message']['text'] ?? '';

            if ($text === '/start') {
                app('telegram.hub')->sendMessage([
                    'chat_id' => $chatId,
                    'text' => '👋 خوش آمدید!'
                ]);
            }
        }

        if (isset($update['callback_query']['id'])) {
            app('telegram.hub')->answerCallbackQuery([
                'callback_query_id' => $update['callback_query']['id'],
                'text' => 'OK',
                'show_alert' => false
            ]);
        }

        return $next($request);
    }
}
```
2) ثبت در Kernel:
```php
protected $routeMiddleware = [
    'telegram.hub' => \App\Http\Middleware\TelegramHubInbound::class,
];
```
3) فعال‌سازی در `.env`:
```env
TELEGRAM_HUB_ROUTE_MIDDLEWARE=api,telegram.hub
```

> جایگزین: می‌توانید روت/کنترلر اختصاصی خودتان را بسازید و وبهوک را روی مسیر خودتان ست کنید.

---

## ✉️ ارسال پیام‌ها (همه انواع رایج)

### سرویس مرکزی
```php
$hub = app('telegram.hub');
$hub->sendMessage(['chat_id' => 123456789, 'text' => 'سلام دنیا']);
```

### انتخاب بات (multi-bot)
```php
$token = config('telegram_hub.bots.sales'); // یا از DB بگیرید
app('telegram.hub')->sendMessage(['chat_id'=>123456789, 'text'=>'از بات فروش'], $token);
```

### آپلود از دیسک (multipart)
```php
use Amirkateb\TelegramHub\Support\InputFile;

app('telegram.hub')->sendPhoto([
  'chat_id' => 123456789,
  'photo' => InputFile::path(storage_path('app/public/pic.jpg'), 'pic.jpg', 'image/jpeg'),
  'caption' => 'ارسال از دیسک'
]);
```

### آلبوم چندتایی
```php
use Amirkateb\TelegramHub\Support\InputFile;

$media = [
  ['type'=>'photo','media'=>InputFile::path(storage_path('app/p1.jpg'),'p1.jpg','image/jpeg'),'caption'=>'1'],
  ['type'=>'photo','media'=>InputFile::path(storage_path('app/p2.jpg'),'p2.jpg','image/jpeg'),'caption'=>'2'],
];

app('telegram.hub')->sendMediaGroup([
  'chat_id' => 123456789,
  'media' => $media
]);
```

### ویرایش و حذف
```php
app('telegram.hub')->editMessageText([
  'chat_id' => 123456789,
  'message_id' => 42,
  'text' => 'ویرایش شد ✅'
]);

app('telegram.hub')->deleteMessage([
  'chat_id' => 123456789,
  'message_id' => 42
]);
```

---

## 🧰 سرویس‌های تفکیک‌شده (Apis)
```php
use Amirkateb\TelegramHub\Apis\MessageApi;
use Amirkateb\TelegramHub\Apis\MediaApi;
use Amirkateb\TelegramHub\Apis\ChatAdminApi;

public function send(MessageApi $msg, MediaApi $media, ChatAdminApi $admin)
{
    $msg->sendMessage(['chat_id'=>123,'text'=>'hi']);
    $media->sendPhoto(['chat_id'=>123,'photo'=>'https://placehold.co/400']);
    $admin->pinChatMessage(['chat_id'=>123,'message_id'=>10]);
}
```

---

## 🔍 لاگ‌ها
تمام درخواست‌های خروجی و ورودی وبهوک با payload/response در `telegram_logs` ذخیره می‌شود.

---

## 🛠️ عیب‌یابی و نکات عملیاتی
- وبهوک کار نمی‌کند: HTTPS، آدرس، `secret_token`، لاگ وب‌سرور و `telegram_logs` را بررسی کنید.
- خطای 429: ارسال‌ها را صف‌بندی کنید و backoff در نظر بگیرید.
- فایل ارسال نمی‌شود: دسترسی فایل، MIME-type و اندازه را بررسی کنید.
- چند‌بات: مطمئن شوید توکن صحیح را به‌عنوان پارامتر دوم متدها می‌دهید یا در DB/config درست ست شده.

---

## 🧭 نقشه راه پیشنهادی (Optional)
- Router پیام‌ها (match روی /start، regex و callback_data)
- Session/Context (Redis/DB) برای جریان‌های چندمرحله‌ای
- Notification Channel لاراول
- Dashboard ساده برای مشاهده‌ی لاگ‌ها و مدیریت بات‌ها
- کش `file_id` برای ارسال مجدد بدون آپلود

---

## 👤 پشتیبانی، مجوز
- ایمیل: amveks43@gmail.com
- گیت‌هاب: https://github.com/amirkateb

MIT License
