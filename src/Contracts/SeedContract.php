<?php

namespace Fillincode\Tests\Contracts;

interface SeedContract
{
    /**
     * Данные, которыми будет заполнена БД перед запросом
     */
    public function seeder(string $user_key): void;
}
