<?php

namespace Fillincode\Tests\Traits;

use Fillincode\Tests\Interfaces\FakeInterface;

trait SeedTrait
{
    /**
     * Проверяет реализует ли класс интерфейс для заполнения фейковыми данными
     */
    public function checkSeedInterface(): bool
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
    public function callSeedMethod(): void
    {
        if ($this->checkSeedInterface()) {
            $this->dbSeed();
        }
    }
}
