<?php

namespace Fillincode\Tests\Interfaces;

interface NotificationTestInterface
{
    /**
     * Тестирование отправки уведомлений
     *
     * @param string $user_type Тип пользователя, от которого выполняется тест
     * @return void
     */
    public function notifyCheck(string $user_type): void;
}