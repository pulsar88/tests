<?php

namespace Fillincode\Tests\Contracts;

interface ValidateContract
{
    /**
     * Валидные данные
     */
    public function validData(string $user_key): array;
}
