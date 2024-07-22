<?php

namespace Fillincode\Tests;

use Fillincode\Tests\Console\InitConfigCommand;
use Fillincode\Tests\Console\MakeMoonshineTestCommand;
use Fillincode\Tests\Console\MakeTestCommand;
use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/fillincode-tests.php' => config_path('fillincode-tests.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands($this->getCommands());
        }
    }

    protected function getCommands(): array
    {
        $commands = [
            MakeTestCommand::class,
            InitConfigCommand::class,
        ];

        if (class_exists('MoonShine\Resources\ModelResource')) {
            $commands[] = MakeMoonshineTestCommand::class;
        }

        return $commands;
    }
}