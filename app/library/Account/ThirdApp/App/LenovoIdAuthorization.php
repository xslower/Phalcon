<?php
namespace Account\ThirdApp\App;

use Account\ThirdApp\ThirdAppAccountAuthorization;

/**
 * Class LenovoIdAuthorization
 * @package Account\ThirdApp\App
 * @author Zhang Zhaowei <zhangzw6@lenovo.com>
 */
class LenovoIdAuthorization implements ThirdAppAccountAuthorization
{
    const APPID = 'lenovoid';
    const REALM = 'browsergreen.lenovo.com'; //app_key

    public function getAppId()
    {
        return self::APPID;
    }

    public function getLoginUrl()
    {
        $realm = self::REALM;

        $url = 'https://passport.lenovo.com/wauthen/gateway';
        $params = [
            'lenovoid.action' => 'uilogin',
//            'lenovoid.uinfo' => 'username,nickname,pid',
            'lenovoid.cb' => 'http://' . $_SERVER['SERVER_NAME'] . '/account/third/lenovoid/callback',
            'lenovoid.realm' => $realm
        ];
        $paramstring = http_build_query($params);
        $redirect = $url . '?' . $paramstring;
        return $redirect;
    }

    public function getLogoutUrl()
    {

    }

    public function verifyAuthorizationCode()
    {
        $realm = self::REALM;

        $params = [
            'lpsust' => isset($_GET['lenovoid.wust']) ? $_GET['lenovoid.wust'] : $_GET['lenovoid_wust'],
            'realm' => $realm
        ];
        $url = 'https://passport.lenovo.com/interserver/authen/1.2/getaccountid';
        $paramstring = http_build_query($params);
        $request = $url . '?' . $paramstring;

        $ch = curl_init($request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        if ($result === false) {
            /**
             * 网络连接失败
             */
            return false;
        } else {
            /**
             * Parse XML File
             */
            $xml = @simplexml_load_string($result);
            if (isset($xml->AccountID)) {
                /**
                 * 登录成功
                 */
                return [
                    'appid' => 'lenovoid',
                    'identifier' => (string)$xml->AccountID,
                    'credential' => $params['lpsust'],
                    'email' => (string)$xml->Username,
                    'verified' => (string)$xml->verified
                ];
            } else {
                /**
                 * 登录失败
                 */
                return false;
            }
        }
    }
}
