<?php

namespace Fillincode\Tests\Traits;

use Fillincode\Tests\Interfaces\CodeInterface;
use Fillincode\Tests\Interfaces\ParametersCodeInterface;

trait GetCodesTrait
{
    /**
     * Проверяется, реализует ли текущий класс, для которого выполняется вызов, интерфейс CodeInterface.
     */
    protected function checkCodeInterface(): bool
    {
        return in_array(
            CodeInterface::class,
            class_implements(static::class),
            true
        );
    }

    /**
     * Проверяется, реализует ли текущий класс, для которого выполняется вызов, интерфейс ParametersCodeInterface.
     */
    protected function checkParametersCodeInterface(): bool
    {
        return in_array(
            ParametersCodeInterface::class,
            class_implements(static::class),
            true
        );
    }

    /**
     * Возвращает код ответа
     */
    protected function getCodeForRequest(string $user): int
    {
        if ($this->checkCodeInterface()) {
            $codes = $this->getCodes();

            return $codes[$user];
        }

        return config('fillincode_tests.' . $user);
    }

    /**
     * Возвращает код ответа для невалидных параметров
     */
    protected function getCodeForInvalidParameters(string $user): int
    {
        if ($this->checkParametersCodeInterface()) {
            $codes = $this->getCodesForInvalidParameters();
            $code = $codes[$user];

            return $code >= 200 && $code < 300 ? config('fillincode_tests.invalid_parameters') : $code;
        }

        return config('fillincode_tests.invalid_parameters');
    }

    /**
     * Метод получения кода для невалидных данных
     */
    protected function getCodeForInvalidData(string $user): int
    {
        $code = $this->getCodeForRequest($user);

        return $code >= 200 && $code < 300 ? config('fillincode_tests.invalid_data') : $code;
    }
}
