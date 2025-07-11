<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");



$logFile =  $_SERVER["DOCUMENT_ROOT"] . '/otus/debug.log';

$datetime = date('Y-m-d H:i:s');

$entry = "Debug call at: {$datetime}\n";

file_put_contents($logFile, $entry, FILE_APPEND);

echo "Время записано: {$datetime}";




?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>



