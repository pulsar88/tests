<?php

namespace Fillincode\Tests\Generator;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

class BaseGenerator
{
    /**
     * Разрыв сроки с табуляцией
     */
    protected string $character = "\n\t\t\t";

    protected string $ds = DIRECTORY_SEPARATOR;

    /**
     * Возвращает необходимый stub
     *
     * @throws FileNotFoundException
     */
    protected function getStub(string $stub): string
    {
        $stub = str($stub)->replace('.', $this->ds);

        return File::get(
            __DIR__ . "$this->ds..$this->ds..{$this->ds}stubs$this->ds$stub.stub"
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