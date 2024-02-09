<?php

namespace Fillincode\Tests\Interfaces;

interface SeedInterface
{
    /**
     * Данные, которыми будет заполнена БД перед запросом
     */
    public function dbSeed(): void;
}
