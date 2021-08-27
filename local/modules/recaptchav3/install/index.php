<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

loc::loadMessages(__FILE__);

Class recaptchav3 extends CModule
{
    var $MODULE_ID = "recaptchav3";
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
        $this->MODULE_NAME = GetMessage("acs_module_name");
        $this->MODULE_DESCRIPTION = GetMessage("acs_module_desc");
    }

    function getPageLocal($page)
    {
        return str_replace('index.php', $page, Loader::getLocal('modules/'.$this->MODULE_ID.'/install/index.php'));
    }

    function InstallDB($arParams = array())
    {
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;

        // Database tables creation
        if(!$DB->Query("SELECT 'x' FROM b_recaptchav3", true)) {
            $SQL = 'CREATE TABLE b_recaptchav3
                    (
                        ID		INT(11)		NOT NULL auto_increment,
                        TIMESTAMP_X	TIMESTAMP	NOT NULL default current_timestamp on update current_timestamp,
                        FORM_ID		INT(11) NULL,
                        FORM_SID	VARCHAR(255) NULL,
                        STATUS		VARCHAR(255)	NULL,
                        USER_IP		VARCHAR(255) NULL,
                        RECAPTCHA	TEXT NULL,
                        PRIMARY KEY (ID)
                    );';
            $this->errors = $DB->Query($SQL, true);
        }

        if($this->errors !== false) {
            $APPLICATION->ThrowException(implode("<br>", $this->errors));
            return false;
        } else {
            return true;
        }
    }

    function UnInstallDB($arParams = array())
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

    function InstallFiles($arParams = array())
    {
        CopyDirFiles($this->getPageLocal('admin'), $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($this->getPageLocal('admin'), $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        return true;
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        // Install
        RegisterModule($this->MODULE_ID);
        $this->InstallDB();
        $this->InstallFiles();
        $APPLICATION->IncludeAdminFile("Установка модуля ".$this->MODULE_ID, $this->getPageLocal('step.php'));
        return true;
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        //
        UnRegisterModule($this->MODULE_ID);
        $this->UnInstallDB();
        $this->UnInstallFiles();
        $APPLICATION->IncludeAdminFile("Деинсталляция модуля ".$this->MODULE_ID, $this->getPageLocal('unstep.php'));
        return true;
    }
}