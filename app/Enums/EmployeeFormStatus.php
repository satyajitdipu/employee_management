<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class EmployeeFormStatus extends Enum
{
    const DRAFT = 'draft';
    const PUBLISHED = 'published';

    public static function getStatuses()
    {
        return [
            self::DRAFT()->value => __('employee_form.status.draft'),
            self::PUBLISHED()->value => __('employee_form.status.published'),
        ];
    }

    public static function getStatusColors()
    {
        return [
            'warning' => fn ($state): bool => (string)$state === self::DRAFT()->value,
            'success' => fn ($state): bool => (string)$state === self::PUBLISHED()->value,
        ];
    }
}
