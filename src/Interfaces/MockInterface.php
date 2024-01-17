<?php

namespace Fillincode\Tests\Interfaces;

interface MockInterface
{
    /**
     * Действия, которые будут сымитированы во время запроса
     */
    public function getMockAction(): void;
}
