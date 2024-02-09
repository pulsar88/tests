<?php

namespace Fillincode\Tests\Interfaces;

interface JobTestInterface
{
    /**
     * Тестирование работы задач во время выполнения запросов
     */
    public function jobCheck(string $user_type): void;
}