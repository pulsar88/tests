<?php

namespace Fillincode\Tests\Contracts;

interface InvalidateContract
{
    /**
     * Невалидные данные
     */
    public function invalidData(string $user_key): array;
}