<?php

namespace Amirkateb\TelegramHub\Facades;

use Illuminate\Support\Facades\Facade;

class TelegramHub extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'telegram.hub';
    }
}