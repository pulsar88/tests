<?php

namespace Fillincode\Tests\Generator\TestGenerator;

use Error;
use Fillincode\Tests\Helpers\ConfigHelper;
use Fillincode\Tests\Helpers\ReflectionHelper;
use Fillincode\Tests\Helpers\RouteHelper;
use Fillincode\Tests\Contracts\CodeContract;
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
use Fillincode\Tests\Generator\BaseGenerator;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionException;

class TestGenerator extends BaseGenerator
{
    /**
     * @param string $className Название класса
     * @param array $contracts Контракты, которые имплементируют класс теста
     * @param string $route_name Имя маршрута
     * @param string $middlewares Миделвары маршрута
     * @param string $group Группа тестов
     * @param string|null $prefix Префикс тестов
     * @param string|null $configKey Ключ конфигурации
     * @param string|null $path Путь к файлу
     */
    public function __construct(
        protected string  $className,
        protected array   $contracts,
        protected string  $route_name,
        protected string  $middlewares,
        protected string  $group,
        protected ?string $prefix = null,
        protected ?string $configKey = null,
        protected ?string $path = null,
    )
    {
        $this->configKey = $this->prefix;
        $this->prefix = str($this->prefix ?: $this->group)->lower()->studly() . '\\';
    }

    /**
     * Генерация класса
     *
     * @throws ReflectionException|FileNotFoundException
     */
    public function generate(): string
    {
        $this->classNameUpdate();
        $this->setPath();

        $stub = $this->getStub('test.class');

        $stub = $this->stubReplace(
            ['{{ namespace }}', '{{ extendsClass }}', '{{ uses }}', '{{ class }}', '{{ implements }}', '{{ methods }}'],
            [trim($this->getNamespace()), $this->getExtendsClass(), $this->getUses(), trim($this->getClassName()), $this->getImplements(), $this->getMethods()],
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
            return "Tests\\Feature\\$this->prefix" . str($this->className)->beforeLast('/')->replace('/', '\\');
        }

        return "Tests\\Feature\\$this->prefix";
    }

    protected function getExtendsClass(): string
    {
        return "Base{$this->prefix}TestCase";
    }

    /**
     * Возвращает все uses класса
     */
    protected function getUses(): string
    {
        $uses = '';

        foreach ($this->contracts as $contract) {
            $uses .= 'use ' . $contract . ";\n";
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
        if (!count($this->contracts)) {
            return '';
        }

        $implements = 'implements ';

        foreach ($this->contracts as $contract) {
            $implements .= Str::afterLast($contract, '\\') . ', ';
        }

        return rtrim($implements, ', ');
    }

    /**
     * Возвращает методы класса
     *
     * @throws ReflectionException|FileNotFoundException
     */
    protected function getMethods(): string
    {
        $methods = $this->getRouteMiddlewares();

        foreach ($this->contracts as $contract) {
            $methods .= match ($contract) {
                CodeContract::class                                         => $this->getFilledCodes() . "\n",
                InvalidateCodeContract::class                               => $this->getStub('test.method_invalid_data_code') . "\n",
                InvalidParametersCodeContract::class                        => $this->getFilledInvalidParametersCodes() . "\n",
                InvalidParametersContract::class, ParametersContract::class => $this->getFilledParameters($contract) . "\n",
                JobContract::class                                          => $this->getStub('test.method_jobs') . "\n",
                MockContract::class                                         => $this->getStub('test.method_mockAction') . "\n",
                NotificationContract::class                                 => $this->getStub('test.method_notifications') . "\n",
                SeedContract::class                                         => $this->getStub('test.method_seed') . "\n",
                ValidateContract::class, InvalidateContract::class          => $this->getFilledValidData($contract),
                default                                                     => '',
            };
        }

        return trim($methods, "\n");
    }

    /**
     * Возвращает методы получения маршрута и промежуточного ПО
     *
     * @throws FileNotFoundException
     */
    protected function getRouteMiddlewares(): string
    {
        $stub = $this->getStub('test.method_route_middleware');

        $result = '';
        $middlewares = explode(', ', trim($this->middlewares, ','));

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
     *
     * @throws FileNotFoundException
     */
    protected function getFilledCodes(): string
    {
        $stub = $this->getStub('test.method_codes');

        $result = '';

        foreach (ConfigHelper::get($this->group, $this->configKey, 'users') as $user => $guard) {
            $result .= "'$user' => " . ConfigHelper::get($this->group, $this->configKey, "codes.valid.$user") . ",$this->character";
        }

        return $this->stubReplace(
            ['{{ default_codes }}'],
            [rtrim($result, ",$this->character")],
            $stub
        );
    }

    /**
     * Возвращает метод с передачей параметров
     *
     * @throws FileNotFoundException
     */
    protected function getFilledParameters(string $contract): string
    {
        $stub = $contract === ParametersContract::class
            ? $this->getStub('test.method_parameters')
            : $this->getStub('test.method_invalid_parameters');

        $result = '';

        if ($this->route_name) {
            $uri = Route::getRoutes()->getByName($this->route_name)->uri();

            foreach (RouteHelper::getParameters($uri) as $parameter) {
                $parameter = str_replace(['{', '}'], '', $parameter);

                $result .= "'$parameter' => ''" . ",$this->character";
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
     *
     * @throws FileNotFoundException
     */
    protected function getFilledInvalidParametersCodes(): string
    {
        $stub = $this->getStub('test.method_invalid_parameters_codes');

        $result = '';

        foreach (ConfigHelper::get($this->group, $this->configKey, 'users') as $user => $guard) {
            $result .= "'$user' => " . ConfigHelper::get($this->group, $this->configKey, 'codes.invalid.parameters') . ",$this->character";
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
     * @throws ReflectionException|FileNotFoundException
     */
    protected function getFilledValidData(string $contract): string
    {
        $stub = $contract === ValidateContract::class
            ? $this->getStub('test.method_validate')
            : $this->getStub('test.method_invalidate');

        $route = Route::getRoutes()->getByName($this->route_name);
        ReflectionHelper::setActionController($route);

        if (!empty(ReflectionHelper::$actionController)) {
            $keys = ReflectionHelper::getFormRequestArrayKeys($route);
        }

        $result = '';

        foreach ($keys ?? [] as $key) {
            $result .= "'$key' => '',$this->character";
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
        $this->path = "tests{$this->ds}Feature$this->ds" . $this->prefix .
            str($this->className)->replace(['\\', '/'], $this->ds)->value() . '.php';
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