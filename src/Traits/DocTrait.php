<?php

namespace Fillincode\Tests\Traits;

use Fillincode\Tests\Interfaces\DocIgnoreInterface;

trait DocTrait
{
    /**
     * Проверяет, реализует ли класс интерфейс игнорирования документирования теста
     */
    public function checkDocIgnoreInterface(): bool
    {
        return in_array(
            DocIgnoreInterface::class,
            class_implements(static::class),
            true
        );
    }
}
