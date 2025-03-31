<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FieldTypeEnums extends Enum
{
    const TEXT = 'text';
    const TEXT_AREA = 'text_area';
    const NUMBER = 'number';
    const Date = 'date';
    const SELECT = 'select';
    const RADIO = 'radio';
    const TOGGLE = 'toggle';

    public static function getTypes()
    {
        return [
            self::TEXT => __('cranberry-cookie.customfield.field-type.text'),
            self::TEXT_AREA => __('cranberry-cookie.customfield.field-type.text_area'),
            self::NUMBER => __('cranberry-cookie.customfield.field-type.number'),
            self::SELECT => __('cranberry-cookie.customfield.field-type.select'),
            self::RADIO => __('cranberry-cookie.customfield.field-type.radio'),
            self::Date => __('cranberry-cookie.customfield.field-type.date'),
            self::TOGGLE  => __('cranberry-cookie.customfield.field-type.toggle'),
        ];
    }

    public static function getSchemaFields($fieldType)
    {
        $fields = [
            self::TEXT => 'string',
            self::TEXT_AREA  => 'mediumText',
            self::NUMBER  => 'integer',
            self::SELECT  => 'string',
            self::RADIO  => 'string',
            self::Date  => 'date',
            self::TOGGLE  => 'boolean',
        ];

        return $fields[$fieldType] ?? null;
    }
}
