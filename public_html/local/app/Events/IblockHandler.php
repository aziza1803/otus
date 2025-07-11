<?php

namespace Events;

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

class IblockHandler
{
    public static $restrictHour = 21;

    public static function onElementBeforeAdd(&$arFields)
    {
        if ($arFields["IBLOCK_ID"] != 5)
            return $arFields;

        $arFields['NAME'] = 'Изменен в обработчике события ' . date('d.m.Y H:i:s');
    }

    public static function onElementAfterUpdate(&$arFields)
    {
//        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/logIAU.txt', 'FIELDS: '.var_export($arFields, true).PHP_EOL, FILE_APPEND);

        if (!Loader::includeModule('im')) // отправляем пользователю сообщение в чат
            return;

        $messageId = \CIMMessage::Add([
            'TO_USER_ID' => 1,
            'FROM_USER_ID' => 3, // Анна Делова
            'MESSAGE' => 'Привет'.' '.$arFields['NAME'],
        ]);

//        if (!$messageId) {
//            if ($exception = $GLOBALS['APPLICATION']->GetException()) {
//                file_put_contents($_SERVER['DOCUMENT_ROOT'].'/logIAU.txt', 'ERROR: '.var_export($exception->GetString(), true).PHP_EOL, FILE_APPEND);
//            } else {
//                file_put_contents($_SERVER['DOCUMENT_ROOT'].'/logIAU.txt', 'UNKNOWN_ERROR'.PHP_EOL, FILE_APPEND);
//            }
//        }
    }

    public static function onElementBeforeDelete(&$id)
    {
        if (date('H') == self::$restrictHour)
        {
            global $APPLICATION;
            $APPLICATION->throwException("Нельзя удалять в ".self::$restrictHour." часов");
            return false;
        }
    }
}
