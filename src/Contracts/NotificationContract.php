<?php

namespace Fillincode\Tests\Contracts;

interface NotificationContract
{
    /**
     * Тестирование отправки уведомлений
     */
    public function notifications(string $user_key): void;
}