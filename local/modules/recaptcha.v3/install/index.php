<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

loc::loadMessages(__FILE__);

Class recaptcha_v3 extends CModule
{
    var $MODULE_ID = "recaptcha.v3";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__.'/version.php');
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("v3_module_name");
        $this->MODULE_DESCRIPTION = GetMessage("v3_module_desc");
        $this->PARTNER_NAME = 'saitovik';
        $this->PARTNER_URI = 'http://saitovik.com/';
    }

    public function getPageLocal($page)
    {
        return str_replace('index.php', $page, Loader::getLocal('modules/'.$this->MODULE_ID.'/install/index.php'));
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
            $APPLICATION->ThrowException(implode("<br>", $this->errors));
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
            $APPLICATION->ThrowException(implode("<br>", $this->errors));
            return false;
        }

        return true;
    }

    public function InstallFiles($arParams = array())
    {
        CopyDirFiles($this->getPageLocal('admin'), $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles($this->getPageLocal('admin'), $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        return true;
    }

    public function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        // Install
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallDB();
        $this->InstallFiles();
        $APPLICATION->IncludeAdminFile("Установка модуля ".$this->MODULE_ID, $this->getPageLocal('step.php'));
        return true;
    }

    public function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        //
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->UnInstallDB();
        $this->UnInstallFiles();
        $APPLICATION->IncludeAdminFile("Деинсталляция модуля ".$this->MODULE_ID, $this->getPageLocal('unstep.php'));
        return true;
    }
}