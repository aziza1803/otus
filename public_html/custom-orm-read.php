<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Query;
use Otus\Orm\BookTable;
use Bitrix\Iblock\Elements\ElementdoctorsTable;
use Bitrix\Iblock\Elements\ElementspecsTable;
use Bitrix\Iblock\Elements\ElementprocedureTable;


if (!Loader::includeModule('iblock'))
{
    return;
}

$q = new Query(BookTable::class);
$q->setSelect([
    'ID',
    'TITLE',
    'YEAR',
    'PUBLISH_DATE',
    'PAGES',
    'DOCTOR_NAME' => 'DOCTOR_RECOMMENDS.NAME',
    // Используем правильный код свойства
    'PROCEDURE_NAME' => 'PROCEDURE.NAME',
]);

$result = $q->exec();

$books = [];
$doctors = [];
$procedures = [];

while ($arItem = $result->fetch())
{
    if (!isset($books[$arItem['ID']])) {
        $books[$arItem['ID']] = [
            'TITLE' => $arItem['TITLE'],
            'YEAR' => $arItem['YEAR'],
            'PUBLISH_DATE' => $arItem['PUBLISH_DATE'],
            'PAGES' => $arItem['PAGES'],
        ];
    }

    $doctors[$arItem['ID']][] = $arItem['DOCTOR_NAME'];

    if (!empty($arItem['PROCEDURE_NAME'])) {
        $procedures[$arItem['ID']][] = $arItem['PROCEDURE_NAME'];
    }
}

foreach ($books as $id => &$book)
{
    if (isset($doctors[$id])) {
        $book['DOCTORS'] = array_unique($doctors[$id]);
    } else {
        $book['DOCTORS'] = [];
    }

    if (isset($procedures[$id])) {
        $book['PROCEDURES'] = array_unique($procedures[$id]);
    } else {
        $book['PROCEDURES'] = [];
    }
}
unset($book);

pr($books);
    

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");