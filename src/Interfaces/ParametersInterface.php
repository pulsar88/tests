<?php

namespace Fillincode\Tests\Interfaces;

interface ParametersInterface
{
    /**
     * Возвращает параметры для маршрутов
     */
    public function getParameters(): array;

    /**
     * Возвращает невалидные параметры
     */
    public function getInvalidParameters(): array;
}
