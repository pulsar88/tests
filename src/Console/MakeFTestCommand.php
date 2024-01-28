<?php

namespace Fillincode\Tests\Console;

use Fillincode\Tests\Interfaces\CodeInterface;
use Fillincode\Tests\Interfaces\DocIgnoreInterface;
use Fillincode\Tests\Interfaces\FakeInterface;
use Fillincode\Tests\Interfaces\FakeStorageInterface;
use Fillincode\Tests\Interfaces\MockInterface;
use Fillincode\Tests\Interfaces\ParametersCodeInterface;
use Fillincode\Tests\Interfaces\ParametersInterface;
use Fillincode\Tests\Interfaces\ValidateInterface;
use Fillincode\Tests\Generator\TestGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use ReflectionException;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

class MakeFTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:f-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает класс для тестирования';

    /**
     * Execute the console command.
     * @throws ReflectionException
     */
    public function handle()
    {
        $className = $this->ask('Введите название класса');

        if (!$className) {
            return self::FAIL;
        }

        $routes = Route::getRoutes();

        $route_name = $this->anticipate('Введите имя маршрута', collect($routes)->map(function ($route) {
            return $route->getName();
        })->filter()->all()->toArray());

        $middlewares = $this->ask(
            'Введите промежуточное ПО через запятую',
            implode(', ', $routes->getByName($route_name)->middleware() ?? [])
        );

        $interfaces = [
            CodeInterface::class,
            FakeInterface::class,
            FakeStorageInterface::class,
            MockInterface::class,
            ParametersInterface::class,
            ParametersCodeInterface::class,
            ValidateInterface::class,
        ];

        if (class_exists('Fillincode\Swagger\Parser\TestParser')) {
            $interfaces[] = DocIgnoreInterface::class;
        }

        $interfaces = $this->choice(
            'Выберите интерфейсы, которые должен будет реализовать тест',
            $interfaces,
        );

        $generator = new TestGenerator($className, $interfaces, $route_name, $middlewares);

        $this->info(sprintf('class [%s] created successfully.', $generator->generate()));

        return self::SUCCESS;
    }
}
