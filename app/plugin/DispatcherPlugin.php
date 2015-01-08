<?php
/**
 * @name DispatcherPlugin.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/12/26 0026
 * @version 0.01
 */

use GreenTea\Utility\String;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

class DispatcherPlugin {
    //默认所有controller中的action的返回都会以json格式输出。
    public function afterExecuteRoute(Event $event, Dispatcher $dispatcher){
        $data = $event->getData();
        if(!$data) return;
        $response = $dispatcher->getDI()->get('response');
        Library\Utils::sendJson($data, $response);

    }

//    public function beforeDispatchLoop(){}
//    public function beforeDispatch(){}
//    public function beforeExecuteRoute(){}
//    public function initialize(){}
//    public function afterExecuteRoute(){}
//    public function afterDispatch(){}
//    public function afterDispatchLoop(){}

    /**
     * @param $event
     * @param Dispatcher $dispatcher
     * @param $exception
     * @return bool|mixed
     * @throws Exception
     */
    //public function beforeException(\Phalcon\Events\Event $event, Dispatcher $dispatcher, $exception){
    //        if(!$exception instanceof \Phalcon\Mvc\Dispatcher\Exception){
//            return true;
//        }else{
//            switch($exception->getCode()){
//                //只有下面两种情况继续处理，其它的返回
//                case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
//                case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
//                    break;
//                default:
//                    return true;
//            }
//        }

    public function beforeNotFoundAction(Event $event, Dispatcher $dispatcher){

        $params = $dispatcher->getParams();
        $action = $dispatcher->getActionName();
        $controllerName = String::camelize($dispatcher->getControllerName());
        $fullControllerName = $controllerName . 'Controller';
        $controllerIns = new $fullControllerName();
        $actionSetting = call_user_func([$controllerIns, 'getActionSetting']);
        $actionName = String::camelize($action);
        $path = ROOT_PATH . '/app/action/';
        if(isset($actionSetting[$action])){
            $subPath = $actionSetting[$action];
            $path .= $subPath;
//            $actionName = rtrim(substr($subPath, strpos($subPath, '/') + 1), '.php');
            $actionName = substr($subPath, strrpos($subPath, '/') + 1, -strlen('.php'));
        }else{
            $path .= $controllerName . '/' . $actionName . '.php';
        }
        if(file_exists($path)){
            require $path;
        }else{
            throw new \Exception('Action[' . $actionName . '] Are not Found! Path:[' . $path . ']');
        }
        $actionIns = new $actionName();
        call_user_func([$actionIns, 'setDI'], $dispatcher->getDI());
        call_user_func([$actionIns, 'setEventsManager'], $dispatcher->getEventsManager());
        call_user_func([$actionIns, 'setRealAction'], $actionName);
        $ret = call_user_func_array([$actionIns, 'execute'], $params);

        return false;
    }

} 