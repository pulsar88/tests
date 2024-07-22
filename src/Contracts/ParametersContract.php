<?php

namespace Fillincode\Tests\Contracts;

interface ParametersContract
{
    /**
     * Возвращает параметры для маршрутов
     */
    public function parameters(string $user_key): array;
}
