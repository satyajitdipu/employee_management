<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CookieSettings extends Settings
{
    public array $webhooks;

    public static function group(): string
    {
        return 'cookie';
    }
}
