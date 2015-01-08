<?php
namespace Account\ThirdApp;

use Account\User\UserController;

/**
 * Class ThirdAppController
 * @package Account\ThirdApp
 * @author Zhang Zhaowei <zhangzw6@lenovo.com>
 */
class ThirdAppController
{
    /**
     * @param string $appname
     * @return \Account\ThirdApp\ThirdAppAccountAuthorization $class
     */
    public static function createApp($appname)
    {
        $applist = [
            'lenovoid' => 'LenovoId'
        ];
        $appname = $applist[strtolower($appname)];
        $class = "Account\\ThirdApp\\App\\{$appname}Authorization";
        return new $class;
    }

    /**
     * @param \Account\ThirdApp\ThirdAppAccountAuthorization $app
     */
    public function onLogin($app)
    {
        $location = $app->getLoginUrl();
        header('Location: ' . $location, true, 302);
    }

    /**
     * @param \Account\ThirdApp\ThirdAppAccountAuthorization $app
     * @throws \Exception
     */
    public function onCallback($app)
    {
        $credentials = $app->verifyAuthorizationCode();

        if ($credentials === false) {
            throw new \Exception("服务器发生错误，请重试");
        }

        /**
         * 判断第三方帐号是否已存在
         */
        if ($userAccess = UserController::thirdAppAccountExist($credentials)) {
            if (UserController::isLogged()) {
                if (UserController::getUserId() == $userAccess['user_id']) {
                    /**
                     * 是当前登录用户（更新Token）
                     */
                    UserController::refreshUserAccess(UserController::getUserId(), $credentials);
                } else {
                    /**
                     * 添加一个已经存在的用户的第三方帐号，报错？重新登录？
                     */
                    throw new \Exception("您登录的第三方帐号已经绑定了其他绿茶帐号");
                }
            } else {
                /**
                 * 登录
                 */
                UserController::auth($userAccess['user_id']);
            }
        } else {
            if (UserController::isLogged()) {
                /**
                 * （询问？）绑定新登录方式到帐号
                 */
                UserController::addUserAccess(UserController::getUserId(), $credentials);
            } else {
                /**
                 * 使用当前帐号作为主帐号注册登录
                 */
                UserController::thirdAppAccountSignup($credentials);
            }
        }

        /**
         * @todo 跳转到先前页面
         */
    }
}
