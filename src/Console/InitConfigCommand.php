<?php

namespace Fillincode\Tests\Console;

use Fillincode\Tests\Generator\BaseClassGenerator\BaseTestCaseGenerator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class InitConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fillincode-test:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает базовые классы на основе конфига';

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        (new BaseTestCaseGenerator('feature'))->generate();

        if (config('fillincode-tests.admin_panel.name') === 'moonshine') {
            (new BaseTestCaseGenerator('admin_panel'))->generate();
        }

        return self::SUCCESS;
    }
}