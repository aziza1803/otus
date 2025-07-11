<?php

namespace UserTypes;

use Bitrix\Main\UserField\Types\StringFormattedType;
use CUserTypeManager;

class FormatTelegramLink extends StringFormattedType
{
    public const
        USER_TYPE_ID = 'telegram_string_formatted_link',
        RENDER_COMPONENT = 'otus:main.field.stringformatted';

    // public const USER_TYPE_ID = 'telegram_string_formatted_link';

    public static function getDescription(): array {
        return [
            'DESCRIPTION' => 'Телеграм ссылка',
            'BASE_TYPE' => CUserTypeManager::BASE_TYPE_STRING,
        ];
    }

    public static function getDbColumnType(): string
    {
        return 'text';
    }

}