<?php

namespace Amirkateb\TelegramHub\Apis;

use Amirkateb\TelegramHub\TelegramHub;

class MessageApi
{
    protected TelegramHub $hub;

    public function __construct(TelegramHub $hub)
    {
        $this->hub = $hub;
    }

    public function sendMessage(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendMessage', $params, $token);
    }

    public function forwardMessage(array $params, ?string $token = null): array
    {
        return $this->hub->call('forwardMessage', $params, $token);
    }

    public function copyMessage(array $params, ?string $token = null): array
    {
        return $this->hub->call('copyMessage', $params, $token);
    }

    public function sendLocation(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendLocation', $params, $token);
    }

    public function sendVenue(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendVenue', $params, $token);
    }

    public function sendContact(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendContact', $params, $token);
    }

    public function sendPoll(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendPoll', $params, $token);
    }

    public function stopPoll(array $params, ?string $token = null): array
    {
        return $this->hub->call('stopPoll', $params, $token);
    }

    public function sendDice(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendDice', $params, $token);
    }

    public function sendChatAction(array $params, ?string $token = null): array
    {
        return $this->hub->call('sendChatAction', $params, $token);
    }

    public function editMessageText(array $params, ?string $token = null): array
    {
        return $this->hub->call('editMessageText', $params, $token);
    }

    public function editMessageCaption(array $params, ?string $token = null): array
    {
        return $this->hub->call('editMessageCaption', $params, $token);
    }

    public function editMessageReplyMarkup(array $params, ?string $token = null): array
    {
        return $this->hub->call('editMessageReplyMarkup', $params, $token);
    }

    public function deleteMessage(array $params, ?string $token = null): array
    {
        return $this->hub->call('deleteMessage', $params, $token);
    }

    public function answerCallbackQuery(array $params, ?string $token = null): array
    {
        return $this->hub->call('answerCallbackQuery', $params, $token);
    }
}