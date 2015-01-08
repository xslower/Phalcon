<?php
use GreenTea\DI\Services;
use GreenTea\Utility\XArray;

/**
 * @name DataIssueController.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-9
 * @version 0.01
 */

abstract class ServiceController extends BaseController{
    /**
     * @var \Phalcon\Logger\Adapter\File
     */
    protected $_logger;

    protected $_result_default = ['err_no' => 0];
    //通过匿名函数，在一个类中达到多个子类的效果
    protected $_callback = [];
    protected $cbGetData;
    protected $cbGetVersion;
    protected $cbPreProcess;
    protected $cbAfterProcess;


    public function onConstruct(){
        parent::onConstruct();
        $this->_logger = $this->di->getShared(Services::SERVICE_LOGGER);
        $this->_initCallback();
        $mode = XArray::fetchItem($_GET, 'mode', 'file');
        $this->cbGetData = $this->_callback['get_data'][$mode];
        $this->cbGetVersion = $this->_callback['get_version'][$mode];

        $this->cbPreProcess = function(&$input){
            return true; //要想中断处理，返回非true(会作为接口结果返回)，直接抛出异常也行(会作为接口错误信息返回)
        };
        $this->cbAfterProcess = function(&$output, $input){
            return $output;
        };
    }

    protected function _initCallback(){
        $model_factory = $this->di->getShared(Services::FACTORY_APP_MODEL);
        $this->_callback['get_data']['db'] = function($service) use($model_factory){
            return $model_factory->getShared($service)->getList();
        };
        $this->_callback['get_version']['db'] = function() use($model_factory){
            return $model_factory->getShared('universion')->getList();
        };
        $this->_callback['get_data']['file'] = function($service){
            $path = ROOT_PATH . '/config/data/' . $service . '.php';
            if(file_exists($path)){
                $data = require($path);
                return $data;
            }else{
                $this->_logger->notice('required file is not exist [' . $path . ']');
            }
            return [];

        };
        $this->_callback['get_version']['file'] = function(){
            $path = ROOT_PATH . '/config/data/universion.php';
            if(file_exists($path)){
                return require($path);
            }
            return [];
        };
        $cache_driver = $this->di->getShared(Services::SERVICE_DB_CACHE);
        $this->_callback['get_data']['cache'] = function($service) use($cache_driver){
            return $cache_driver->get($service);
        };
        $this->_callback['get_version']['cache'] = function() use($cache_driver){
            return $cache_driver->get('universion');
        };

    }

    protected function _getVersion($service){
        $versions = call_user_func($this->cbGetVersion, $service);
        if(!is_array($versions)) return $versions;
        foreach ($versions as $ver) {
            if($ver['key'] == $service){
                return $ver['version'];
            }
        }
        return 1;
    }

    public function process($service = '') {
        try {
            $request = array_replace($_POST, $_GET);
            if($service) $request['service'] = $service;
            $ret = $this->_process($request);
            $this->output($ret);

        } catch (Exception $e) {
            $this->output($e->getMessage());
            $this->_logger->warning($e->getMessage());
        }
    }

    protected function _process($request){
        $service = XArray::fetchItem($request, 'service');

        $ret = call_user_func_array($this->cbPreProcess, [&$request]);
        if($ret !== true){
            return $ret;
        }
        $version = $this->_getVersion($service);
        //版本号检测
        $req_ver = XArray::fetchItem($request, 'version', -1);
        if ($req_ver == $version) {
            return $this->_result_default;
        }
        $data = call_user_func($this->cbGetData, $service);
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
        $this->response->setContentType('application/json', 'utf-8');
        $this->response->setJsonContent($output);
        $this->response->send();
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

    protected function _isVersionValid(Array $config, $gt_ver){
        if (isset($config['max'])) {
            if (version_compare($gt_ver, $config['max']) > 0) {
                return false;
            }
        }
        if (isset($config['min'])) {
            if (version_compare($gt_ver, $config['min']) < 0) {
                return false;
            }
        }
        return true;
    }

    public function __destruct(){

    }
} 