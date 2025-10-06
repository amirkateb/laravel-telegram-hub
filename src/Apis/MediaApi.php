<?php

namespace Amirkateb\TelegramHub\Apis;

use Amirkateb\TelegramHub\TelegramHub;

class MediaApi
{
    protected TelegramHub $hub;

    public function __construct(TelegramHub $hub)
    {
        $this->hub = $hub;
    }

    public function sendPhoto(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendPhoto', $params, $token);
    }

    public function sendAudio(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendAudio', $params, $token);
    }

    public function sendDocument(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendDocument', $params, $token);
    }

    public function sendVideo(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendVideo', $params, $token);
    }

    public function sendAnimation(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendAnimation', $params, $token);
    }

    public function sendVoice(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendVoice', $params, $token);
    }

    public function sendVideoNote(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendVideoNote', $params, $token);
    }

    public function sendMediaGroup(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendMediaGroup', $params, $token);
    }

    public function editMessageMedia(array $params, ?string $token = null): array
    {
        return $this->hub->call('editMessageMedia', $params, $token);
    }
}