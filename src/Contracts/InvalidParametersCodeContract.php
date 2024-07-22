<?php

namespace Fillincode\Tests\Contracts;

interface InvalidParametersCodeContract
{
    /**
     * Возвращает коды для пользователей
     */
    public function codesForInvalidParameters(): array;
}
