<?php

namespace Fillincode\Tests\Console;

use Fillincode\Tests\Interfaces\CodeInterface;
use Fillincode\Tests\Interfaces\DocIgnoreInterface;
use Fillincode\Tests\Interfaces\JobTestInterface;
use Fillincode\Tests\Interfaces\NotificationTestInterface;
use Fillincode\Tests\Interfaces\SeedInterface;
use Fillincode\Tests\Interfaces\FakeStorageInterface;
use Fillincode\Tests\Interfaces\MockInterface;
use Fillincode\Tests\Interfaces\ParametersCodeInterface;
use Fillincode\Tests\Interfaces\ParametersInterface;
use Fillincode\Tests\Interfaces\ValidateInterface;
use Fillincode\Tests\Generator\TestGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use ReflectionException;
use function Laravel\Prompts\error;
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
        $className = text(
            'введите название класса',
            'Projects/GetProjectTest',
            required: true,
            validate: fn (string $value) => match (true) {
                strlen($value) < 3 => 'The name must be at least 3 characters.',
                strlen($value) > 255 => 'The name must not exceed 255 characters.',
                default => null
            },
        );

        $routes = Route::getRoutes();

        $route_name = suggest(
            'введите имя маршрута',
            collect($routes)->map(function ($route) {
                return $route->getName();
            })->filter()->all(),
            'api.user.update',
        );

        $route = $routes->getByName($route_name);

        if (!$route) {
            error('Маршрут с именем [' . $route_name . '] не найден');
            return self::FAILURE;
        }

        $middlewares = text(
            'Введите промежуточное ПО через запятую',
            'api, auth',
            implode(', ', $route->middleware() ?? [])
        );

        $interfaces = [
            CodeInterface::class,
            FakeStorageInterface::class,
            ParametersInterface::class,
            ParametersCodeInterface::class,
            ValidateInterface::class,
            SeedInterface::class,
            NotificationTestInterface::class,
            JobTestInterface::class,
            MockInterface::class,
        ];

        if (class_exists('Fillincode\Swagger\Parser\TestParser')) {
            $interfaces[] = DocIgnoreInterface::class;
        }

        $interfaces = multiselect(
            label: 'Выберите интерфейсы, которые должен будет реализовать тест',
            options: $interfaces
        );

        $generator = new TestGenerator($className, $interfaces, $route_name, $middlewares);

        info(sprintf('class [%s] created successfully.', $generator->generate()));

        return self::SUCCESS;
    }
}
