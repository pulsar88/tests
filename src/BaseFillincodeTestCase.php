<?php

namespace Fillincode\Tests;

use Fillincode\Tests\Contracts\CodeContract;
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
use Fillincode\Tests\Helpers\ConfigHelper;
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
     * Проверка имплементации интерфейса
     */
    protected function checkContract(string $contract): bool
    {
        return in_array($contract, class_implements(static::class), true);
    }

    /**
     * Получение кода ответа из массива
     */
    protected function getCodeFromArray(array $codes, string $user_key): int
    {
        return $codes[$user_key];
    }

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
     * Возвращает код ответа
     */
    protected function getCode(string $user_key): int
    {
        return $this->checkContract(CodeContract::class)
            ? $this->getCodeFromArray($this->codes(), $user_key)
            : ConfigHelper::get($this->group, $this->prefix, "codes.valid.$user_key");
    }

    /**
     * Возвращает код ответа для невалидных параметров
     */
    protected function getInvalidParametersCode(string $user_key): int
    {
        return $this->checkContract(InvalidParametersCodeContract::class)
            ? $this->getCodeFromArray($this->codesForInvalidParameters(), $user_key)
            : ConfigHelper::get($this->group, $this->prefix, 'codes.invalid.parameters');
    }

    /**
     * Метод получения кода для невалидных данных
     */
    protected function getInvalidDataCode(string $user_key): int
    {
        $code = $this->getCode($user_key);

        $def_code = $this->checkContract(InvalidateCodeContract::class)
            ? $this->invalidDataCode($user_key)
            : ConfigHelper::get($this->group, $this->prefix, "codes.invalid.data");

        return $code >= 200 && $code < 399 ? $def_code : $code;
    }

    /**
     * Возвращает валидные данные для запроса
     */
    protected function getValidData(string $user_key): array
    {
        return $this->checkContract(ValidateContract::class)
            ? $this->validData($user_key)
            : [];
    }

    /**
     * Возвращает невалидные данные для запроса
     */
    protected function getInvalidData(string $user_key): array
    {
        return $this->checkContract(InvalidateContract::class)
            ? $this->invalidData($user_key)
            : [];
    }

    /**
     * Возвращает параметры для запроса
     */
    protected function getParameters(string $user_key): array
    {
        return $this->checkContract(ParametersContract::class)
            ? $this->parameters($user_key)
            : [];
    }

    /**
     * Возвращает невалидные параметры
     */
    protected function getInvalidParameters(string $user_key): array
    {
        return $this->checkContract(InvalidParametersContract::class)
            ? $this->invalidParameters($user_key)
            : [];
    }

    /**
     * Вызывает метод mock, если тест реализует интерфейс MockInterface
     */
    protected function callJobsMethod(string $user_key): void
    {
        if ($this->checkContract(JobContract::class)) {
            $this->jobs($user_key);
        }
    }

    /**
     * Вызывает метод mock, если тест реализует интерфейс MockInterface
     */
    protected function callMockMethod(string $user_key): void
    {
        if ($this->checkContract(FakeStorageContract::class)) {
            Storage::fake('public');
        }

        if ($this->checkContract(MockContract::class)) {
            $this->mockAction($user_key);
        }
    }

    /**
     * Вызывает метод для проверки уведомлений
     */
    public function callNotifyMethod(string $user_key): void
    {
        if ($this->checkContract(NotificationContract::class)) {
            $this->notifications($user_key);
        }
    }

    /**
     * Вызывает метод для заполнения фейковыми данными
     */
    public function callSeedMethod(string $user_key): void
    {
        if ($this->checkContract(SeedContract::class)) {
            $this->seeder($user_key);
        }
    }
}