<?php

namespace Fillincode\Tests\Traits;

use Fillincode\Tests\Interfaces\FakeStorageInterface;
use Fillincode\Tests\Interfaces\MockInterface;
use Illuminate\Support\Facades\Storage;


trait MockTrait
{
    /**
     * Проверяет, реализует ли тест интерфейс MockInterface
     */
    protected function checkMockInterface(): bool
    {
        return in_array(
            MockInterface::class,
            class_implements(static::class),
            true
        );
    }

    /**
     * Проверят, реализует ли класс FakeStorageInterface
     */
    protected function checkFakeStorageInterface(): bool
    {
        return in_array(
            FakeStorageInterface::class,
            class_implements(static::class),
            true
        );
    }

    /**
     * Вызывает метод mock, если тест реализует интерфейс MockInterface
     */
    protected function callMockMethod(): void
    {
        if ($this->checkFakeStorageInterface()) {
            Storage::fake('public');
        }

        if ($this->checkMockInterface()) {
            $this->getMockAction();
        }
    }
}
