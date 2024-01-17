<?php

namespace Fillincode\Tests\Traits;

use Fillincode\Tests\Interfaces\ParametersInterface;
use Fillincode\Tests\Interfaces\ValidateInterface;

trait GetDataTrait
{
    /**
     * Проверяется, реализует ли текущий класс, для которого выполняется вызов, интерфейс ValidateInterface.
     */
    protected function checkValidateInterface(): bool
    {
        return in_array(
            ValidateInterface::class,
            class_implements(static::class),
            true
        );
    }

    /**
     * Проверяется, реализует ли текущий класс, для которого выполняется вызов, интерфейс ParametersInterface.
     */
    protected function checkParametersInterface(): bool
    {
        return in_array(
            ParametersInterface::class,
            class_implements(static::class),
            true
        );
    }

    /**
     * Возвращает валидные данные для запроса
     */
    protected function getValidDataToRequest(): array
    {
        return $this->checkValidateInterface() ? $this->getValidData() : [];
    }

    /**
     * Возвращает невалидные данные для запроса
     */
    protected function getNotValidDataToRequest(): array
    {
        return $this->checkValidateInterface() ? $this->getNotValidData() : [];
    }

    /**
     * Возвращает параметры для запроса
     */
    protected function getParametersToRequest(): array
    {
        return $this->checkParametersInterface() ? $this->getParameters() : [];
    }

    /**
     * Возвращает невалидные параметры
     */
    protected function getInvalidParametersToRequest(): array
    {
        return $this->checkParametersInterface() ? $this->getInvalidParameters() : [];
    }
}
