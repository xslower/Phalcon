<?php
namespace Account\User;

use User;
use UserAccess;

/**
 * Class UserController
 * @package Account\User
 * @author Zhang Zhaowei <zhangzw6@lenovo.com>
 */
class UserController
{
    /**
     * @var UserController
     */
    private static $instance = null;
    /**
     * @var array|null
     */
    private $user = null;
    /**
     * @var int|null
     */
    private $user_id = null;
    /**
     * @var bool
     */
    private $logged = false;

    public function __construct()
    {
        /**
         * 根据Session判断是否已经登录
         */
        if (!isset($_SESSION)) {
            session_start();
        }

        if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
            $userModel = new User();
            $this->user = $userModel->getOne(['id' => $_SESSION['user_id']]);
            $this->user_id = $_SESSION['user_id'];
        }
        $this->logged = !is_null($this->user);
    }

    private static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * 判断是否已经登录
     * @return bool
     */
    public static function isLogged()
    {
        return self::getInstance()->logged;
    }

    /**
     * 获取当前用户
     * @return array|null
     */
    public static function getUser()
    {
        return self::getInstance()->user;
    }

    /**
     * 获取当前用户
     * @return array|null
     */
    public static function getUserId()
    {
        return self::getInstance()->user_id;
    }

    /**
     * 将登录状态写入Session/Cookie中
     * @param int $user_id
     */
    public static function auth($user_id)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION['user_id'] = $user_id;
        self::$instance = null;
    }

    /**
     * @param array $credentials
     * @return bool
     */
    public static function login($credentials)
    {

    }

    /**
     * 判断第三方帐号是否已经注册过
     * @param array $credentials
     * @return array|bool
     */
    public static function thirdAppAccountExist($credentials)
    {
        $userAccess = new UserAccess();
        $item = $userAccess->getOne([
            'type' => $credentials['appid'],
            'identifier' => $credentials['identifier']
        ]);
        return $item ? $item : false;
    }

    /**
     * 使用第三方登录信息创建账户（创建账户、添加第三方登录信息）
     * @param array $credentials
     * @return int $user_id
     */
    public static function thirdAppAccountSignup($credentials)
    {
        $item = [
            'username' => isset($credentials['username']) ? $credentials['username'] : '',
            'email' => isset($credentials['email']) ? $credentials['email'] : '',
            'phone' => isset($credentials['phone']) ? $credentials['phone'] : '',
        ];
        $user_id = self::createAccount($item);
        self::addUserAccess($user_id, $credentials);
        return $user_id;
    }

    /**
     * 注册帐号
     * @param $userinfo
     * @return mixed
     */
    public static function createAccount($userinfo)
    {
        $userinfo['created_at'] = date('Y-m-d H:i:s');
        $userModel = new User();
        $user_id = $userModel->insert($userinfo);
        return $user_id;
    }

    /**
     * 添加第三方登录
     * @param $user_id
     * @param $credentials
     */
    public static function addUserAccess($user_id, $credentials)
    {
        $item = [
            'user_id' => $user_id,
            'type' => $credentials['appid'],
            'identifier' => $credentials['identifier'],
            'credential' => $credentials['credential'],
            'verified' => $credentials['verified']
        ];
        $userAccessModel = new UserAccess();
        $userAccessModel->insert($item);
    }

    /**
     * 更新第三方帐号Token
     * @param $user_id
     * @param $credentials
     */
    public static function refreshUserAccess($user_id, $credentials)
    {
        $item = [
            'credential' => $credentials['credential'],
            'verified' => $credentials['verified']
        ];
        $userAccessModel = new UserAccess();
        $userAccessModel->update(
            $item,
            ['user_id' => $user_id]
        );
    }
}
