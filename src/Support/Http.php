<?php

namespace Amirkateb\TelegramHub\Support;

class Http
{
    public static function buildOptions(array $params): array
    {
        $hasFile = false;
        foreach ($params as $k => $v) {
            if (self::isFile($v)) {
                $hasFile = true;
                break;
            }
        }
        if ($hasFile) {
            $multipart = [];
            foreach ($params as $k => $v) {
                if (self::isFile($v)) {
                    $multipart[] = self::filePart($k, $v);
                } else {
                    $multipart[] = [
                        'name' => $k,
                        'contents' => self::encodeScalar($v)
                    ];
                }
            }
            return ['multipart' => $multipart];
        }
        $encoded = [];
        foreach ($params as $k => $v) {
            $encoded[$k] = self::encodeScalar($v);
        }
        return ['form_params' => $encoded];
    }

    protected static function isFile($v): bool
    {
        if ($v instanceof \CURLFile) return true;
        if (is_resource($v)) return true;
        if (is_array($v) && isset($v['__tg_inputfile']) && $v['__tg_inputfile'] === true) return true;
        return false;
    }

    protected static function filePart(string $name, $v): array
    {
        if ($v instanceof \CURLFile) {
            return ['name' => $name, 'contents' => $v];
        }
        if (is_resource($v)) {
            return ['name' => $name, 'contents' => $v];
        }
        if (is_array($v) && ($v['__tg_inputfile'] ?? false)) {
            $filename = $v['filename'] ?? null;
            $mime = $v['mime'] ?? null;
            if (($v['type'] ?? null) === 'path') {
                $stream = fopen($v['path'], 'r');
                $part = ['name' => $name, 'contents' => $stream];
                if ($filename) $part['filename'] = $filename;
                if ($mime) $part['headers'] = ['Content-Type' => $mime];
                return $part;
            }
            if (($v['type'] ?? null) === 'stream') {
                $part = ['name' => $name, 'contents' => $v['stream']];
                if ($filename) $part['filename'] = $filename;
                if ($mime) $part['headers'] = ['Content-Type' => $mime];
                return $part;
            }
            if (($v['type'] ?? null) === 'contents') {
                $part = ['name' => $name, 'contents' => $v['contents']];
                if ($filename) $part['filename'] = $filename;
                if ($mime) $part['headers'] = ['Content-Type' => $mime];
                return $part;
            }
        }
        return ['name' => $name, 'contents' => $v];
    }

    protected static function encodeScalar($v)
    {
        if (is_array($v) || is_object($v)) {
            return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        if (is_bool($v)) return $v ? 'true' : 'false';
        if ($v === null) return 'null';
        return $v;
    }
}