<?php

namespace Fillincode\Tests\Contracts;

interface JobContract
{
    /**
     * Тестирование работы задач во время выполнения запросов
     */
    public function jobs(string $user_key): void;
}