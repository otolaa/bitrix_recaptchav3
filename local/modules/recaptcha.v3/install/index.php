<?php
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;

loc::loadMessages(__FILE__);

Class recaptcha_v3 extends CModule
{
    public $MODULE_ID = "recaptcha.v3";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__.'/version.php');
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("v3_module_name");
        $this->MODULE_DESCRIPTION = Loc::getMessage("v3_module_desc");
        $this->PARTNER_NAME = 'Alex Noodles';
        $this->PARTNER_URI = '//github.com/otolaa/bitrix_recaptchav3';
    }

    public function getPageLocal($page)
    {
        return str_replace('index.php', $page, Loader::getLocal('modules/'.$this->MODULE_ID.'/install/index.php'));
    }

    public function getStringText($obj)
    {
        return is_array($obj)?implode('<br>', $obj):$obj;
    }

    public function InstallDB($arParams = array())
    {
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;

        // Database tables creation
        $SQL = 'CREATE TABLE IF NOT EXISTS b_recaptchav3
                (
                    ID		INT(11)		NOT NULL auto_increment,
                    TIMESTAMP_X	TIMESTAMP	NOT NULL default current_timestamp on update current_timestamp,
                    FORM_ID		INT(11) NULL,
                    FORM_SID	VARCHAR(255) NULL,
                    STATUS		VARCHAR(255)	NULL,
                    USER_IP		VARCHAR(255) NULL,
                    RECAPTCHA	TEXT NULL,
                    PRIMARY KEY (ID)
                ) CHARACTER SET utf8 COLLATE utf8_unicode_ci;';
        $this->errors = $DB->Query($SQL, true);


        if($this->errors !== false) {
            $APPLICATION->ThrowException($this->getStringText($this->errors));
            return false;
        } else {
            return true;
        }
    }

    public function UnInstallDB($arParams = array())
    {
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;

        if(!array_key_exists("save_tables", $arParams) || ($arParams["save_tables"] != "Y")) {
            $this->errors = $DB->Query('DROP TABLE if exists b_recaptchav3', false);
        }

        if($this->errors !== false) {
            $APPLICATION->ThrowException($this->getStringText($this->errors));
            return false;
        }

        return true;
    }

    public function InstallFiles($arParams = array())
    {
        CopyDirFiles($this->getPageLocal('admin'), $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        CopyDirFiles($this->getPageLocal('pages/index_form.php'), $_SERVER["DOCUMENT_ROOT"]."/index_form.php");
        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles($this->getPageLocal('admin'), $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        DeleteDirFiles($this->getPageLocal('pages/index_form.php'), $_SERVER["DOCUMENT_ROOT"]."/index_form.php");
        return true;
    }

    public function DoInstall()
    {
        global $APPLICATION;
        // Install
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallDB();
        $this->InstallFiles();
        Option::set($this->MODULE_ID, 'RECAPTCHA_POLITICS', Loc::getMessage('RECAPTCHA_POLITICS'));
        Option::set($this->MODULE_ID, 'RECAPTCHA_ERROR', Loc::getMessage('RECAPTCHA_ERROR'));
        Option::set($this->MODULE_ID, 'RECAPTCHA_ERROR_SCORE', Loc::getMessage('RECAPTCHA_ERROR_SCORE'));
        Option::set($this->MODULE_ID, 'RECAPTCHA_SCORE', '0.5');
        Option::set($this->MODULE_ID, 'RECAPTCHA_LOG', 'Y');
        $APPLICATION->IncludeAdminFile("Установка модуля ".$this->MODULE_ID, $this->getPageLocal('step.php'));
        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION;
        //
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->UnInstallDB();
        $this->UnInstallFiles();
        Option::delete($this->MODULE_ID); // Will remove all module variables
        $APPLICATION->IncludeAdminFile("Деинсталляция модуля ".$this->MODULE_ID, $this->getPageLocal('unstep.php'));
        return true;
    }
}