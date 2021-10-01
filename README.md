# 😸 The module reCAPTCHA.v3 for Bitrix
### this module contains logging, which allows you to see the response of the reCAPTCHA.v3 service
```php
/index_form.php // the test form
/local/modules/recaptcha.v3/*
``` 

![the form](https://github.com/otolaa/bitrix_recaptchav3/blob/master/images/recaptchav3/re.png "the form")  

```php
// https://www.google.com/recaptcha/api/siteverify
\ReCaptcha\V3\Api::requestPostReCaptcha($recaptcha_response = Null, $ID = Null, $SID = Null);
```

![reCAPTCHA.v3 module](https://github.com/otolaa/bitrix_recaptchav3/blob/master/images/recaptchav3/re_2.png "reCAPTCHA.v3 module")  

![reCAPTCHA.v3 the log](https://github.com/otolaa/bitrix_recaptchav3/blob/master/images/recaptchav3/re_1.png "reCAPTCHA.v3 the log")  