<? //
define("TITLE_HEADER", "The module reCAPTCHA.v3 for Bitrix");
define("TITLE_HEADER_SMALL", "reCAPTCHA.v3");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Context;

?>
<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="shortcut icon" href="/favicon.ico" />
        <title><?=TITLE_HEADER?></title>
        <meta name="robots" content="noindex, nofollow">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    </head>
<body>

<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
    <a class="navbar-brand" href="/" title="SAITOVIK"><?=TITLE_HEADER_SMALL?></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav ml-auto ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="#send_question">Форма <span class="sr-only">(current)</span></a>
            </li>
        </ul>
    </div>
</nav>

<main role="main">
    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron" style="padding-bottom: 1.5rem;">
        <div class="container">
            <h4 class="display-4 tiser-h"><?=TITLE_HEADER_SMALL?></h4>
            <div class="alert alert-secondary" role="alert">
                / the module reCAPTCHA.v3 for Bitrix<br>
                / tokenKey => <?=Option::get("recaptcha.v3", "RECAPTCHA_TOKEN_KEY", "")?>
            </div>
        </div>
    </div><!--/jumbotron-->

    <div class="container">
    <? // return form
    if (Loader::includeModule('recaptcha.v3')) :
        $form_id = 2; // number in form
        $sid = 'main_f'; // slug in form
        $get_form = [
                'sid'=>$sid,
                'tokenKey'=>\ReCaptcha\V3\Api::tokenKey(),
                'recaptchaResponse'=>'recaptcha_'.$sid,
        ];

        $frm = [
                ['title'=>'ФИО', 'type'=>'text', 'name'=>'USER_NAME', 'required'=>'Y'],
                ['title'=>'Телефон', 'type'=>'text', 'name'=>'USER_PHONES', 'required'=>'Y'],
                ['title'=>'Вопрос', 'type'=>'textarea', 'name'=>'USER_QUEST', 'required'=>'Y'],
        ];

        // move the message calling algorithm here POST !?
        $error = [];
        $request = Context::getCurrent()->getRequest();
        $get['method'] = $request->getRequestMethod();
        $get['recaptcha_'.$sid] = $request->getPost('recaptcha_'.$sid);
        if ($get['method'] == "POST" && is_set($get['recaptcha_'.$sid]) && strlen($get['recaptcha_'.$sid]))
        {
            $get["recaptcha"] = \ReCaptcha\V3\Api::requestPostReCaptcha($get['recaptcha_'.$sid], $form_id, $sid);
            if ($get["recaptcha"]['success'] == 'N') {
                $error['RECAPTCHA'] = Option::get("recaptchav3", "RECAPTCHA_ERROR", "Y");
            } elseif ($get["recaptcha"]['success'] == 'S') {
                $error['RECAPTCHA'] = Option::get("recaptchav3", "RECAPTCHA_ERROR_SCORE", "Y");
            }
        } ?>

        <div class="alert alert-primary" role="alert">
            Пример формы с реализацей reCAPTCHA.v3 <? if ($get['method'] == "POST" && count($error) == 0) : ?><strong>сообщение отправленно</strong><? endif; ?>
        </div>

        <? if (count($error)) { ?>
            <div class="alert alert-danger" role="alert"><?=implode('<br>', $error)?></div>
        <? } ?>

        <form role="form" action="<?=POST_FORM_ACTION_URI?>" method="POST" class="row" id="send_question" name="send_question">
            <div class="form-group col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
                <label><?=$frm[0]['title']?></label>
                <input type="<?=$frm[0]['type']?>" class="form-control form-control-lg" placeholder="<?=$frm[0]['title']?>" name="<?=$frm[0]['name']?>" <?=($frm[0]['required']=='Y'?'required':'')?>>
            </div>

            <div class="form-group col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
                <label><?=$frm[1]['title']?></label>
                <input type="<?=$frm[1]['type']?>" class="form-control form-control-lg phone" placeholder="<?=$frm[1]['title']?>" name="<?=$frm[1]['name']?>" <?=($frm[1]['required']=='Y'?'required':'')?>>
            </div>

            <div class="form-group col-12">
                <label><?=$frm[2]['title']?></label>
                <textarea class="form-control form-control-lg" rows="2" name="<?=$frm[2]['name']?>" placeholder="<?=$frm[2]['title']?>" <?=($frm[2]['required']=='Y'?'required':'')?>></textarea>
            </div>

            <div class="form-group col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6">
                <div class="custom-checkbox ml-4">
                <input class="form-check-input custom-control-input" type="checkbox" name="remember" value="Y" required="" id="remember">
                <label class="form-check-label custom-control-label" for="remember">Согласен на обработку персональных данных</label>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 text-right">
                <!--// this hidden input for recaptcha -->
                <input type="hidden" id="<?=$get_form['recaptchaResponse']?>" name="<?=$get_form['recaptchaResponse']?>" value="">
                <button type="submit" class="btn btn-primary btn-lg" disabled
                        data-callback="onSubmit"
                        data-badge="inline"
                        data-sid="<?=$get_form['sid']?>"
                        data-fid="1"
                        data-tokenkey="<?=$get_form['tokenKey']?>">Отправить</button>
            </div>
        </form>
    <? endif; ?>
    </div>
</main>

<footer>
    <div class="container"><p></p></div>
</footer>

<script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<script type="text/javascript">
$(document).ready( function() {
    /* the remember */
    $('input[name="remember"]').on('change', function(event) {
        event.preventDefault();
        var $bs = $(this).parent().parent().parent().find('button[type="submit"]');
        if(!$(this).prop("checked")){
            $($bs).attr('disabled','disabled');
        }else{
            $($bs).removeAttr('disabled');
        }
        return false;
    });
    /* add recaptcha for google */
    var $sub = $('#send_question').find("button[type='submit']"), $tokenKey = $sub.data('tokenkey'), $sid = $sub.data('sid');
    $.getScript( "https://www.google.com/recaptcha/api.js?render="+$tokenKey)
        .done(function( script, textStatus ) {
            if(typeof grecaptcha !== "undefined") {
                grecaptcha.ready(function () {
                    grecaptcha.execute($tokenKey, {action: $sid}).then(function (token) {
                        var recaptchaResponse = document.getElementById('recaptcha_'+$sid);
                        recaptchaResponse.value = token;
                    });
                });
            }
        });
});
</script>
</body>
</html>
<?require( $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");?>
