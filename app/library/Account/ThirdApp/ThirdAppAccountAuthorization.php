<?php
namespace Account\ThirdApp;

/**
 * Interface ThirdAppAccountAuthorization
 * 第三方应用接入需要实现的接口
 * @package Account\ThirdApp
 * @author Zhang Zhaowei <zhangzw6@lenovo.com>
 */
interface ThirdAppAccountAuthorization
{
    /**
     * 标准的应用名
     * @return string
     */
    public function getAppId();

    /**
     * 登录跳转地址
     * @return string
     */
    public function getLoginUrl();

    /**
     * 注销跳转地址
     * @return string
     */
    public function getLogoutUrl();

    /**
     * 用 Authorization Code 取用户信息
     * @return boolean|array
     */
    public function verifyAuthorizationCode();
}
