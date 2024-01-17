<?php

namespace Fillincode\Tests\Interfaces;

interface FakeInterface
{
    /**
     * Данные, которыми будет заполнена БД перед запросом
     */
    public function faker(): void;
}
