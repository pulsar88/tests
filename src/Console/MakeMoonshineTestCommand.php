<?php

namespace Fillincode\Tests\Console;

use Fillincode\Tests\Generator\TestGenerator\MoonshineTestGenerator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use MoonShine\Resources\ModelResource;

class MakeMoonshineTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fillincode-test:moonshine-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает тесты для ресурса админ панели moonshine';

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $generator = new MoonshineTestGenerator();

        foreach (moonshine()->getResources() as $resource) {
            if (!$resource instanceof ModelResource) {
                continue;
            }

            $generator->setProperty($resource, 'index')->generate();
            $generator->setProperty($resource, 'show')->generate();
            $generator->setProperty($resource, 'create')->generate();
            $generator->setProperty($resource, 'store')->generate();
            $generator->setProperty($resource, 'edit')->generate();
            $generator->setProperty($resource, 'update')->generate();
            $generator->setProperty($resource, 'destroy')->generate();
        }

        return self::SUCCESS;
    }
}