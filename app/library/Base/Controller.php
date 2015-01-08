<?php
/**
 * @name Controller.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/12/25 0025
 * @version 0.01
 */
namespace Library\Base;

use GreenTea\DI\Services;
use GreenTea\Utility\String;

abstract class Controller extends \Phalcon\Mvc\Controller {
    //protected $_default_action = 'index';
    /**
     * @var array
     * action路径默认为app/action/CONTROLLER/ACTION.php
     * 如果不是此路径，则需要在子类中继承此数组并配置
     * 格式：
     * 'actionInUrl' => 'path/to/ActionInUrl.php'   //每个url中的action对应一个路径
     */
    protected $_action_setting = [];

    /**
     * @var \Phalcon\Logger\Adapter
     */
    protected $_logger;

    /**
     * @var \Greentea\Model\Nosql
     */
    protected $_cache;

    public function onConstruct(){
        $this->_logger = $this->di[Services::SERVICE_LOGGER];
        $this->_cache = $this->di[Services::SERVICE_CACHE];
    }

    /**
     * 被dispatcher的beforeException事件替代了。
     * @return mixed
     * @throws \Exception
     */
    public function dispatchAction(){
        $params = func_get_args();
        $action = array_shift($params);
        if(!$action){ //如果action名为空，则使用默认
            $action = $this->_default_action;
        }
        $method = $action . 'Action';
        $this->dispatcher->setActionName($action);
        if(method_exists($this, $method)){
            $ret = call_user_func_array([$this, $method], $params);
            return $ret;
        }
        $actionName = String::camelize($action);
        $path = ROOT_PATH . '/app/action/';
        if(isset($this->_action_setting[$action])){
            $subPath = $this->_action_setting[$action];
            $path .= $subPath;
            $actionName = substr($subPath, strrpos($subPath, '/') + 1, -strlen('.php'));
        }else{
            $path .= String::camelize( $this->router->getControllerName()) . '/' . $actionName . '.php';
        }
        if(file_exists($path)){
            require $path;
        }else{
            throw new \Exception('Action[' . $actionName . '] Are not Found!!');
        }
        $instance = new $actionName;
        call_user_func([$instance, 'setDI'], $this->_dependencyInjector);
        call_user_func([$instance, 'setEventsManager'], $this->_eventsManager);
        $ret = call_user_func_array([$instance, 'execute'], $params);
        return $ret;

    }
    public function getActionSetting(){
        return $this->_action_setting;
    }

} 