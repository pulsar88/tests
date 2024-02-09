<?php

namespace Fillincode\Tests\Helpers;

use ReflectionClass;
use ReflectionException;
use Illuminate\Routing\Route;
use ReflectionParameter;
use stdClass;
use Illuminate\Foundation\Http\FormRequest;

class ReflectionHelper
{
    /**
     * Контроллер маршрута
     */
    public static string $actionController;

    /**
     * Параметры метода контролера
     */
    public static array $methodParameters;


    /**
     * Записывает контроллер маршрута в свойство класса
     */
    public static function setActionController(Route $route): void
    {
        self::$actionController = $route->getAction()['controller'];
    }

    /**
     * Получает ключи массива метода rule класса FormRequest
     *
     * @throws ReflectionException
     */
    public static function getFormRequestArrayKeys(Route $route): array
    {
        self::setMethodParameters();

        foreach (self::$methodParameters as $parameter) {
            if (!$formRequestReflectionClass = self::getFormRequestReflectionClass($parameter)) {
                continue;
            }

            $instance = $formRequestReflectionClass->newInstance();

            foreach (RouteHelper::getParameters($route->uri()) as $route_parameter) {
                $property_name = str_replace(['{', '}'], '', $route_parameter);
                $instance->$property_name = self::setProperty();
            }

            try {
                $keys = array_keys(
                    $formRequestReflectionClass->getMethod('rules')->invoke($instance)
                );
            } catch (\TypeError $exception) {
            }


            break;
        }

        return $keys ?? [];
    }

    /**
     * Сохраняет методы параметра в свойство класса
     *
     * @throws ReflectionException
     */
    protected static function setMethodParameters(): void
    {
        $action_array = explode('@', self::$actionController);

        $controllerClass = new ReflectionClass($action_array[0]);

        self::$methodParameters = $controllerClass->getMethod($action_array[1])->getParameters();
    }

    /**
     * Возвращает ReflectionClass для FormRequest маршрута
     */
    protected static function getFormRequestReflectionClass(ReflectionParameter $parameter): ReflectionClass|null
    {
        $paramName = $parameter->getType()->getName();

        if (!class_exists($paramName)) {
            return null;
        }

        $reflectionClass = new ReflectionClass($paramName);

        if (!$reflectionClass->isSubclassOf(FormRequest::class)) {
            return null;
        }

        return $reflectionClass;
    }

    /**
     * Создает объект StdClass.
     *
     * Метод нужен для того, чтобы если в методе rule есть проверка на уникальность записи из параметра маршрута,
     * не происходила ошибка в момент получения id этой записи
     */
    protected static function setProperty(): stdClass
    {
        $object = new stdClass();
        $object->id = 1;

        return $object;
    }
}