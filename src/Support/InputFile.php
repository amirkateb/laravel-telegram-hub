<?php

namespace Amirkateb\TelegramHub\Support;

class InputFile
{
    public static function path(string $path, ?string $filename = null, ?string $mime = null): array
    {
        return [
            '__tg_inputfile' => true,
            'type' => 'path',
            'path' => $path,
            'filename' => $filename,
            'mime' => $mime
        ];
    }

    public static function stream($resource, ?string $filename = null, ?string $mime = null): array
    {
        return [
            '__tg_inputfile' => true,
            'type' => 'stream',
            'stream' => $resource,
            'filename' => $filename,
            'mime' => $mime
        ];
    }

    public static function contents(string $binary, ?string $filename = null, ?string $mime = null): array
    {
        return [
            '__tg_inputfile' => true,
            'type' => 'contents',
            'contents' => $binary,
            'filename' => $filename,
            'mime' => $mime
        ];
    }
}