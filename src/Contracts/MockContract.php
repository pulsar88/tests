<?php

namespace Fillincode\Tests\Contracts;

interface MockContract
{
    /**
     * Действия, которые будут сымитированы во время запроса
     */
    public function mockAction(string $user_key): void;
}
