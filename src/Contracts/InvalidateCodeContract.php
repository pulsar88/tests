<?php

namespace Fillincode\Tests\Contracts;

interface InvalidateCodeContract
{
    /**
     * Код для невалидных данных
     */
    public function invalidDataCode(string $user_key): int;
}