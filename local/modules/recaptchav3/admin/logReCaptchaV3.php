<?php
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
require_once(Loader::getLocal("modules/recaptchav3/include.php")); // инициализация модуля
require_once(Loader::getLocal("/modules/recaptchav3/prolog.php")); // пролог модуля

// подключим языковой файл
Loc::loadMessages(__FILE__);
//
if(!Loader::includeModule("recaptchav3")) return;

// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("recaptchav3");
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "b_recaptchav3"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

/* ФИЛЬТР */

// проверку значений фильтра для удобства вынесем в отдельную функцию
function CheckFilter()
{
    global $FilterArr, $lAdmin;
    foreach ($FilterArr as $f) global $$f;

    // В данном случае проверять нечего.
    // В общем случае нужно проверять значения переменных $find_имя
    // и в случае возниконовения ошибки передавать ее обработчику
    // посредством $lAdmin->AddFilterError('текст_ошибки').

    return count($lAdmin->arFilterErrors)==0; // если ошибки есть, вернем false;
}

// опишем элементы фильтра
$FilterArr = Array(
    "find",
    "find_type",
    "find_ID",
);

// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);

// если все значения фильтра корректны, обработаем его
if (CheckFilter())
{
    // создадим массив фильтрации для выборки
    $arFilter = Array(
        "ID"    => ($find!="" && $find_type == "id"? $find:$find_ID),
    );
}

/*  КОНЕЦ ФИЛЬТРА */


/*  ОБРАБОТКА ДЕЙСТВИЙ НАД ЭЛЕМЕНТАМИ СПИСКА */

// сохранение отредактированных элементов
if($lAdmin->EditAction() && $POST_RIGHT=="W")
{
    // пройдем по списку переданных элементов
    foreach($FIELDS as $ID=>$arFields)
    {

        if(!$lAdmin->IsUpdated($ID))continue;
        //
        $SET = array();
        if(count($arFields)>0){
            // параметры POST летащие и т.д.
            foreach ($arFields as $k=>$fieldsValue) {
                $SET[] = "cat.`".$k."` = '".$fieldsValue."'";
            }
            // сохраним изменения каждого элемента
        }
    }
}

// обработка одиночных и групповых действий
if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W")
{
    // если выбрано "Для всех элементов"
    if($_REQUEST['action_target']=='selected')
    {
        /**/
        global $DB;
        $DB->StartTransaction();
        $SQL = "DELETE FROM `b_recaptchav3`";
        $dbResult = $DB->Query($SQL, true);
        $DB->Commit();
        if(!$dbResult){
            $lAdmin->AddGroupError("Ошибка удаления");
        }else{
            CAdminMessage::ShowMessage(array("MESSAGE"=>"Удалены ", "TYPE"=>"OK"));
        }
    }

    // пройдем по списку элементов
    foreach($arID as $ID)
    {
        if(strlen($ID)<=0)continue;
        $ID = IntVal($ID);

        // для каждого элемента совершим требуемое действие
        switch($_REQUEST['action'])
        {
            // удаление
            case "delete":
                /**/
                global $DB;
                $DB->StartTransaction();
                $SQL = "DELETE FROM `b_recaptchav3` WHERE `b_recaptchav3`.`ID` =  ".$ID;
                $dbResult = $DB->Query($SQL, true);
                $DB->Commit();
                if(!$dbResult){
                    $lAdmin->AddGroupError("Ошибка удаления", $ID);
                }else{
                    CAdminMessage::ShowMessage(array("MESSAGE"=>"Удалено ID ".$ID, "TYPE"=>"OK"));
                }
                //$lAdmin->AddGroupError("ОПЕРАЦИИ НАД ЭЛЕМЕНТАМИ НЕ ПРЕДУСМОТРЕННЫ", $ID);
                break;

            // активация/деактивация
            case "activate":
            case "deactivate":
                $active = ($_REQUEST['action']=="activate"?"Y":"N");
                // обновляем и т.д.
                $lAdmin->AddGroupError("ОПЕРАЦИИ НАД ЭЛЕМЕНТАМИ НЕ ПРЕДУСМОТРЕННЫ", $ID);
                break;
            //  модерация
            /**/
        }
    }
} // end GroupAction

/* Конец Обработки действия над элементами в таблитце */

/* ВЫБОРКА ЭЛЕМЕНТОВ СПИСКА */

// выберем список параметров
$arFilter = array_filter($arFilter, 'strlen');  // удаляем Null
$rsData = \Local\ReCaptchaV3\ReCaptcha::GetList($arFilter, [$by=>$order], $bShowAll = false);

// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);

// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint("Параметры"));

/* ПОДГОТОВКА СПИСКА К ВЫВОДУ */
$lAdmin->AddHeaders([
    ["id"=>"ID", "content"=>"ID", "sort"=>"ID", "align"=>"right", "default"=>true],
    ["id"=>"TIMESTAMP_X", "content"=>"Дата", "sort"=>"TIMESTAMP_X", "default"=>true],
    ["id"=>"FORM_ID", "content"=>"ID формы", "sort"=>"FORM_ID", "default"=>true],
    ["id"=>"FORM_SID","content"=>"SID формы","sort"=>"FORM_SID","default"=>true],
    ["id"=>"STATUS", "content"=>"Проверка (Y/N/S)", "sort"=>"STATUS","default"=>true],
    ["id"=>"USER_IP", "content"=>"IP User", "sort"=>"USER_IP", "default"=>true],
    ["id"=>"RECAPTCHA", "content"=>"Ответ", "sort"=>"RECAPTCHA", "default"=>true],
]);

// вывод
while($arRes = $rsData->NavNext(true, "f_")):
    // создаем строку. результат - экземпляр класса CAdminListRow
    $row =& $lAdmin->AddRow($f_ID, $arRes);
    //
    $row->AddViewField("TIMESTAMP_X",date("d.m.Y H:i", strtotime($f_TIMESTAMP_X)));
    $row->AddViewField("FORM_ID",$f_FORM_ID);
    $row->AddViewField("FORM_SID",$f_FORM_SID);
    $row->AddViewField("STATUS",$f_STATUS);
    $row->AddViewField("USER_IP",$f_USER_IP);
    //
    $arrRecaptcha = [];
    $arRes['RECAPTCHA'] = (array)json_decode($arRes['RECAPTCHA']);
    if (count($arRes['RECAPTCHA'])) {
        foreach ($arRes['RECAPTCHA'] as $r=>$rec) {
            $arrRecaptcha[] = $r." : ".(is_array($rec)?implode('/',$rec):$rec);
        }
    }
    $row->AddViewField("RECAPTCHA",'<small>'.implode('<br>', $arrRecaptcha).'</small>');
    //

    //
    /* контекстное меню */
    $arActions = Array();
    // разделитель
    $arActions[] = array("SEPARATOR"=>true);
    // если последний элемент - разделитель, почистим мусор.
    if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
        unset($arActions[count($arActions)-1]);

    // применим контекстное меню к строке
    $row->AddActions($arActions);

endwhile;


// резюме таблицы
$lAdmin->AddFooter(
    array(
        array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // кол-во элементов
        array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // счетчик выбранных элементов
    )
);

// групповые действия
$lAdmin->AddGroupActionTable(Array(
    "delete"=>"Удалить", // удалить выбранные элементы
    //"activate"=>"Активировать", // активировать выбранные элементы
    //"deactivate"=>"Деактивировать", // деактивировать выбранные элементы
    // "modern"=>GetMessage("LIST_MODERN"), // отмодерировать выбранные элементы
));

/*  АДМИНИСТРАТИВНОЕ МЕНЮ */
// сформируем меню из одного пункта - добавление параметра и т.д.
$aContext = [];
$aContext = [];

// и прикрепим его к списку
$lAdmin->AddAdminContextMenu($aContext);

/* ВЫВОД */
// альтернативный вывод
$lAdmin->CheckListMode();

// здесь будет вся серверная обработка и подготовка данных

$APPLICATION->SetTitle("Лог ответов ReCaptchaV3"); // установка заголовка
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/* ВЫВОД ФИЛЬТРА */
// создадим объект фильтра
$oFilter = new CAdminFilter($sTableID."_filter", ["ID"]); ?>

<?
// выведем таблицу списка элементов
$lAdmin->DisplayList();
?>

<? // информационная подсказка, про статусы и т.д.
echo BeginNote();
echo "В логах отчета уведомления проверка (Y/N) означает пройдена проверка или нет как определил Вас Гугл, степень оценки  0.0 < score < 0.9 <br>";
echo "если score >= 0.5 то выходит ошибка. (сообщение об ошибке можно поменять в админке)<br>";
echo "score - оценка сравнения бот или человек (статус S - означет проверка пройденна с ошибкой, нужно попробывать еще раз.)";
echo EndNote();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
