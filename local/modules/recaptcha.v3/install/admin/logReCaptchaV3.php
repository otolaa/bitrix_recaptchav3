<?php

if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/recaptcha.v3/admin/logReCaptchaV3.php")) {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/recaptcha.v3/admin/logReCaptchaV3.php");
} else {
    require($_SERVER["DOCUMENT_ROOT"]."/local/modules/recaptcha.v3/admin/logReCaptchaV3.php");
}
