<?php

namespace Fillincode\Tests\Helpers;

class RouteHelper
{
    /**
     * Возвращает параметры маршрута
     */
    public static function getParameters(string $uri): array
    {
        $parameters = [];

        preg_match_all('/\{\w+}/', $uri, $parameters);

        return $parameters[0] ?? [];
    }
}