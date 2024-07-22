<?php

namespace Fillincode\Tests\Generator\TestGenerator;

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
use Fillincode\Tests\Generator\BaseGenerator;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
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
        protected string $configKey = 'feature',
    )
    {
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

    protected function getPrefix(): string
    {
        $prefix = config("fillincode-tests.$this->configKey.prefix");

        return $prefix
            ? str($prefix)->lower()->ucfirst() . '\\'
            : '';
    }

    /**
     * Возвращает namespace класса
     */
    protected function getNamespace(): string
    {
        if (Str::contains($this->className, '/')) {
            return "Tests\\Feature\\{$this->getPrefix()}" . str($this->className)->beforeLast('/')->replace('/', '\\');
        }

        return "Tests\\Feature\\{$this->getPrefix()}";
    }

    protected function getExtendsClass(): string
    {
        return $this->configKey === 'feature' ? 'BaseFeatureTestCase' : 'BaseMoonshineTestCase';
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
     *
     * @throws ReflectionException|FileNotFoundException
     */
    protected function getMethods(): string
    {
        $methods = $this->getRouteMiddlewares();

        foreach ($this->interfaces as $interface) {
            $methods .= match ($interface) {
                CodeInterface::class => $this->getFilledCodes() . "\n",
                ParametersInterface::class => $this->getFilledParameters() . "\n",
                SeedInterface::class => $this->getStub('test.methods_seed') . "\n",
                MockInterface::class => $this->getStub('test.methods_mock') . "\n",
                ParametersCodeInterface::class => $this->getFilledInvalidParametersCodes() . "\n",
                ValidateInterface::class => $this->getFilledValidData() . "\n",
                NotificationTestInterface::class => $this->getStub('test.methods_notify_check') . "\n",
                JobTestInterface::class => $this->getStub('test.methods_job_check') . "\n",
                default => '',
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
        $stub = $this->getStub('test.methods_route_middleware');

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
        $stub = $this->getStub('test.methods_codes');

        $result = '';

        foreach (config("fillincode-tests.$this->configKey.users") as $user => $guard) {
            $result .= "'$user' => " . config("fillincode-tests.$this->configKey.codes.$user") . ",$this->character";
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
    protected function getFilledParameters(): string
    {
        $stub = $this->getStub('test.methods_parameters');

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
        $stub = $this->getStub('test.methods_parameters_codes');

        $result = '';

        foreach (config("fillincode-tests.$this->configKey.users") as $user => $guard) {
            $result .= "'$user' => " . config("fillincode-tests.$this->configKey.invalid.parameters") . ",$this->character";
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
    protected function getFilledValidData(): string
    {
        $stub = $this->getStub('test.methods_validate');

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
        $this->path = "tests{$this->ds}Feature$this->ds" . str_replace('\\', $this->ds, $this->getPrefix()) .
            str($this->className)->replace('/', $this->ds)->value() . '.php';
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