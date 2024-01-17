<?php

namespace Fillincode\Tests\Console;

use Fillincode\Tests\Generator\BaseFeatureTestGenerator;
use Illuminate\Console\Command;

class MakeBaseFeatureTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'f-tests:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $generator = new BaseFeatureTestGenerator();

        $generator->generate();

        return self::SUCCESS;
    }
}