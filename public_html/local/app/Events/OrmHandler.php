<?php

namespace Events;

use Bitrix\Main\EventManager;
use Bitrix\Main\Event;
use Bitrix\Main\ORM\EventResult;

class OrmHandler
{
       public static function onTimelineBeforeAdd(Event $event) 
    {
        $result = new EventResult();
        $arParams = $event->getParameters();
        
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/logTBA.txt', 'PARAMS: '.var_export($arParams, true).PHP_EOL, FILE_APPEND);

        $changedFields = ['COMMENT' => 'Otus ' . date('d.m.Y H:i:s')];

        $result->modifyFields($changedFields);

        return $result;
    }
}
