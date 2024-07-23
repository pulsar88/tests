<?php

namespace Fillincode\Tests\Contracts;

interface SeedContract
{
    /**
     * Данные, которыми будет заполнена БД перед запросом
     */
    public function dbSeed(string $user_key): void;
}
