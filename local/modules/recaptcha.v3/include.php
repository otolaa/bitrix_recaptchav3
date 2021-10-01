<?php

\Bitrix\Main\Loader::registerAutoLoadClasses("recaptcha.v3", [
    '\ReCaptcha\V3\ReCaptcha'=>'lib/api.php',
    '\ReCaptcha\V3\Recaptchav3Table'=>'lib/recaptchav3table.php',
]);