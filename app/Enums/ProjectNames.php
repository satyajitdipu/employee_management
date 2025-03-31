<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static CranberryPunch()
 * @method static static CranberryPie()
 * @method static static CranberryMuffin()
 */
final class ProjectNames extends Enum
{
    const CranberryPunch = "cranberry_punch";
    const CranberryPie = "cranberry_pie";
    const CranberryMuffin = "cranberry_muffin";

    public static function getProjectNames()
    {
        return [
            self::CranberryPunch()->value => __('cranberry-cookie::cranberry-cookie.project-name.cranberry_punch'),
            self::CranberryPie()->value => __('cranberry-cookie::cranberry-cookie.project-name.cranberry_pie'),
            self::CranberryMuffin()->value => __('cranberry-cookie::cranberry-cookie.project-name.cranberry_muffin'),
        ];
    }
}
