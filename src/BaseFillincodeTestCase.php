<?php

namespace Fillincode\Tests;

use Fillincode\Tests\Interfaces\CodeInterface;
use Fillincode\Tests\Interfaces\FakeStorageInterface;
use Fillincode\Tests\Interfaces\JobTestInterface;
use Fillincode\Tests\Interfaces\MockInterface;
use Fillincode\Tests\Interfaces\NotificationTestInterface;
use Fillincode\Tests\Interfaces\ParametersCodeInterface;
use Fillincode\Tests\Interfaces\ParametersInterface;
use Fillincode\Tests\Interfaces\SeedInterface;
use Fillincode\Tests\Interfaces\ValidateCodeInterface;
use Fillincode\Tests\Interfaces\ValidateInterface;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

abstract class BaseFillincodeTestCase extends TestCase
{
    /**
     * Получение имени маршрута
     */
    abstract public function getRouteName(): string;

    /**
     * Возвращает промежуточное ПО маршрута
     */
    abstract public function getMiddleware(): array;

    /**
     * Получение объекта маршрута по имени маршрута
     */
    protected function getRouteByName(): Route
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()
            ->getByName($this->getRouteName());

        if (! $route) {
            $this->fail("Маршрут с именем \"{$this->getRouteName()}\" не найден!");
        }

        return $route;
    }

    /**
     * Проверка, содержит ли маршрут переданное промежуточное ПО
     */
    protected function assertRouteContainsMiddleware(array $names): void
    {
        $route = $this->getRouteByName();

        foreach ($names as $name) {
            $this->assertContains(
                $name,
                $route->middleware(),
                "Маршрут не содержит промежуточного ПО \"$name\""
            );
        }

    }

    /**
     * Проверка, содержит ли маршрут только переданное промежуточное ПО
     */
    protected function assertRouteHasExactMiddleware(array $names): void
    {
        $route = $this->getRouteByName();

        $this->assertRouteContainsMiddleware($names);

        $this->assertCount(
            count($names),
            $route->middleware(),
            'Маршрут содержит иное количество промежуточного ПО.'
        );
    }

    /**
     * Проверка имплементации интерфейса
     */
    protected function hasInterface(string $interface): bool
    {
        return in_array(
            $interface,
            class_implements(static::class),
            true
        );
    }

    /**
     * Возвращает код ответа
     */
    protected function getCodeForRequest(string $user): int
    {
        if ($this->hasInterface(CodeInterface::class)) {
            $codes = $this->getCodes();

            return $codes[$user];
        }

        return config("fillincode-tests.$this->configKey.codes.$user");
    }

    /**
     * Возвращает код ответа для невалидных параметров
     */
    protected function getCodeForInvalidParameters(string $user): int
    {
        if ($this->hasInterface(ParametersCodeInterface::class)) {
            $codes = $this->getCodesForInvalidParameters();
            $code = $codes[$user];

            return $code >= 200 && $code < 399 ? config("fillincode-tests.$this->configKey.invalid.parameters") : $code;
        }

        return config("fillincode-tests.$this->configKey.invalid.parameters");
    }

    /**
     * Метод получения кода для невалидных данных
     */
    protected function getCodeForInvalidData(string $user): int
    {
        $code = $this->getCodeForRequest($user);

        $def_code = $this->hasInterface(ValidateCodeInterface::class)
            ? $this->invalidParamCode()
            : config("fillincode-tests.$this->configKey.invalid.data");

        return $code >= 200 && $code < 399 ? $def_code : $code;
    }

    /**
     * Возвращает валидные данные для запроса
     */
    protected function getValidDataToRequest(): array
    {
        return $this->hasInterface(ValidateInterface::class) ? $this->getValidData() : [];
    }

    /**
     * Возвращает невалидные данные для запроса
     */
    protected function getNotValidDataToRequest(): array
    {
        return $this->hasInterface(ValidateInterface::class) ? $this->getNotValidData() : [];
    }

    /**
     * Возвращает параметры для запроса
     */
    protected function getParametersToRequest(): array
    {
        return $this->hasInterface(ParametersInterface::class) ? $this->getParameters() : [];
    }

    /**
     * Возвращает невалидные параметры
     */
    protected function getInvalidParametersToRequest(): array
    {
        return $this->hasInterface(ParametersInterface::class) ? $this->getInvalidParameters() : [];
    }

    /**
     * Вызывает метод mock, если тест реализует интерфейс MockInterface
     */
    protected function callJobCheckMethod(string $user_type): void
    {
        if ($this->hasInterface(JobTestInterface::class)) {
            $this->jobCheck($user_type);
        }
    }

    /**
     * Вызывает метод mock, если тест реализует интерфейс MockInterface
     */
    protected function callMockMethod(): void
    {
        if ($this->hasInterface(FakeStorageInterface::class)) {
            Storage::fake('public');
        }

        if ($this->hasInterface(MockInterface::class)) {
            $this->getMockAction();
        }
    }

    /**
     * Вызывает метод для проверки уведомлений
     */
    public function callNotifyTestMethod(string $user_type): void
    {
        if ($this->hasInterface(NotificationTestInterface::class)) {
            $this->notifyCheck($user_type);
        }
    }

    /**
     * Вызывает метод для заполнения фейковыми данными
     */
    public function callSeedMethod(): void
    {
        if ($this->hasInterface(SeedInterface::class)) {
            $this->dbSeed();
        }
    }
}