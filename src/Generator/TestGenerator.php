<?php

namespace Fillincode\Tests\Generator;

use Error;
use Fillincode\Tests\Helpers\ReflectionHelper;
use Fillincode\Tests\Helpers\RouteHelper;
use Fillincode\Tests\Interfaces\CodeInterface;
use Fillincode\Tests\Interfaces\JobTestInterface;
use Fillincode\Tests\Interfaces\NotificationTestInterface;
use Fillincode\Tests\Interfaces\SeedInterface;
use Fillincode\Tests\Interfaces\MockInterface;
use Fillincode\Tests\Interfaces\ParametersCodeInterface;
use Fillincode\Tests\Interfaces\ParametersInterface;
use Fillincode\Tests\Interfaces\ValidateInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use ReflectionException;

class TestGenerator extends BaseGenerator
{
    /**
     * Путь к файлу
     */
    protected string $path;

    public function __construct(
        protected string $className,
        protected array  $interfaces,
        protected string $route_name,
        protected string $middlewares,
    )
    {
    }

    /**
     * Генерация класса
     * @throws ReflectionException
     */
    public function generate(): string
    {
        $this->classNameUpdate();
        $this->setPath();

        $stub = $this->getStub('class');

        $stub = $this->stubReplace(
            ['{{ namespace }}', '{{ uses }}', '{{ class }}', '{{ implements }}', '{{ methods }}'],
            [trim($this->getNamespace()), $this->getUses(), trim($this->getClassName()), $this->getImplements(), $this->getMethods()],
            $stub
        );

        $this->saveClass($stub);

        return $this->path;
    }

    /**
     * Возвращает namespace класса
     */
    protected function getNamespace(): string
    {
        if (Str::contains($this->className, '/')) {
            return 'Tests\\Feature\\' . str($this->className)->beforeLast('/')->replace('/', '\\');
        }

        return 'Tests\\Feature';
    }

    /**
     * Возвращает все uses класса
     */
    protected function getUses(): string
    {
        $uses = '';

        foreach ($this->interfaces as $interface) {
            $uses .= 'use ' . $interface . ";\n";
        }

        return rtrim($uses, "\n");
    }

    /**
     * Возвращает имя класса
     */
    protected function getClassName(): string
    {
        if (Str::contains($this->className, '/')) {
            return Str::afterLast($this->className, '/');
        }

        return $this->className;
    }

    /**
     * Возвращает implements класса
     */
    protected function getImplements(): string
    {
        if (!count($this->interfaces)) {
            return '';
        }

        $implements = 'implements ';

        foreach ($this->interfaces as $interface) {
            $implements .= Str::afterLast($interface, '\\') . ', ';
        }

        return rtrim($implements, ', ');
    }

    /**
     * Возвращает методы класса
     * @throws ReflectionException
     */
    protected function getMethods(): string
    {
        $methods = $this->getRouteMiddlewares();

        foreach ($this->interfaces as $interface) {
            $methods .= match ($interface) {
                CodeInterface::class => $this->getFilledCodes() . "\n",
                ParametersInterface::class => $this->getFilledParameters() . "\n",
                SeedInterface::class => $this->getStub('methods.seed') . "\n",
                MockInterface::class => $this->getStub('methods.mock') . "\n",
                ParametersCodeInterface::class => $this->getFilledInvalidParametersCodes() . "\n",
                ValidateInterface::class => $this->getFilledValidData() . "\n",
                NotificationTestInterface::class => $this->getStub('methods.notify_check') . "\n",
                JobTestInterface::class => $this->getStub('methods.job_check') . "\n",
                default => '',
            };
        }

        return trim($methods, "\n");
    }

    /**
     * Возвращает методы получения маршрута и промежуточного ПО
     */
    protected function getRouteMiddlewares(): string
    {
        $stub = $this->getStub('methods.route_middleware');

        $result = '';
        $middlewares = explode(',', str_replace(' ', '', trim($this->middlewares, ',')));

        foreach ($middlewares as $middleware) {
            $result .= "'$middleware', ";
        }

        return $this->stubReplace(
                ['{{ route }}', '{{ middleware }}',],
                [$this->route_name, rtrim(trim($result), ',')],
                $stub
            ) . "\n";
    }

    /**
     * Возвращает метод для изменения кодов ответа пользователей
     */
    protected function getFilledCodes(): string
    {
        $stub = $this->getStub('methods.codes');

        $result = '';

        foreach (config('fillincode_tests.users') as $user => $guard) {
            $result .= "'$user' => " . config('fillincode_tests.' . $user) . ",$this->character";
        }

        return $this->stubReplace(
            ['{{ default_codes }}'],
            [rtrim($result, ",$this->character")],
            $stub
        );
    }

    /**
     * Возвращает метод с передачей параметров
     */
    protected function getFilledParameters(): string
    {
        $stub = $this->getStub('methods.parameters');

        $result = '';

        if ($this->route_name) {
            $uri = Route::getRoutes()->getByName($this->route_name)->uri();

            foreach (RouteHelper::getParameters($uri) as $parameter) {
                $parameter = str_replace(['{', '}'], '', $parameter);

                $result .= "'$parameter' => ''" . ",\n\t\t\t";
            }
        }

        return $this->stubReplace(
            ['{{ parameters }}'],
            [rtrim($result, ",$this->character")],
            $stub
        );
    }

    /**
     * Возвращает метод для изменения кодов с передачей невалидных параметров
     */
    protected function getFilledInvalidParametersCodes(): string
    {
        $stub = $this->getStub('methods.parameters_codes');

        $result = '';

        foreach (config('fillincode_tests.users') as $user => $guard) {
            $result .= "'$user' => " . config('fillincode_tests.invalid_parameters') . ",\n\t\t\t";
        }

        return $this->stubReplace(
            ['{{ default_codes }}'],
            [rtrim($result, ",$this->character")],
            $stub
        );
    }

    /**
     * Возвращает метод для передачи данных в теле запроса
     *
     * @throws ReflectionException
     */
    protected function getFilledValidData(): string
    {
        $stub = $this->getStub('methods.validate');

        $route = Route::getRoutes()->getByName($this->route_name);
        ReflectionHelper::setActionController($route);

        if (!empty(ReflectionHelper::$actionController)) {
            $keys = ReflectionHelper::getFormRequestArrayKeys($route);
        }

        $result = '';

        foreach ($keys ?? [] as $key) {
            $result .= "'$key' => '',\n\t\t\t";
        }

        return $this->stubReplace(
                ['{{ data }}'],
                [rtrim($result, ",$this->character")],
                $stub
            ) . "\n";
    }

    /**
     * Получает путь для сохранения файла
     */
    protected function setPath(): void
    {
        $this->path = 'tests' . DIRECTORY_SEPARATOR . 'Feature' . DIRECTORY_SEPARATOR .
            str($this->className)->replace('/', DIRECTORY_SEPARATOR)->value() . '.php';
    }

    /**
     * Сохранение файла
     */
    protected function saveClass(string $stub): void
    {
        if (File::exists($this->path)) {
            throw new Error($this->path . ' already exists');
        }

        if (!File::isDirectory(dirname($this->path))) {
            File::makeDirectory(dirname($this->path), 0777, true);
        }

        File::put($this->path, $stub);
    }

    /**
     * Замена обратной косой черты на косую черту и добавляет Test в конец имени, если его нет
     */
    protected function ClassNameUpdate(): void
    {
        $this->className = str($this->className)->replace('\\', '/');

        $last = str($this->className)->afterLast('/');

        if (!$last->endsWith('Test') && !$last->endsWith('test')) {
            $this->className .= 'Test';
        } else if ($last->endsWith('test')) {
            $this->className = str($this->className)->replaceLast('test', 'Test');
        }
    }
}