# 📦 Laravel Telegram Hub
ساخته‌شده توسط **@amirkateb**  
نسخه: 1.0

---

## 🧠 معرفی

**Laravel Telegram Hub** یک پکیج جامع برای کار با **Telegram Bot API** در لاراول است که این قابلیت‌ها را فراهم می‌کند:
- پشتیبانی از چند بات (multi-bot) با کانفیگ، دیتابیس یا توکن لحظه‌ای
- وبهوک امن با Secret Token، مسیر قابل‌تنظیم و لاگ کامل رویدادها
- ارسال همهٔ انواع پیام (متن، عکس، ویدیو، ویس، سند، آلبوم، لوکیشن، ونیو، کانتکت…)، ویرایش/حذف پیام و پاسخ به callback_query
- آپلود مستقیم فایل از دیسک (multipart) با هلسپر `InputFile`
- کلاس‌های تفکیک‌شده برای ارسال (Message/Media/Admin) و فَساد `TelegramHub`
- Artisan Commands برای مدیریت وبهوک و ارسال تستی/دلخواه
- API داخلی برای ست/حذف/استعلام وبهوک
- تنظیم پراکسی، تایم‌اوت‌ها و لاگ‌گیری دیتابیسی

---

## ✅ پیش‌نیازها

- PHP ^8.2  
- Laravel ^10 | ^11  
- اکستنشن‌های cURL و JSON فعال

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
TELEGRAM_WEBHOOK_SECRET=my-long-secret
TELEGRAM_HUB_ROUTE_PREFIX=telegram-hub
TELEGRAM_HUB_ROUTE_MIDDLEWARE=api
TELEGRAM_PROXY_ENABLED=false
TELEGRAM_PROXY_HTTP=
TELEGRAM_PROXY_HTTPS=
TELEGRAM_HTTP_TIMEOUT=15
TELEGRAM_HTTP_CONNECT_TIMEOUT=10
TELEGRAM_HUB_LOG_CHANNEL=stack
```

### config/telegram_hub.php (نمونه تعاریف چند بات)
```php
'bots' => [
  'default' => env('TELEGRAM_BOT_TOKEN'),
  'sales'   => env('TELEGRAM_BOT_TOKEN_SALES'),
  'support' => env('TELEGRAM_BOT_TOKEN_SUPPORT'),
],
```

> نکته: بات‌ها را می‌توان در دیتابیس نیز مدیریت کرد (پایین توضیح داده شده).

---

## 🧩 دیتابیس

### جدول `telegram_bots`
ساختار پیشنهادی (مایگریشن آماده‌ی پکیج):

| ستون | نوع | توضیح |
|------|-----|------|
| id | bigint | کلید |
| key | string(unique) | کلید بات (مثال: sales) |
| name | string | نام نمایشی |
| token | string | توکن BotFather |
| webhook_url | string | آدرس وبهوک فعلی |
| secret_token | string | رمز امنیتی وبهوک |
| enabled | boolean(index) | فعال/غیر فعال |
| allowed_updates | json | انواع آپدیت‌های مجاز |
| timestamps | — | زمان‌ها |

نمونه درج رکورد:
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
برای لاگ inbound/outbound:

| ستون | توضیح |
|------|------|
| direction | inbound یا outbound |
| bot_key, bot_id | مشخصات بات |
| chat_id, message_id | شناسه‌های تلگرام |
| method | متد Bot API یا "webhook" |
| status_code, ok, error_code, error_description | وضعیت |
| payload, response | JSON ورودی/خروجی |
| created_at, updated_at | زمان‌ها |

---

## 🔗 مسیرها (Routes)

پکیج به‌صورت خودکار این مسیرها را بارگذاری می‌کند (مگر اینکه فایل را حذف کنید):

- `POST /{PREFIX}/webhook/{bot}` → دریافت آپدیت‌ها برای یک بات مشخص
- `POST /{PREFIX}/webhook` → دریافت آپدیت‌ها برای بات پیش‌فرض
- `POST /{PREFIX}/set-webhook/{bot}` → ست وبهوک
- `DELETE /{PREFIX}/delete-webhook/{bot}` → حذف وبهوک
- `GET /{PREFIX}/webhook-info/{bot}` → اطلاعات وبهوک

مقادیر `PREFIX` و میدلورها در کانفیگ قابل‌تنظیم‌اند:
```env
TELEGRAM_HUB_ROUTE_PREFIX=telegram-hub
TELEGRAM_HUB_ROUTE_MIDDLEWARE=api
```

---

## 🛡️ امنیت وبهوک

- هنگام `setWebhook` مقدار `secret_token` را تنظیم کنید
- در وبهوک هدر `X-Telegram-Bot-Api-Secret-Token` بررسی می‌شود
- همیشه از HTTPS استفاده کنید
- می‌توانید روی مسیرهای مدیریتی (set/delete/info) میدلور احراز هویت اضافه کنید

---

## 📡 مدیریت وبهوک

### Artisan
```bash
php artisan telegram-hub:webhook:set --url="https://your-domain.com/telegram-hub/webhook/default" --bot=default --secret-token="$TELEGRAM_WEBHOOK_SECRET"
php artisan telegram-hub:webhook:info --bot=default
php artisan telegram-hub:webhook:delete --bot=default
```

پارامترهای مفید:
- `--bot=KEY` یا `--token=` یا `--id=` (شناسه در جدول `telegram_bots`)
- `--allowed-updates=message,callback_query`
- `--drop-pending-updates=true`

### API داخلی پکیج
```http
POST   /{PREFIX}/set-webhook/{bot}
DELETE /{PREFIX}/delete-webhook/{bot}
GET    /{PREFIX}/webhook-info/{bot}
```

---

## 💬 مدیریت پیام‌های دریافتی (Business Logic)

### روش توصیه‌شده: Middleware روی روت پکیج
1) میدلور بسازید: `app/Http/Middleware/TelegramHubInbound.php`
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
2) ثبت در Kernel: `app/Http/Kernel.php`
```php
protected $routeMiddleware = [
    // ...
    'telegram.hub' => \App\Http\Middleware\TelegramHubInbound::class,
];
```
3) فعال‌سازی در `.env`:
```env
TELEGRAM_HUB_ROUTE_MIDDLEWARE=api,telegram.hub
```

> می‌توانید به‌جای میدلور، کنترلر اختصاصی هم بسازید و وبهوک را روی مسیر خودتان ست کنید.

---

## ✉️ ارسال پیام‌ها (Facade / Service)

### Facade
```php
use Amirkateb\TelegramHub\Facades\TelegramHub as TH;

TH::sendMessage(['chat_id' => 123456789, 'text' => 'سلام دنیا']);
```

### انتخاب بات (سه روش)
- از config:
```php
$token = config('telegram_hub.bots.sales');
TH::sendMessage(['chat_id' => 123456789, 'text' => 'سلام از بات فروش'], $token);
```
- توکن لحظه‌ای (مثلاً از فرم یا DB):
```php
$token = '123456789:ABC_DEF_987'; // توکن مستقیم
TH::sendPhoto(['chat_id' => 123456789, 'photo' => 'https://placehold.co/600x400'], $token);
```
- از جدول `telegram_bots`:
```php
$bot = DB::table('telegram_bots')->where('key','sales')->first();
TH::sendMessage(['chat_id'=>123456789,'text'=>'سلام از '.$bot->name], $bot->token);
```

---

## 🖼️ ارسال مدیا از دیسک (Multipart)

```php
use Amirkateb\TelegramHub\Support\InputFile;
use Amirkateb\TelegramHub\Facades\TelegramHub as TH;

// عکس از مسیر فایل
TH::sendPhoto([
  'chat_id' => 123456789,
  'photo'   => InputFile::path(storage_path('app/public/pic.jpg'), 'pic.jpg', 'image/jpeg'),
  'caption' => 'از دیسک'
]);

// سند با stream
$fp = fopen(storage_path('app/private/report.pdf'), 'r');
TH::sendDocument([
  'chat_id'  => 123456789,
  'document' => InputFile::stream($fp, 'report.pdf', 'application/pdf')
]);

// آلبوم چندتایی
$media = [
  ['type'=>'photo','media'=>InputFile::path(storage_path('app/p1.jpg'),'p1.jpg','image/jpeg'),'caption'=>'1'],
  ['type'=>'photo','media'=>InputFile::path(storage_path('app/p2.jpg'),'p2.jpg','image/jpeg'),'caption'=>'2'],
];
TH::sendMediaGroup(['chat_id'=>123456789,'media'=>$media]);
```

---

## 🎛 کیبوردها

```php
use Amirkateb\TelegramHub\Support\Keyboard;
use Amirkateb\TelegramHub\Facades\TelegramHub as TH;

$inline = Keyboard::inline([
  [Keyboard::inlineButton('🌐 سایت', ['url' => 'https://example.com'])],
  [Keyboard::inlineButton('✅ تایید', ['callback_data' => 'CONFIRM'])]
]);

TH::sendMessage([
  'chat_id' => 123456789,
  'text' => 'یکی را انتخاب کنید:',
  'reply_markup' => json_encode($inline, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
]);
```

---

## ✏️ ویرایش و حذف پیام

```php
TH::editMessageText([
  'chat_id' => 123456789,
  'message_id' => 42,
  'text' => 'ویرایش شد ✅'
]);

TH::deleteMessage([
  'chat_id' => 123456789,
  'message_id' => 42
]);
```

---

## 🧰 Artisan Commands

```bash
# ارسال تست ساده
php artisan telegram-hub:send:test --chat=123456789 --text="سلام از تست"

# هر متد Bot API به‌صورت عمومی
php artisan telegram-hub:send --method=sendMessage --kv=chat_id=123456789 --kv=text="Hello"
php artisan telegram-hub:send --method=sendPhoto --data='{"chat_id":123456789,"photo":"https://...","caption":"cap"}'
```

---

## 🛡️ نکات امنیتی و عملیاتی

- `secret_token` را حتماً ست و در وبهوک چک کن
- HTTPS الزامی
- در صورت ترافیک بالا، ارسال‌ها را داخل **Queue** انجام بده
- برای جلوگیری از سوءاستفاده، روی مسیرهای مدیریتی میدلور Auth/API Key بگذار
- از `telegram_logs` برای عیب‌یابی/مانیتور استفاده کن

---

## 🧪 عیب‌یابی

- وبهوک کار نمی‌کند: آدرس صحیح، HTTPS، `secret_token` و لاگ‌های وب‌سرور را بررسی کنید
- 429 Too Many Requests: فاصله بین ارسال‌ها یا صف‌بندی با backoff
- فایل ارسال نمی‌شود: دسترسی فایل/مسیر صحیح و نوع MIME را بررسی کنید

---

## 👤 سازنده

- نام: **Amir Kateb**
- ایمیل: **amveks43@gmail.com**
- گیت‌هاب: **https://github.com/amirkateb**

---

## 📄 مجوز

MIT License