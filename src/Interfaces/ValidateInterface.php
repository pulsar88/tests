<?php

namespace Fillincode\Tests\Interfaces;

interface ValidateInterface
{
    /**
     * Валидные данные
     */
    public function getValidData(): array;

    /**
     * Невалидные данные
     */
    public function getNotValidData(): array;
}
