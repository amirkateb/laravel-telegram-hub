<?php

namespace Amirkateb\TelegramHub\Support;

class Payload
{
    public static function fromOptions(array $kvList, ?string $json): array
    {
        $base = [];
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $base = $decoded;
            }
        }
        foreach ($kvList as $pair) {
            $eq = strpos($pair, '=');
            if ($eq === false) {
                continue;
            }
            $key = substr($pair, 0, $eq);
            $val = substr($pair, $eq + 1);
            $base[$key] = self::cast($val);
        }
        return $base;
    }

    protected static function cast(string $val)
    {
        $trim = trim($val);

        if ($trim === 'null') return null;
        if ($trim === 'true') return true;
        if ($trim === 'false') return false;

        if (is_numeric($trim)) {
            if (ctype_digit($trim)) return (int) $trim;
            return (float) $trim;
        }

        $j = json_decode($trim, true);
        if (json_last_error() === JSON_ERROR_NONE && (is_array($j) || is_object($j))) {
            return $j;
        }

        return $val;
    }
}