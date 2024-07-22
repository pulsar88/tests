<?php

namespace Fillincode\Tests\Contracts;

interface CodeContract
{
    /**
     * Возвращает коды ответа для пользователей
     */
    public function codes(string $user_key): array;
}
