<?php

namespace Fillincode\Tests\Helpers;

class ConfigHelper
{
    public static function get(string $group, ?string $prefix, string $key): mixed
    {
        if ($group === 'admin_panel') {
            return config("fillincode-tests.admin_panel.$key");
        }

        return config("fillincode-tests.$group.$prefix.$key") ?: config("fillincode-tests.$group.default.$key");
    }
}