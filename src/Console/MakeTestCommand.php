<?php

namespace Fillincode\Tests\Console;

use Fillincode\Tests\Contracts\CodeContract;
use Fillincode\Tests\Contracts\DocIgnoreContract;
use Fillincode\Tests\Contracts\FakeStorageContract;
use Fillincode\Tests\Contracts\InvalidateCodeContract;
use Fillincode\Tests\Contracts\InvalidateContract;
use Fillincode\Tests\Contracts\InvalidParametersCodeContract;
use Fillincode\Tests\Contracts\InvalidParametersContract;
use Fillincode\Tests\Contracts\JobContract;
use Fillincode\Tests\Contracts\MockContract;
use Fillincode\Tests\Contracts\NotificationContract;
use Fillincode\Tests\Contracts\ParametersContract;
use Fillincode\Tests\Contracts\SeedContract;
use Fillincode\Tests\Contracts\ValidateContract;
use Fillincode\Tests\Generator\TestGenerator\TestGenerator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Route;
use ReflectionException;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

class MakeTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fillincode-test:make-test 
                            {--A|admin : Создания теста для админки}
                            {group? : Определение группы тестов}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает класс для тестирования';

    /**
     * Execute the console command.
     *
     * @throws ReflectionException|FileNotFoundException
     */
    public function handle(): int
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
            'Введите промежуточное ПО через запятую (между промежуточным ПО должны быть запятая и пробел)',
            'api, auth',
            implode(', ', $route->middleware() ?? [])
        );

        $contracts = [
            CodeContract::class,
            ValidateContract::class,
            InvalidateContract::class,
            InvalidateCodeContract::class,
            ParametersContract::class,
            InvalidParametersContract::class,
            InvalidParametersCodeContract::class,
            SeedContract::class,
            FakeStorageContract::class,
            JobContract::class,
            MockContract::class,
            NotificationContract::class,
        ];

        if (class_exists('Fillincode\Swagger\Parser\TestParser')) {
            $contracts[] = DocIgnoreContract::class;
        }

        $contracts = multiselect(
            label: 'Выберите интерфейсы, которые должен будет реализовать тест',
            options: $contracts
        );

        $generator = new TestGenerator(
            $className, $contracts, $route_name, $middlewares, $this->getGroup(), $this->getPrefix()
        );

        info(sprintf('class [%s] created successfully.', $generator->generate()));

        return self::SUCCESS;
    }

    protected function getGroup(): string
    {
        return $this->option('admin') ? 'admin_panel' : 'app';
    }

    protected function getPrefix(): ?string
    {
        return $this->option('admin') ? null : $this->argument('group', array_key(config('fillincode-test.app')[1]));
    }
}