<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class EmployeeStatus extends Enum
{
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
    const TERMINATED = 'terminated';
    const RESIGNED = 'resigned';
    const ABSCONDED = 'absconded';
    const SUSPENDED = 'suspended';
    const RETIRED = 'retired';
    const LAID_OFF = 'laid_off';
    const DECEASED = 'deceased';
    const ON_SABBATICAL = 'on_sabbatical';

    public static function getStatuses()
    {
        return [
            self::ACTIVE()->value => __('cranberry-cookie::cranberry-cookie.employee.status.active'),
            self::INACTIVE()->value => __('cranberry-cookie::cranberry-cookie.employee.status.inactive'),
            self::TERMINATED()->value => __('cranberry-cookie::cranberry-cookie.employee.status.terminated'),
            self::RESIGNED()->value => __('cranberry-cookie::cranberry-cookie.employee.status.resigned'),
            self::ABSCONDED()->value => __('cranberry-cookie::cranberry-cookie.employee.status.absconded'),
            self::SUSPENDED()->value => __('cranberry-cookie::cranberry-cookie.employee.status.suspended'),
            self::RETIRED()->value => __('cranberry-cookie::cranberry-cookie.employee.status.retired'),
            self::LAID_OFF()->value => __('cranberry-cookie::cranberry-cookie.employee.status.laid_off'),
            self::DECEASED()->value => __('cranberry-cookie::cranberry-cookie.employee.status.deceased'),
            self::ON_SABBATICAL()->value => __('cranberry-cookie::cranberry-cookie.employee.status.on_sabbatical'),
        ];
    }

    public static function getStatusKeys()
    {
        return [
            self::ACTIVE,
            self::INACTIVE,
            self::TERMINATED,
            self::RESIGNED,
            self::ABSCONDED,
            self::SUSPENDED,
            self::RETIRED,
            self::LAID_OFF,
            self::DECEASED,
            self::ON_SABBATICAL,
        ];
    }

    public static function getExitStatusKeys()
    {
        return [
            self::TERMINATED,
            self::RESIGNED,
            self::ABSCONDED,
            self::RETIRED,
            self::LAID_OFF,
            self::DECEASED,
        ];
    }
}
