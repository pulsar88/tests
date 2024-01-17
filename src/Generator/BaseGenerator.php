<?php

namespace Fillincode\Tests\Generator;

use Illuminate\Support\Facades\File;

class BaseGenerator
{
    /**
     * Разрыв сроки с табуляцией
     */
    protected string $character = "\n\t\t\t";

    /**
     * Возвращает необходимый stub
     */
    protected function getStub(string $stub): string
    {
        return File::get(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $stub . '.stub'
        );
    }

    /**
     * Заменяет шаблоны блоками кода
     */
    protected function stubReplace(array $search, array $replace, string $stub): string
    {
        return str_replace($search, $replace, $stub);
    }
}