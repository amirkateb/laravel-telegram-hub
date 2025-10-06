<?php

namespace Amirkateb\TelegramHub\Support;

class Keyboard
{
    public static function inline(array $rows): array
    {
        return ['inline_keyboard' => $rows];
    }

    public static function inlineButton(string $text, array $options = []): array
    {
        return array_merge(['text' => $text], $options);
    }

    public static function reply(array $rows, bool $resize = true, bool $oneTime = false, bool $isPersistent = false, bool $selective = false): array
    {
        return [
            'keyboard' => $rows,
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $oneTime,
            'is_persistent' => $isPersistent,
            'selective' => $selective
        ];
    }

    public static function remove(bool $selective = false): array
    {
        return [
            'remove_keyboard' => true,
            'selective' => $selective
        ];
    }

    public static function forceReply(bool $selective = false, string $inputPlaceholder = null): array
    {
        $data = ['force_reply' => true, 'selective' => $selective];
        if ($inputPlaceholder !== null) {
            $data['input_field_placeholder'] = $inputPlaceholder;
        }
        return $data;
    }
}