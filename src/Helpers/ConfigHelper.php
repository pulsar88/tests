<?php

namespace Fillincode\Tests\Helpers;

class ConfigHelper
{
    public static function get(string $group, string $prefix, string $key): mixed
    {
        return config("fillincode-tests.$group.$prefix.$key") ?: config("fillincode-tests.$group.default.$key");
    }
}