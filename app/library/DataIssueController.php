<?php
use GreenTea\DI\MoreDefault;
use GreenTea\Utility\XArray;

/**
 * @name DataIssueController.php
 * @desc
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-9
 * @version 0.01
 */

abstract class DataIssueController extends \Phalcon\Mvc\Controller{
    /**
     * @var \Phalcon\Logger\Adapter\File
     */
    protected $_logger;

    protected $_result_default = ['err_no' => 0];
    protected $cbPreProcess;
    protected $cbAfterProcess;
    protected $cbGetVersion;
    protected $cbGetData;

    public function onConstruct(){
        $this->_logger = $this->di->get(MoreDefault::SERVICE_LOGGER);
        $model_factory = $this->di->get(MoreDefault::FACTORY_APP_MODEL);

        $mode = XArray::fetchItem($_GET, 'mode', 'file');
        if($mode == 'file'){ //通过函数式编程，在一个类中达到多个子类的效果
            $this->cbGetData = function($service) use($model_factory){
                $path = ROOT_PATH . '/config/data/' . $service . '.php';
                if(file_exists($path)){
                    return require($path);
                }else{
                    return [];
                }
            };
            $this->cbGetVersion = function($service) use($model_factory){
                $dv = require(ROOT_PATH . '/config/data/data_version.php');
                return XArray::fetchItem($dv, $service, 1);
            };
        }else{
            $this->cbGetData = function($service) use($model_factory){
                return $model_factory->getShared($service)->getList();
            };
            $this->cbGetVersion = function($service) use($model_factory){
                $condition['service'] = $service;
                return $model_factory->getShared('data_version')->getOne($condition)['version'];
            };
        }
        $this->cbPreProcess = function(&$input){
            return true;//返回其它值则表明需要中断处理，直接输出返回信息。
        };
        $this->cbAfterProcess = function(&$output, $input){
            return $output;
        };
    }

    public function process() {
        try {
            $request = array_merge($_GET, $_POST);

            $ret = $this->_process($request);
            $this->output($ret);

        } catch (Exception $e) {
            $this->_logger->warning($e->getMessage());
        }
    }

    //子程序入口函数
    protected function _process($request){
        $cbPreProcess = $this->cbPreProcess;
        $go_on = $cbPreProcess($request);
        if($go_on !== true){
            return $go_on;
        }
        $service = $request['service'];
        $version = call_user_func_array($this->cbGetVersion, [$service]);

        //版本号检测
        $req_ver = XArray::fetchItem($request, 'version', -1);
        if ($req_ver == $version) {
            return $this->_result_default;
        }
        $data = call_user_func_array($this->cbGetData, [$service]);
        $result = ['version' => $version, 'result' => $data];
        $cbAfterProcess = $this->cbAfterProcess;
        $result = $cbAfterProcess($result, $request);
        return $result;
    }

    //输出结果
    protected function output($output) {
        if (!is_array($output)) {
            $temp['err_msg'] = $output;
            $temp['err_no'] = 1;
            $output = $temp;
        } elseif (!array_key_exists('err_no', $output)) {
            $output['err_no'] = 0;
        }
        echo json_encode($output);
    }

    protected function getGtVersion($input){
        $ver = XArray::fetchItem($input, 'gt_ver', '0.0.0.0');
        $ver = str_replace('_', '.', $ver);
        $pattern = '/[\d\.]+/';
        $match = [];
        preg_match($pattern, $ver, $match);
        if($match){
            return $match[0];
        }else{
            return $ver;
        }
    }

    public function __destruct(){

    }
}