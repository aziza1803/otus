<?php
namespace Events\DuplicateCounter;

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

// $eventManager = EventManager::getInstance();
// //$eventManager->addEventHandlerCompatible('main', 'OnProlog', ['Otus\Js\Handler', 'duplicateCounter']);
// $eventManager->addEventHandlerCompatible('main', 'OnEpilog', ['Otus\Js\Handler', 'duplicateCounter']);

class Handler
{

    /**
     * Duplicate counter in deals
     * @return void
     * @throws LoaderException
     */
    public static function duplicateCounter()
    {
        if (!$GLOBALS['USER']->IsAuthorized())
            return;

        $request = Application::getInstance()->getContext()->getRequest();
        $curUri = parse_url($request->getRequestUri());
        $curUri = $curUri['path'];
        $wantedPathReg = '/\/crm\/deal\/details\/(\d+)/';
        preg_match($wantedPathReg, $curUri, $matches);
        $dealId = intval($matches[1]);
        if ($dealId)
        {
            self::showCounter($dealId);
        }
    }

    /**
     * Show counter
     * @param int $dealId - deal id
     * @return void
     */
    public static function showCounter(int $dealId)
    {
        $dir = str_replace(Application::getDocumentRoot(), '', __DIR__);

        $asset = Asset::getInstance();
//        $asset->addJs($dir . '/script.js');

        \CJSCore::RegisterExt('otusJsExt', [
            'js' => $dir . '/script.js',
            'css' => $dir . '/style.css',
            'lang' => $dir . '/lang/' . LANGUAGE_ID . '/message.php',
            'rel' => ['core']
        ]);
        \CJSCore::Init('otusJsExt');


        $counter = $dealId;

        $arParams = ['COUNTER' => $counter];
        $jsParams = \CUtil::PhpToJSObject($arParams, true);
        $asset->addString('<script>
            BX.ready(function()
            {
                BX.message({
                    BTN_TEXT: "'.Loc::getMessage('BTN_TEXT').'"
                })
                new BX.MPP.DuplicateCounter('.$jsParams.');
            })
        </script>');
    }
}