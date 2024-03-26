<?php

namespace Fillincode\Tests\Interfaces;

interface ValidateCodeInterface
{
    /**
     * Код для невалидных данных
     */
    public function invalidParamCode(): int;
}