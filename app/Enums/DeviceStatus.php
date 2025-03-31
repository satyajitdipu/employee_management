<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ACTIVE()
 * @method static static INACTIVE()
 */
final class DeviceStatus extends Enum
{
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';

    public static function getStatuses()
    {
        return [
            self::ACTIVE()->value => __('cranberry-cookie::cranberry-cookie.device.status.active'),
            self::INACTIVE()->value => __('cranberry-cookie::cranberry-cookie.device.status.inactive'),
        ];
    }
}
