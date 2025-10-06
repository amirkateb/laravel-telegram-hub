<?php

namespace Amirkateb\TelegramHub\Apis;

use Amirkateb\TelegramHub\TelegramHub;

class ChatAdminApi
{
    protected TelegramHub $hub;

    public function __construct(TelegramHub $hub)
    {
        $this->hub = $hub;
    }

    public function banChatMember(array $params, ?string $token = null): array
    {
        return $this->hub->call('banChatMember', $params, $token);
    }

    public function unbanChatMember(array $params, ?string $token = null): array
    {
        return $this->hub->call('unbanChatMember', $params, $token);
    }

    public function restrictChatMember(array $params, ?string $token = null): array
    {
        return $this->hub->call('restrictChatMember', $params, $token);
    }

    public function promoteChatMember(array $params, ?string $token = null): array
    {
        return $this->hub->call('promoteChatMember', $params, $token);
    }

    public function setChatAdministratorCustomTitle(array $params, ?string $token = null): array
    {
        return $this->hub->call('setChatAdministratorCustomTitle', $params, $token);
    }

    public function setChatPermissions(array $params, ?string $token = null): array
    {
        return $this->hub->call('setChatPermissions', $params, $token);
    }

    public function setChatTitle(array $params, ?string $token = null): array
    {
        return $this->hub->call('setChatTitle', $params, $token);
    }

    public function setChatDescription(array $params, ?string $token = null): array
    {
        return $this->hub->call('setChatDescription', $params, $token);
    }

    public function pinChatMessage(array $params, ?string $token = null): array
    {
        return $this->hub->call('pinChatMessage', $params, $token);
    }

    public function unpinChatMessage(array $params, ?string $token = null): array
    {
        return $this->hub->call('unpinChatMessage', $params, $token);
    }

    public function unpinAllChatMessages(array $params, ?string $token = null): array
    {
        return $this->hub->call('unpinAllChatMessages', $params, $token);
    }

    public function setChatPhoto(array $params, ?string $token = null): array
    {
        return $this->hub->call('setChatPhoto', $params, $token);
    }

    public function deleteChatPhoto(array $params, ?string $token = null): array
    {
        return $this->hub->call('deleteChatPhoto', $params, $token);
    }
}