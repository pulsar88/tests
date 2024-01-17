<?php

namespace Fillincode\Tests\Generator;

use Illuminate\Support\Facades\File;

class BaseFeatureTestGenerator extends BaseGenerator
{
    /**
     * Типы пользователей
     *
     * @var array
     */
    protected array $users;

    public function __construct()
    {
        $this->users = config('fillincode_tests.users');
    }

    /**
     * Генерация BaseFeatureTest класса
     *
     * @return void
     */
    public function generate(): void
    {
        $stub = $this->getStub('base_feature_test');

        $searches = [
            '{{ route_actions }}',
            '{{ test_from_auth_users }}',
            '{{ test_from_guest }}',
            '{{ send_not_valid_data }}',
            '{{ send_not_valid_data_from_guest }}',
            '{{ send_invalid_parameters }}',
            '{{ send_invalid_parameters_from_guest }}',
            '{{ use_test_parser }}',
            '{{ test_parser }}',
        ];

        $replaces = [
            $this->getRouteActions(),
            $this->getTestFromAuthUsers(),
            $this->getTestFromGuest(),
            $this->getSendNotValidData(),
            $this->getSendNotValidDataFromGuest(),
            $this->getSendInvalidParameters(),
            $this->getSendInvalidParametersFromGuest(),
            $this->getUseTestParser(),
            $this->getTestParserCode(),
        ];

        $stub = $this->stubReplace($searches, $replaces, $stub);

        $this->saveClass($stub);
    }

    /**
     * Возвращает методы для авторизации под разными пользователями
     *
     * @return string
     */
    protected function getRouteActions(): string
    {
        $stub = $this->getStub('route_action');

        $result = '';

        foreach ($this->users as $user => $auth_guard) {
            if ($user !== 'guest') {
                $auth = '$this->be($user, \'' . $auth_guard . '\');';

                if (str($auth_guard)->upper()->value() === 'PASSPORT') {
                    $auth = 'Passport::actingAs($user, [\'*\']);';
                }

                $result .= $this->stubReplace(
                    ['{{ name }}', '{{ auth }}',],
                    [str($user)->studly(), $auth],
                    $stub) . "\n";
            }
        }

        return rtrim($result, "\n");
    }

    /**
     * Создает методы тестирования из-под авторизованного пользователя
     */
    protected function getTestFromAuthUsers(): string
    {
        $stub = $this->getStub('test_from_auth_user');

        $result = '';

        foreach ($this->users as $user => $auth_guard) {
            if ($user !== 'guest') {
                $result .= $this->stubReplace(
                    ['{{ studly_name }}', '{{ name }}'],
                    [str($user)->studly(), $user],
                    $stub
                );
            }
        }

        return rtrim($result, "\n");
    }

    /**
     * Создает метод тестирования из-под гостя
     */
    protected function getTestFromGuest(): string
    {
        if (!array_key_exists('guest', $this->users)) {
            return '';
        }

        return $this->getStub('test_from_guest');
    }

    /**
     * Создает метод для отправки невалидных данных
     */
    protected function getSendNotValidData(): string
    {
        $stub = $this->getStub('send_not_valid_data');

        $result = '';

        foreach ($this->users as $user => $auth_guard) {
            if ($user !== 'guest') {
                $result .= $this->stubReplace(
                    ['{{ studly_name }}', '{{ name }}'],
                    [str($user)->studly(), $user],
                    $stub
                ) . "\n";
            }
        }

        return rtrim($result, "\n");
    }

    /**
     * Создает метод для отправки невалидных данных для гостя
     */
    protected function getSendNotValidDataFromGuest(): string
    {
        if (!array_key_exists('guest', $this->users)) {
            return '';
        }

        return $this->getStub('send_not_valid_data_from_guest');
    }

    /**
     * Создает метод для отправки невалидных параметров адресной строки
     */
    protected function getSendInvalidParameters(): string
    {
        $stub = $this->getStub('send_invalid_parameters');

        $result = '';

        foreach ($this->users as $user => $auth_guard) {
            if ($user !== 'guest') {
                $result .= $this->stubReplace(
                    ['{{ studly_name }}', '{{ name }}'],
                    [str($user)->studly(), $user,],
                    $stub
                );
            }
        }

        return rtrim($result, "\n");
    }

    /**
     * Создает метод для отправки невалидных параметров адресной строки из-под гостя
     */
    protected function getSendInvalidParametersFromGuest(): string
    {
        if (!array_key_exists('guest', $this->users)) {
            return '';
        }

        return $this->getStub('send_invalid_parameters_from_guest');
    }

    /**
     * @return string
     */
    protected function getUseTestParser(): string
    {
        if (!class_exists('Fillincode\Swagger\Parser\TestParser')) {
            return '';
        }

        return $this->getStub('use_test_parser') . "\n";
    }

    /**
     * @return string
     */
    protected function getTestParserCode(): string
    {
        if (!class_exists('Fillincode\Swagger\Parser\TestParser')) {
            return '';
        }

        return "\n" . $this->getStub('test_parser') . "\n";
    }

    /**
     * Сохранение файла
     */
    protected function saveClass(string $stub): void
    {
        File::put(
            'tests' . DIRECTORY_SEPARATOR . 'Feature' . DIRECTORY_SEPARATOR . 'BaseFeatureTest.php',
            $stub
        );
    }
}