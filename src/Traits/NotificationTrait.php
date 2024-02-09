<?php

namespace Fillincode\Tests\Traits;

use Fillincode\Tests\Interfaces\NotificationTestInterface;
use Illuminate\Support\Facades\Notification;

trait NotificationTrait
{
    /**
     * Проверяет, реализует ли класс интерфейс для тестирования уведомлений
     */
    public function checkNotifyInterface(): bool
    {
        return in_array(
            NotificationTestInterface::class,
            class_implements(static::class),
            true
        );
    }

    /**
     * Вызывает метод для проверки уведомлений
     */
    public function callNotifyTestMethod(string $user_type): void
    {
        if ($this->checkNotifyInterface()) {
            $this->notifyCheck($user_type);
        }
    }
}