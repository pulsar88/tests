<?php

namespace Fillincode\Tests\Traits;

use Fillincode\Tests\Interfaces\JobTestInterface;
use Illuminate\Support\Facades\Storage;

trait JobTrait
{
    /**
     * Проверят, реализует ли класс JobTestInterface
     */
    protected function checkJobTestInterface(): bool
    {
        return in_array(
            JobTestInterface::class,
            class_implements(static::class),
            true
        );
    }

    /**
     * Вызывает метод mock, если тест реализует интерфейс MockInterface
     */
    protected function callJobCheckMethod(string $user_type): void
    {
        if ($this->checkJobTestInterface()) {
            $this->jobCheck($user_type);
        }
    }
}