<?php

namespace Fillincode\Tests\Generator\BaseClassGenerator;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Fillincode\Tests\Generator\BaseGenerator;

class BaseTestCaseGenerator extends BaseGenerator
{
    /**
     * Типы пользователей
     */
    protected array $users;

    public function __construct(
        protected string $configKey
    )
    {
        $this->users = config("fillincode-tests.$this->configKey.users");
    }

    /**
     * Генерация BaseFeatureTest класса
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function generate(): void
    {
        $stub = $this->getStub('base_class.class');

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
            '{{ class_name }}',
            '{{ config_key }}',
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
            $this->getClassName(),
            $this->configKey,
        ];

        $stub = $this->stubReplace($searches, $replaces, $stub);

        $this->saveClass($stub);
    }

    /**
     * Возвращает методы для авторизации под разными пользователями
     *
     * @return string
     * @throws FileNotFoundException
     */
    protected function getRouteActions(): string
    {
        $stub = $this->getStub('base_class.route_action');

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
     *
     * @throws FileNotFoundException
     */
    protected function getTestFromAuthUsers(): string
    {
        $stub = $this->getStub('base_class.test_from_auth_user');

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
     *
     * @throws FileNotFoundException
     */
    protected function getTestFromGuest(): string
    {
        if (!array_key_exists('guest', $this->users)) {
            return '';
        }

        return $this->getStub('base_class.test_from_guest');
    }

    /**
     * Создает метод для отправки невалидных данных
     *
     * @throws FileNotFoundException
     */
    protected function getSendNotValidData(): string
    {
        $stub = $this->getStub('base_class.send_not_valid_data');

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
     *
     * @throws FileNotFoundException
     */
    protected function getSendNotValidDataFromGuest(): string
    {
        if (!array_key_exists('guest', $this->users)) {
            return '';
        }

        return $this->getStub('base_class.send_not_valid_data_from_guest');
    }

    /**
     * Создает метод для отправки невалидных параметров адресной строки
     *
     * @throws FileNotFoundException
     */
    protected function getSendInvalidParameters(): string
    {
        $stub = $this->getStub('base_class.send_invalid_parameters');

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
     *
     * @throws FileNotFoundException
     */
    protected function getSendInvalidParametersFromGuest(): string
    {
        if (!array_key_exists('guest', $this->users)) {
            return '';
        }

        return $this->getStub('base_class.send_invalid_parameters_from_guest');
    }

    /**
     * @return string
     * @throws FileNotFoundException
     */
    protected function getUseTestParser(): string
    {
        if (!class_exists('Fillincode\Swagger\Parser\TestParser')) {
            return '';
        }

        return $this->getStub('base_class.use_test_parser') . "\n";
    }

    /**
     * @return string
     * @throws FileNotFoundException
     */
    protected function getTestParserCode(): string
    {
        if (!class_exists('Fillincode\Swagger\Parser\TestParser')) {
            return '';
        }

        return "\n" . $this->getStub('base_class.test_parser') . "\n";
    }

    protected function getClassName(): string
    {
        return $this->configKey === 'feature' ? 'BaseFeatureTestCase' : 'BaseMoonshineTestCase';
    }

    /**
     * Сохранение файла
     */
    protected function saveClass(string $stub): void
    {
        File::put(
            "tests{$this->ds}Feature$this->ds{$this->getClassName()}.php", $stub
        );
    }
}