<?php
/**
 * @name Action.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/12/25 0025
 * @version 0.01
 */
namespace Library\Base;


use GreenTea\DI\Services;
use GreenTea\Utility\XArray;

abstract class Action extends \Phalcon\DI\Injectable {
    /**
     * @var string
     * 如果controller中的action_setting设置的action名与类名不符时的真实类名
     */
    protected $_real_action;

    /**
     * @var \Phalcon\Logger\Adapter
     */
    protected $_logger;

    /**
     * @var \Greentea\Model\Nosql
     */
    protected $_cache;

    public function __construct(){
        $this->_logger = $this->di[Services::SERVICE_LOGGER];
        $this->_cache = $this->di[Services::SERVICE_CACHE];
    }

    public function execute(){
        if(!method_exists($this, 'run')){
            throw new \Exception('Action Class must implement the Method [run]!');
        }
        $this->_logger->warning('execute start');
        try{
            $ret = call_user_func_array([$this, 'run'], func_get_args());
            $this->sendJson($ret);
        }catch (\Exception $e){
            if(DEV_MODE == 'dev'){ //开发模式不捕获异常。
                throw $e;
            }
            $this->_logger->warning($e->getMessage());
        }
        $this->_logger->warning('execute end');
    }

    /**
     * 这里不使用抽象方法强制子类实现，是因为子类需要的参数可能不同，导致跟抽象方法不匹配。
     */
    //abstract public function run();

    protected function sendJson($data){
        if(!$data) return;
        \Library\Utils::sendJson($data, $this->response);
    }

    public function setRealAction($action){
        $this->_real_action = $action;
    }
} 