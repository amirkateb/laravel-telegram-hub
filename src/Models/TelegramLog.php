<?php

namespace Amirkateb\TelegramHub\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramLog extends Model
{
    protected $table = 'telegram_logs';
    protected $fillable = [
        'direction',
        'bot_key',
        'bot_id',
        'chat_id',
        'message_id',
        'method',
        'status_code',
        'ok',
        'error_code',
        'error_description',
        'payload',
        'response'
    ];
    protected $casts = [
        'ok' => 'boolean',
        'payload' => 'array',
        'response' => 'array'
    ];
}