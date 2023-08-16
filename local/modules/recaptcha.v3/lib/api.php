<?php
namespace ReCaptcha\V3;

use \Bitrix\Main\Config\Option;
use \ReCaptcha\V3\Recaptchav3Table;

/**
 * Class Api
 * @package ReCaptcha\V3
 */
class Api
{

    static $MODULE_ID = "recaptcha.v3";

    public static function tokenKey()
    {
        return Option::get(self::$MODULE_ID, "RECAPTCHA_TOKEN_KEY", "");
    }

    public static function tokenSecretKey()
    {
        return Option::get(self::$MODULE_ID, "RECAPTCHA_TOKEN_SECRET_KEY", "");
    }

    public static function getScore()
    {
        $score = Option::get(self::$MODULE_ID, "RECAPTCHA_SCORE", "");
        $score = ($score?trim(str_replace(',','.',$score)):"0.5");
        return floatval($score);
    }

    public static function getLog()
    {
        $log = Option::get(self::$MODULE_ID, "RECAPTCHA_LOG", "");
        return ($log=='Y'?true:false);
    }

    public static function getStrArr($arr)
    {
        $arrRecaptcha = [];
        if (count($arr)) {
            foreach ($arr as $r=>$rec) {
                $arrRecaptcha[] = $r." : ".(is_array($rec)?implode('/',$rec):$rec);
            }
        }
        return implode(PHP_EOL, $arrRecaptcha);
    }

    public static function getCurlProxy()
    {
        $curl_proxy = trim(Option::get(self::$MODULE_ID, "RECAPTCHA_CURLOPT_PROXY", ''));
        return ($curl_proxy && strlen($curl_proxy) > 2?$curl_proxy:false);
    }

    // https://www.google.com/recaptcha/api/siteverify
    public static function requestPostReCaptcha($recaptcha_response = Null, $ID = Null, $SID = Null)
    {
        //
        $arJson = [];
        // Build POST request
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_secret = self::tokenSecretKey();
        $recaptcha_curl_proxy = self::getCurlProxy();
        //
        if ($recaptcha_response) {
            // Make and decode POST request
            $data = [
                'secret' => $recaptcha_secret,
                'response' => $recaptcha_response
            ];
            $verify = curl_init();
            curl_setopt($verify, CURLOPT_URL, $recaptcha_url);
            curl_setopt($verify, CURLOPT_POST, true);
            curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
            if ($recaptcha_curl_proxy) { curl_setopt($verify, CURLOPT_PROXY, $recaptcha_curl_proxy); }
            $response = curl_exec($verify);
            $recaptcha = (array)json_decode($response);

            // Take action based on the score returned
            if ($recaptcha['score'] >= self::getScore()) {
                // Verified - send email
                $arJson['success'] = 'Y';
            } else {
                // Not verified - show form error, message with status S assigned the status Y
                $arJson['success'] = (isset($recaptcha['score'])?'N':'S');
            }
            //
            $arJson['date'] = time();
            $arJson['fid'] = $ID;
            $arJson['sid'] = $SID;
            $arJson['recaptcha'] = $recaptcha;
            $arJson['response'] = $recaptcha_response;
            $arJson['ip'] = self::getRealUserIp();

            // here goes the logging algorithm
            if (self::getLog()) self::addLog($arJson);

        }

        return (count($arJson)?$arJson:false);
    }

    public static function getRealUserIp()
    {
        switch (true) {
            case (!empty($_SERVER['HTTP_X_REAL_IP'])) : return $_SERVER['HTTP_X_REAL_IP'];
            case (!empty($_SERVER['HTTP_CLIENT_IP'])) : return $_SERVER['HTTP_CLIENT_IP'];
            case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) : return $_SERVER['HTTP_X_FORWARDED_FOR'];
            default : return $_SERVER['REMOTE_ADDR'];
        }
    }

    public static function addLog($arJson)
    {
        $result = Recaptchav3Table::add([
            "FORM_ID" => trim($arJson['fid']),
            "FORM_SID" => trim($arJson['sid']),
            "STATUS" => trim($arJson['success']),
            "USER_IP" => trim($arJson['ip']),
            "RECAPTCHA" => json_encode($arJson['recaptcha'], JSON_UNESCAPED_UNICODE),
        ]);

        if ($result->isSuccess()) {
            return $result->getId(); // id
        } else return false;
    }
}