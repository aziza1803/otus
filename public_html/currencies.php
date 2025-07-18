<?php require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(""); ?><?$APPLICATION->IncludeComponent(
	"otus:currencies",
	"",
	Array(
		"CACHE_TIME" => "86400",
		"CACHE_TYPE" => "A",
		"CURRENCY_BASE" => "RUB",
		"RATE_DAY" => "",
		"SHOW_CB" => "N",
		"arrCURRENCY_FROM" => array("RUB","USD","EUR")
	)
);?><?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>