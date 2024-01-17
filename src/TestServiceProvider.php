<?php

namespace Fillincode\Tests;

use Fillincode\Tests\Console\MakeBaseFeatureTestCommand;
use Fillincode\Tests\Console\MakeFTestCommand;
use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/fillincode_tests.php' => config_path('fillincode_tests.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeFTestCommand::class,
                MakeBaseFeatureTestCommand::class,
            ]);
        }
    }
}