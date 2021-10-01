<?php
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

loc::loadMessages(__FILE__);

if($APPLICATION->GetGroupRight("recaptcha.v3")>"D"){

    require_once(Loader::getLocal("/modules/recaptcha.v3/prolog.php"));

    $aMenu = array(
        "parent_menu" => "global_menu_services",
        "section" => "recaptcha.v3",
        "sort" => 11, //
        "module_id" => "recaptcha.v3",
        "text" => 'Модуль reCaptcha.V3',
        "title"=> 'Модуль reCaptcha.V3 для дополнительного функционала',
        "icon" => "sys_menu_icon",   // sys_menu_icon  bizproc_menu_icon
        "page_icon" => "sys_menu_icon", // sys_menu_icon  bizproc_menu_icon
        "items_id" => "menu_acs",
        "items" => array(
            array(
                "text" => 'Настройки',
                "title" => 'Настройки модуля',
                "url" => "settings.php?mid=recaptcha.v3&lang=".LANGUAGE_ID,
            ),
            array(
                "text" => 'Логи ответов',
                "title" => 'Логи ответов',
                "url" => "logReCaptchaV3.php?lang=".LANGUAGE_ID,
            ),
        )
    );
    return $aMenu;
}
return false;