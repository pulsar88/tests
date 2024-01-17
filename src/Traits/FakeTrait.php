<?php

namespace Fillincode\Tests\Traits;

use Fillincode\Tests\Interfaces\FakeInterface;

trait FakeTrait
{
    /**
     * Проверяет реализует ли класс интерфейс для заполнения фейковыми данными
     */
    public function checkFakeInterface(): bool
    {
        return in_array(
            FakeInterface::class,
            class_implements(static::class),
            true
        );
    }

    /**
     * Вызывает метод для заполнения фейковыми данными
     */
    public function callFakeMethod(): void
    {
        if ($this->checkFakeInterface()) {
            $this->faker();
        }
    }
}
