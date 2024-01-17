<?php

namespace Fillincode\Tests\Interfaces;

interface ParametersCodeInterface
{
    /**
     * Возвращает коды для пользователей
     */
    public function getCodesForInvalidParameters(): array;
}
