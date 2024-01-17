<?php

namespace Fillincode\Tests\Interfaces;

interface CodeInterface
{
    /**
     * Возвращает коды ответа для пользователей
     */
    public function getCodes(): array;
}
