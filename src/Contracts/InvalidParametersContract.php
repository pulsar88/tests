<?php

namespace Fillincode\Tests\Contracts;

interface InvalidParametersContract
{
    /**
     * Возвращает невалидные параметры
     */
    public function invalidParameters(string $user_key): array;
}