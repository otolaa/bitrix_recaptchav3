<?php
IncludeModuleLangFile(__FILE__);
static $MODULE_ID = 'recaptchav3';
//
$arClasses=array(
    'reCaptcha'=>'classes/general/reCaptcha.php',
);

CModule::AddAutoloadClasses("recaptchav3", $arClasses);