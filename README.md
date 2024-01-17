# Fillincode-tests

Быстрая генерация тестов без необходимости писать всю логику тестирования вручную.

Возможности пакета:
1. Пакет выполняет тестирования middleware маршрута,
2. Запросы от каждого пользователя, определенного в конфигурации,
3. Тестирование запросов с передачей данных,
4. Тестирование запросов с передачей параметров адресной строки,
5. Проверка кода ответа для каждого теста,
6. Заполнение данными БД перед выполнением запроса,
7. Создание насмешек

## Installation

```shell
composer require fillincode/tests
```

Публикация конфигурации

```php
php artisan vendor:publish --provider="Fillincode\Tests\TestServiceProvider"
```

## Config

Конфигурация находится в файле config/fillincode_tests.php

Необходимо указать дефолтные коды ответа для пользователей, 
а также для невалидных данных и параметров адресной строки

```php
[
    'user' => 200,
    'admin' => 200,
    'guest' => 401,

    'invalid_data' => 422,
    'invalid_parameters' => 404,
];
```

Необходимо указать, какие пользователи есть в системе и какие guards проверяют их авторизацию.
Для guest не нужно указывать guard

```php
'users' => [
    'guest',
    'user' => 'Passport',
    'admin' => 'web',
],
```

## Commands

Сгенерирует базовый класс для функциональных тестов, который содержит основную логику тестов

```shell
php artisan f-tests:init
```

Генерирует класс теста. С помощью этой же команды можно выбрать интерфейсы, который реализует класс. 
Методы будет автоматически добавлены в класс

```shell
php artisan make:f-test
```

## Пример первоначальной настройки пакета

Необходимо в конфигурации указать, какие есть пользователи в системе, дефолтные коды ответа для этих пользователей.

```php
return [
    'web_user' => 200,
    'api_user' => 200,
    'admin' => 200,
    'guest' => 401,

    'invalid_data' => 422,
    'invalid_parameters' => 404,

    'has_fillincode_swagger_parser' => true,

    'users' => [
        'guest',
        'web_user' => 'web',
        'api_user' => 'Passport'
        'admin' => 'Moonshine',
    ],
];
```

После чего выполнить команду для генерации класса. 
В этом классе будут реализованы методы тестирования от каждого пользователя.

```shell
php artisan f-tests:init
```

Затем реализовать методы либо в BaseFeatureTest, либо в TestCase для получения этих пользователей.

```php
use App\Models\User;

    ````

/**
 * Получения пользователя web_user
 * 
 * @return User
 */
public function getWebUser(): User
{
    return User::whereEmail('web_user@gmail.com')->first();
}

/**
 * Получения пользователя api_user
 * 
 * @return User
 */
public function getApiUser(): User
{
    return User::whereEmail('api_user@gmail.com')->first();
}

/**
 * Получения пользователя admin
 * 
 * @return User
 */
public function getAdmin(): User
{
    return User::whereEmail('admin@gmail.com')->first();
}
```

## Возможности пакета для тестов

### Изменения дефолтных кодов для текущего маршрута

1. Необходимо имплементировать интерфейс Fillincode/Tests/Interfaces/CodeInterface
2. Реализовать метод getCodes

```php
use Fillincode\Tests\Interfaces\CodeInterface;
use Tests\Feature\BaseFeatureTest;

class ExampleTest extends BaseFeatureTest implements CodeInterface
{
    /**
     * {@inheritDoc}
     */
    public function getCodes(): array
    {
        return [
            'guest' => 401,
            'web_user' => 200,
            'api_user' => 401,
            'admin' => 401,
        ];       
    }
}
```

### Изменения дефолтных кодов для передачи невалидных параметров в адресной строке

1. Необходимо имплементировать интерфейс Fillincode/Tests/Interfaces/ParametersCodeInterface
2. Реализовать метод getCodesForInvalidParameters

```php
use Fillincode\Tests\Interfaces\ParametersCodeInterface;
use Tests\Feature\BaseFeatureTest;

class ExampleTest extends BaseFeatureTest implements ParametersCodeInterface
{
    /**
     * {@inheritDoc}
     */
    public function getCodesForInvalidParameters(): array
    {
        return [
            'guest' => 404,
            'web_user' => 404,
            'api_user' => 404,
            'admin' => 404,
        ];       
    }
}
```

### Передача параметров во время тестирования

1. Необходимо имплементировать интерфейс Fillincode/Tests/Interfaces/ParametersInterface
2. Реализовать методы getParameters и getInvalidParameters

Первый метод должен вернуть корректные параметры адресной строки, второй метод должен вернуть некорректные параметры адресной строки

```php
use Fillincode\Tests\Interfaces\ParametersInterface;
use Tests\Feature\BaseFeatureTest;

class ExampleTest extends BaseFeatureTest implements ParametersInterface
{
    /**
     * {@inheritDoc}
     */
    public function getParameters(): array
    {
        return [
            'project' => Project::factory()->create(['status' => 'active'])
        ];       
    }
    
    /**
     * {@inheritDoc}
     */
    public function getInvalidParameters(): array
    {
        return [
            'project' => Project::factory()->create(['status' => 'draft']) 
        ];       
    }
}
```

### Валидация данных

1. Необходимо имплементировать интерфейс Fillincode/Tests/Interfaces/ValidateInterface
2. Реализовать методы getValidData и getNotValidData. 

Первый метод должен вернуть валидные данные, второй метод должен вернуть невалидные данные

```php
use Fillincode\Tests\Interfaces\ValidateInterface;
use Tests\Feature\BaseFeatureTest;

class ExampleTest extends BaseFeatureTest implements ValidateInterface
{
    /**
     * {@inheritDoc}
     */
    public function getValidData(): array
    {
        return [
            'name' => 'test_name',
            'age' => 12,
        ];       
    }
    
    /**
     * {@inheritDoc}
     */
    public function getNotValidData(): array
    {
        return [
            'name' => 'q',
            'age' => null,
        ];       
    }
}
```

### Заполнение БД данными перед выполнением каждого запроса

1. Необходимо имплементировать интерфейс Fillincode/Tests/Interfaces/FakeInterface
2. Реализовать метод faker. В этом методе нужно будет выполнить логику заполнения данными БД

```php
use Fillincode\Tests\Interfaces\FakeInterface;
use Tests\Feature\BaseFeatureTest;

class ExampleTest extends BaseFeatureTest implements FakeInterface
{
    /**
     * {@inheritDoc}
     */
    public function faker(): void
    {
        Project::factory(10)->create(['web_user_id' => $this->getWebUser()->id]);
    }
}
```

### Создание фейкового хранилища данных

1. Необходимо имплементировать интерфейс Fillincode/Tests/Interfaces/FakeStorageInterface

Для теста, который реализует этот интерфейс автоматически будет создано фейковое public хранилище

```php
use Fillincode\Tests\Interfaces\FakeStorageInterface;
use Tests\Feature\BaseFeatureTest;

class ExampleTest extends BaseFeatureTest implements FakeStorageInterface
{
    
}
```

### Насмешка в тестах

1. Необходимо имплементировать интерфейс Fillincode/Tests/Interfaces/MockInterface
2. Реализовать метод getMockAction

```php
use Fillincode\Tests\Interfaces\MockInterface;
use Tests\Feature\BaseFeatureTest;

class ExampleTest extends BaseFeatureTest implements MockInterface
{
    /**
     * {@inheritDoc}
     */
    public function getMockAction(): void
    {
        Http::fake();
    }
}
```

### Если пакет работает в связке с пакетом Fillincode/Swagger и есть маршруты, которые не нужно документировать

1. Необходимо имплементировать интерфейс Fillincode/Tests/Interfaces/DocIgnoreInterface 

    ```php
    use Fillincode\Tests\Interfaces\DocIgnoreInterface;
    use Tests\Feature\BaseFeatureTest;
    
    class ExampleTest extends BaseFeatureTest implements DocIgnoreInterface
    {
        
    }
    ```
2. В классе BaseFeatureTest в метод callRouteAction добавить

    ```php
    if (! $this->checkDocIgnoreInterface()) {
        (new TestParser())->makeAutoDoc($testResponse);
    }
    ```

## Пример использования пакета

Для минимального тестирования достаточно создать класс, 
который будет наследником класса BaseFeatureTest и реализовать методы getRouteName и getMiddleware

```php
use Tests\Feature\BaseFeatureTest;

class ExampleTest extends BaseFeatureTest
{
    /**
     * {@inheritDoc}
     */
    public function getRouteName(): string
    {
        return 'api.user.update';
    }
    
    /**
     * {@inheritDoc}
     */
    public function getMiddleware(): array
    {
        return ['api', 'auth'];
    }
}
```

Пример класса, который реализует все возможности пакета. 

Возможности класса: 

1. Выполнит запросы от всех пользователей, которые определенны в конфигурации пакета, 
2. Выполнит тесты с отправкой параметров адресной строки
3. Передаст данные для валидации, 
4. Создаст фейковое хранилище файлов, 
5. Подделает фасад Http, 
6. Проигнорирует документирование результатов тестирования 
7. Заполнит базу 10 проектами

```php
use Tests\Feature\BaseFeatureTest;
use Fillincode\Tests\Interfaces\CodeInterface;
use Fillincode\Tests\Interfaces\ParametersCodeInterface;
use Fillincode\Tests\Interfaces\ParametersInterface;
use Fillincode\Tests\Interfaces\ValidateInterface;
use Fillincode\Tests\Interfaces\FakeInterface;
use Fillincode\Tests\Interfaces\FakeStorageInterface;
use Fillincode\Tests\Interfaces\MockInterface;
use Fillincode\Tests\Interfaces\DocIgnoreInterface;

class ExampleTest extends BaseFeatureTest implements CodeInterface, ParametersCodeInterface, ParametersInterface, ValidateInterface, FakeInterface, FakeStorageInterface, MockInterface, DocIgnoreInterface
{
    /**
     * {@inheritDoc}
     */
    public function getRouteName(): string
    {
        return 'api.user.update';
    }
    
    /**
     * {@inheritDoc}
     */
    public function getMiddleware(): array
    {
        return ['api', 'auth'];
    }
    
    /**
     * {@inheritDoc}
     */
    public function getCodes(): array
    {
        return [
            'guest' => 401,
            'web_user' => 200,
            'api_user' => 401,
            'admin' => 401,
        ];       
    }
    
    /**
     * {@inheritDoc}
     */
    public function getCodesForInvalidParameters(): array
    {
        return [
            'guest' => 404,
            'web_user' => 404,
            'api_user' => 404,
            'admin' => 404,
        ];       
    }
    
    /**
     * {@inheritDoc}
     */
    public function getParameters(): array
    {
        return [
            'project' => Project::factory()->create(['status' => 'active'])
        ];       
    }
    
    /**
     * {@inheritDoc}
     */
    public function getInvalidParameters(): array
    {
        return [
            'project' => Project::factory()->create(['status' => 'draft']) 
        ];       
    }
    
    /**
     * {@inheritDoc}
     */
    public function getValidData(): array
    {
        return [
            'name' => 'test_name',
            'age' => 12,
        ];       
    }
    
    /**
     * {@inheritDoc}
     */
    public function getNotValidData(): array
    {
        return [
            'name' => 'q',
            'age' => null,
        ];       
    }
    
    /**
     * {@inheritDoc}
     */
    public function faker(): void
    {
        Project::factory(10)->create(['web_user_id' => $this->getWebUser()->id]);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getMockAction(): void
    {
        Http::fake();
    }
}
```