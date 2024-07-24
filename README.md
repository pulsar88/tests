# Fillincode-tests

Быстрая генерация тестов без необходимости писать всю логику тестирования вручную.

Возможности пакета:

1. Пакет выполняет тестирования middlewares маршрута;
2. Запросы от каждого пользователя, определенного в конфигурации;
3. Создание нескольких групп тестов для разных групп маршрутов;
4. Тестирование запросов с передачей данных;
5. Тестирование запросов с передачей параметров адресной строки;
6. Проверка кода ответа для каждого теста;
7. Заполнение данными БД перед выполнением запроса;
8. Создание насмешек;
9. Проверка отправки уведомлений;
10. Проверка передачи задачи в очередь.

## Установка

```shell
composer require fillincode/tests
```

Публикация конфигурации

```php
php artisan vendor:publish --provider="Fillincode\Tests\TestServiceProvider"
```

## Конфигурация

Конфигурация находится в файле config/fillincode-tests.php

Конфигурация содержит два блока:

1. Блок с внешней частью сервиса (app)

   ```php
      'app' => [
        'default' => [
            'users' => [
                'guest' => '',
                'api_user' => 'Passport',
                'web_user' => 'web',
            ],

            'codes' => [
                'valid' => [
                    'guest' => 401,
                    'user' => 200,
                    'web_user' => 200,
                ],

                'invalid' => [
                    'data' => 422,
                    'parameters' => 404
                ]
            ],
        ],

        'api' => [
            'invalid' => [
                'data' => 404,
                'parameters' => 401
            ]
        ],

        'web' => [
            'invalid' => [
                'data' => 404,
                'parameters' => 401
            ]
        ]
    ],
   ```

   Блок default определяет конфигурацию для всех групп. При создании группы, можно переопределить конфигурацию, добавив
   свои параметры внутри группы.

   В примере создаются две группы (api и web), каждая группа переопределяет параметры невалидных кодов ответов. Все
   остальное эти группы получат из default конфигурации.

   В этой конфигурации можно определить какие пользователи есть в группе тестов и как их нужно авторизовать, наиболее
   частые коды ответов для каждого пользователя и наиболее часты коды ответов для невалидных данных.

2. Блок с админ-панелью (на данный момент поддерживается только Moonshine)

   ```php
        'admin_panel' => [
           'name' => 'moonshine',
   
           'users' => [
               'guest' => '',
               'admin' => 'moonshine',
           ],
   
           'codes' => [
               'valid' => [
                   'guest' => 401,
                   'admin' => 200,
               ],
   
               'invalid' => [
                   'invalid_validate' => 422,
                   'invalid_parameters' => 404
               ],
           ],
       ]
   ```
   В этом блоке определяется конфигурация тестов для админ панели. На момент написания документации поддерживается
   только Moonshine. Если у вас другая админ-панель или ее нет, то укажите в поле name none

## Консольные команды

Сгенерирует базовые классы для функциональных тестов. Эти классы будут содержать основную логику тестов. Создаются в
директории test/Feature

```shell
php artisan fillincode-test:init
```

Генерирует класс теста. С помощью этой же команды можно выбрать интерфейсы, который реализует класс.
Методы будет автоматически добавлены в класс. Команда принимает необязательный параметр, в котором можно передать группу
для тестов (иначе берется первая группа). Либо можно передать флаг -A|--admin для генерации теста админ-панели

```shell
php artisan fillincode-test:make-test
```

Покрывает тестами все ресурсы админ панели Moonshine.

```shell
php artisan fillincode-test:moonshine-test
```

## Пример первоначальной настройки пакета

Необходимо в конфигурации указать, какие есть пользователи в системе, дефолтные коды ответа для этих пользователей.

```php
return [
    'app' => [
        'default' => [
            'users' => [
                'guest' => '',
                'api_user' => 'Passport',
                'web_user' => 'web',
            ],

            'codes' => [
                'valid' => [
                    'guest' => 401,
                    'user' => 200,
                    'web_user' => 200,
                ],

                'invalid' => [
                    'data' => 422,
                    'parameters' => 404,
                ],
            ],
        ],

        'api' => [
            'users' => [
                'guest' => '',
                'job_seeker' => 'Passport',
                'company_admin' => 'Passport',
                'company_curator' => 'Passport',
            ],

            'codes' => [
                'valid' => [
                    'guest' => 401,
                    'job_seeker' => 200,
                    'company_admin' => 200,
                    'company_curator' => 200,
                ],
            ],
        ],
    ],

    'admin_panel' => [
        'name' => 'moonshine',

        'users' => [
            'admin' => 'moonshine',
        ],

        'codes' => [
            'valid' => [
                'admin' => 200,
            ],

            'invalid' => [
                'data' => 422,
                'parameters' => 404,
            ],
        ],
    ],
];
```

В этой конфигурации создается одна группа для внешней части и указывается, что админ-панель - Moonshine. После создания
конфигурации необходимо выполнить команду для генерации базовых классов

```shell
php artisan fillincode-test:init

```

Затем реализовать методы либо в базовых классах, либо в TestCase для получения этих пользователей. Метод должен иметь
префикс get затем имя пользователя, также метод должен быть в верблюжьем регистре

```php

    ````

    public function getJobSeeker(): User
    {
        return User::query()->where('email', 'test_seeker@mail.ru')->first();
    }

    public function getCompanyAdmin(): User
    {
        return User::query()->where('email', 'test@mail.ru')->first();
    }

    public function getCompanyCurator(): User
    {
        return User::query()->where('email', 'test_curator@mail.ru')->first();
    }

    public function getAdmin(): MoonshineUser
    {
        return MoonshineUser::query()->first();
    }
```

## Возможности пакета для тестов

### Изменения дефолтных кодов для текущего маршрута

1. Необходимо имплементировать интерфейс /Fillincode/Tests/Contracts/CodeContract
2. Реализовать метод codes

```php
use Fillincode\Tests\Contracts\CodeContract;
use Tests\Feature\BaseFeatureTestCase;

class ExampleTest extends BaseFeatureTestCase implements CodeContract
{
    /**
     * {@inheritDoc}
     */
    public function codes(string $user_key): array
    {
        return [
            'guest' => 401,
            'job_seeker' => 200,
            'company_admin' => 401,
            'company_curator' => 401,
        ];       
    }
}
```

### Изменения дефолтного кода для передачи невалидных параметров в адресной строке

1. Необходимо имплементировать интерфейс Fillincode/Tests/Interfaces/ParametersCodeInterface
2. Реализовать метод codesForInvalidParameters

```php
use Fillincode\Tests\Contracts\InvalidParametersCodeContract;
use Tests\Feature\BaseFeatureTestCase;

class ExampleTest extends BaseFeatureTestCase implements InvalidParametersCodeContract
{
    /**
     * {@inheritDoc}
     */
    public function codesForInvalidParameters(): array
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

### Изменения дефолтного кода для передачи невалидных данных

1. Необходимо имплементировать интерфейс Fillincode/Tests/Contracts/InvalidateCodeContract
2. Реализовать метод invalidDataCode

```php
use Fillincode\Tests\Contracts\InvalidateCodeContract;
use Tests\Feature\BaseFeatureTestCase;

class ExampleTest extends BaseFeatureTestCase implements InvalidateCodeContract
{
    /**
     * {@inheritDoc}
     */
    public function invalidDataCode(string $user_key): int
    {
        return 404;
    }
}
```

### Передача валидных параметров во время тестирования

1. Необходимо имплементировать интерфейс Fillincode/Tests/Contracts/ParametersContract
2. Реализовать методы parameters

```php
use Fillincode\Tests\Contracts\ParametersContract;
use Tests\Feature\BaseFeatureTestCase;

class ExampleTest extends BaseFeatureTestCase implements ParametersContract
{
    /**
     * {@inheritDoc}
     */
    public function parameters(string $user_key): array
    {
        return [
            'project' => Project::factory()->create(['status' => 'active'])
        ];       
    }
}
```

### Передача невалидных параметров во время тестирования

1. Необходимо имплементировать интерфейс Fillincode/Tests/Contracts/InvalidParametersContract
2. Реализовать методы parameters

```php
use Fillincode\Tests\Contracts\InvalidParametersContract;
use Tests\Feature\BaseFeatureTestCase;

class ExampleTest extends BaseFeatureTestCase implements InvalidParametersContract
{
    /**
     * {@inheritDoc}
     */
    public function invalidParameters(string $user_key): array
    {
        return [
            'project' => Project::factory()->create(['status' => 'draft'])
        ];       
    }
}
```

### Передача валидных данных

1. Необходимо имплементировать интерфейс Fillincode/Tests/Contracts/ValidateContract
2. Реализовать метод validData

```php
use Fillincode\Tests\Contracts\ValidateContract;
use Tests\Feature\BaseFeatureTestCase;

class ExampleTest extends BaseFeatureTestCase implements ValidateContract
{
    /**
     * {@inheritDoc}
     */
    public function validData(): array
    {
        return [
            'name' => 'test_name',
            'age' => 12,
        ];       
    }
}
```

### Передача невалидных данных

1. Необходимо имплементировать интерфейс Fillincode/Tests/Contracts/InvalidateContract
2. Реализовать метод invalidData

```php
use Fillincode\Tests\Contracts\InvalidateContract;
use Tests\Feature\BaseFeatureTestCase;

class ExampleTest extends BaseFeatureTestCase implements InvalidateContract
{
    /**
     * {@inheritDoc}
     */
    public function invalidData(): array
    {
        return [
            'name' => 12,
            'age' => 'qwerty',
        ];       
    }
}
```

### Заполнение БД данными перед выполнением каждого запроса

1. Необходимо имплементировать интерфейс Fillincode/Tests/Contracts/SeedContract
2. Реализовать метод dbSeed. В этом методе нужно будет выполнить логику заполнения данными БД

```php
use Fillincode\Tests\Contracts\SeedContract;
use Tests\Feature\BaseFeatureTestCase;

class ExampleTest extends BaseFeatureTestCase implements SeedContract
{
    /**
     * {@inheritDoc}
     */
    public function dbSeed(string $user_key): void
    {
        Project::factory(10)->create(['web_user_id' => $this->getWebUser()->id]);
    }
}
```

### Создание фейкового хранилища данных

1. Необходимо имплементировать интерфейс Fillincode/Tests/Contracts/FakeStorageContract

Для теста, который реализует этот интерфейс автоматически будет создано фейковое public хранилище

```php
use Fillincode\Tests\Contracts\FakeStorageContract;
use Tests\Feature\BaseFeatureTestCase;

class ExampleTest extends BaseFeatureTestCase implements FakeStorageContract
{
    
}
```

### Насмешка в тестах

1. Необходимо имплементировать интерфейс Fillincode/Tests/Contracts/MockContract
2. Реализовать метод getMockAction

```php
use Fillincode\Tests\Contracts\MockContract;
use Tests\Feature\BaseFeatureTestCase;

class ExampleTest extends BaseFeatureTestCase implements MockContract
{
    /**
     * {@inheritDoc}
     */
    public function mockAction(): void
    {
        Http::fake();
    }
}
```

### Проверка отправки уведомлений

1. Необходимо имплементировать интерфейс Fillincode\Tests\Contracts\NotificationContract
2. Реализовать метод notifications. Метод принимает тип пользователя, от которого выполняется запрос

Автоматически будет вызван метод fake() фасада Notification, поэтому эту логику не нужно будет реализовывать в методе
notifications

```php

use Tests\Feature\BaseFeatureTestCase;
use Fillincode\Tests\Contracts\NotificationContract;

class ExampleTest extends BaseFeatureTestCase implements NotificationContract
{
    /**
     * {@inheritDoc}
     */
    public function notifications(string $user_key): void
    {
        if ($user_key === 'user') {
            return;
        }

        Notification::assertSentToTimes(
            User::query()->where('email', 'example@example.com')->first(),
            WelcomeNotify::class,
        );
    }
}
```

### Проверка отправки задач в очередь

### Проверка отправки уведомлений

1. Необходимо имплементировать интерфейс Fillincode\Tests\Contracts\JobContract
2. Реализовать метод jobs. Метод принимает тип пользователя, от которого выполняется запрос

Автоматически будет вызван метод fake() фасада Queue, поэтому эту логику не нужно будет реализовывать в методе jobs

```php

use Tests\Feature\BaseFeatureTestCase;
use Fillincode\Tests\Contracts\JobContract;

class ExampleTest extends BaseFeatureTestCase implements JobContract
{
    /**
     * {@inheritDoc}
     */
    public function jobs(string $user_key): void
    {
        if ($user_key !== 'user') {
            return;
        }

        Queue::assertPushed(SendingNotifyAboutNewUserNews::class, 1);
    }
}
```

### Если пакет работает в связке с пакетом Fillincode/Swagger и есть маршруты, которые не нужно документировать

1. Необходимо имплементировать интерфейс Fillincode/Tests/Contracts/DocIgnoreInterface

    ```php
    use Fillincode\Tests\Contracts\DocIgnoreInterface;
    use Tests\Feature\BaseFeatureTestCase;
    
    class ExampleTest extends BaseFeatureTestCase implements DocIgnoreInterface
    {
        
    }
    ```
2. В базовом классе в метод callRouteAction добавить

    ```php
    if (! $this->checkDocIgnoreInterface()) {
        (new TestParser())->makeAutoDoc($testResponse);
    }
    ```

## Пример использования пакета

Для минимального тестирования достаточно создать класс,
который будет наследником базового класса и реализовать методы getRouteName и getMiddleware

```php
use Tests\Feature\BaseFeatureTestCase;

class ExampleTest extends BaseFeatureTestCase
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
2. Проверит миделвары маршрута,
3. Выполнит запрос с передачей валидных/невалидных данных и параметров маршрута,
4. Изменит стандартные коды ответа при отправке невалидных данных/параметров,
5. Создаст двух пользователей перед выполнением запроса,
6. Проверит отправку задач в очередь,
7. Проверит отправку уведомлений,

```php
namespace Tests\Feature\Api\Qwerty;

use Tests\Feature\BaseApiTestCase;
use Fillincode\Tests\Contracts\CodeContract;
use Fillincode\Tests\Contracts\ValidateContract;
use Fillincode\Tests\Contracts\InvalidateContract;
use Fillincode\Tests\Contracts\InvalidateCodeContract;
use Fillincode\Tests\Contracts\ParametersContract;
use Fillincode\Tests\Contracts\InvalidParametersContract;
use Fillincode\Tests\Contracts\InvalidParametersCodeContract;
use Fillincode\Tests\Contracts\SeedContract;
use Fillincode\Tests\Contracts\FakeStorageContract;
use Fillincode\Tests\Contracts\JobContract;
use Fillincode\Tests\Contracts\MockContract;
use Fillincode\Tests\Contracts\NotificationContract;
use Fillincode\Tests\Contracts\DocIgnoreContract;

class CaseTest extends BaseApiTestCase implements CodeContract, ValidateContract, InvalidateContract, InvalidateCodeContract, ParametersContract, InvalidParametersContract, InvalidParametersCodeContract, SeedContract, FakeStorageContract, JobContract, MockContract, NotificationContract, DocIgnoreContract
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
        return ['api', 'auth.api'];
    }

    /**
     * {@inheritDoc}
     */
    public function codes(string $user_key): array
    {
        return [
            'guest' => 401,
            'job_seeker' => 200,
            'company_admin' => 200,
            'company_curator' => 200
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function validData(string $user_key): array
    {
        return [
            'logo' => '',
            'email' => '',
            'name' => '',
            'surname' => '',
            'last_name' => '',
            'gender' => '',
            'birthday' => '',
            'description' => '',
            'site' => '',
            'attached_files' => '',
            'attached_files.*' => ''
        ];
    }
    /**
    * {@inheritDoc}
    */
    public function invalidData(string $user_key): array
    {
        return [
            'logo' => '',
            'email' => '',
            'name' => '',
            'surname' => '',
            'last_name' => '',
            'gender' => '',
            'birthday' => '',
            'description' => '',
            'site' => '',
            'attached_files' => '',
            'attached_files.*' => ''
        ];
    }
    
    /**
     * {@inheritDoc}
     */
    public function invalidDataCode(string $user_key): int
    {
        return 422;
    }
    
    /**
     * {@inheritDoc}
     */
    public function parameters(string $user_key): array
    {
        return [
            'user' => User::query()->first(),
        ];
    }
    
    /**
     * {@inheritDoc}
     */
    public function invalidParameters(string $user_key): array
    {
        return [
            'user' => null,
        ];
    }
    
    /**
     * {@inheritDoc}
     */
    public function codesForInvalidParameters(): array
    {
        return [
            'guest' => 404,
            'job_seeker' => 404,
            'company_admin' => 404,
            'company_curator' => 404
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function dbSeed(string $user_key): void
    {
        User::factory(2)->create();
    }

    /**
     * {@inheritDoc}
     */
    public function jobs(string $user_key): void
    {
        Queue::assertPushed(ShipOrder::class, 2);
    }
    
    /**
     * {@inheritDoc}
     */
    public function mockAction(string $user_key): void
    {
        Http::fake();
    }

    /**
     * {@inheritDoc}
     */
    public function notifications(string $user_key): void
    {
        Notification::assertCount(3);
    }
}
```