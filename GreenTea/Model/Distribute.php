<?php
/**
 * @name Distribute.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-30
 * @version 0.01
 */

namespace GreenTea\Model;

use GreenTea\DI\Services;
use GreenTea\DI\Web as DI;
use GreenTea\Utility\XArray;
use GreenTea\Utility\Tools;


abstract class Distribute {
    const OPT_OP_TYPE = 'type';
    const OP_WRITE = 'write';
    const OP_READ = 'read';
    const FACTOR_DB_NAME = 'dbname';
    const FACTOR_TABLE_NAME = 'tablename';
    const ROLE_MASTER = 'master';   //master can read and write
    const ROLE_SLAVE = 'slave';     //slave only accept read operation
    const ROLE_STANDBY = 'standby'; //standby read only, and can be promoted to master
    const WEIGHT = 'weight';
    const UPPER_BOUND = 'upper_bound';
    const LOWER_BOUND = 'lower_bound';
    const AVAILABLE = 'available';
    const LIFETIME = 'lifetime';
    const PERSISTENT = 'persistent';
    /**
     * 用户更新数据库后，多长时间内只读master。以防止用户看不到自己的数据
     * after user update database, this value determine in how long read data from msater.
     */
    const MASTER_READ_INTERVAL = 'master_read';
    const TIMESTAMP_OF_WRITE = 'timestamp_of_write';
    const INVALIDE_KEY = -999;
    const WEIGHT_DEFAULT = 1;
    const WEIGHT_CEILING = 100;

    protected $_uniqueKey;
    protected $_config;
    protected $_nodes;
    protected $_parsedNodes;
    /**
     * @var \GreenTea\Model\Connector
     */
    protected $_connector;

    /**
     * @var \Phalcon\Session\AdapterInterface
     */
    protected $_session;
    /**
     * @var \Phalcon\Logger\Adapter
     */
    protected $_logger;

    public function __construct(Array $config){
        $this->_nodes = $config['nodes'];
        unset($config['nodes']);
        $this->_config = $config;

        $this->_uniqueKey = $this->getUniqueKey($this->_nodes);
        $this->_parsedNodes = Tools::cacheGet($this->_uniqueKey);
        if(!$this->_parsedNodes){
            $this->_parsedNodes = $this->_getParsedNodes($this->_nodes, $config);
        }

        $mc_factory = DI::getDefault()->getShared(Services::FACTORY_MODEL_CONNECTION);
        $this->_connector = $mc_factory->getShared($this->_config['driver']);
        $this->_session = DI::getDefault()->getShared(Services::SERVICE_SESSION);
        $this->_logger = DI::getDefault()->getShared(Services::SERVICE_LOGGER);
    }

    /**
     * @param array $factors all factors that affect distribute strategy result,
     *  e.g.'key'/'dbname'/'write_or_read'/etc
     * @return \GreenTea\Cache\General|\Phalcon\Db\Adapter\Pdo
     */
    abstract public function getDistributedDriver(Array $factors);

    protected function getUniqueKey(Array $nodes){
        $node = current($nodes);
        $key = key($nodes);
        $host = XArray::fetchItem($node, 'host', 'localhost');
        $port = XArray::fetchItem($node, 'port', 'port');
        return $key . $host . $port;
    }

    /**
     * @param array $nodes
     * @return mixed
     */
    private function _getParsedNodes(Array $nodes, $config){
        $this->_parseConfig($nodes, $config);
        $parsedNodes = $this->_parseRole($nodes);
        $this->_parseWeight($parsedNodes);
        return $parsedNodes;
    }

    /**
     * 把一些统一配置的，但却是连接时使用的项，推到node中
     * @param array $nodes
     * @param array $config
     */
    private function _parseConfig(Array &$nodes, Array $config){
        foreach ($nodes as &$n) {
            if(!isset($n[self::PERSISTENT]) && isset($config[self::PERSISTENT])){
                $n[self::PERSISTENT] = $config[self::PERSISTENT];
            }
            if(!isset($n[self::LIFETIME]) && isset($config[self::LIFETIME])){
                $n[self::LIFETIME] = $config[self::LIFETIME];
            }
        }
    }

    /**
     * @param array $nodes
     * @return array
     * @throws \Exception
     */
    private function _parseRole(Array $nodes){
        $parsedNodes = array();
        foreach ($nodes as $k => $v) {
            if(!isset($v['role']) ) {
                $v['role'] = self::ROLE_MASTER;
            }
            switch($v['role']){
                case self::ROLE_MASTER:
                    $parsedNodes[$k][$k] = $v;
                    break;
                case self::ROLE_SLAVE:
                case self::ROLE_STANDBY:
                    if(isset($v['master']) && isset($nodes[$v['master']])){
                        //has key 'master'，and it's valid
                        $parsedNodes[$v['master']][$k] = $v;
                    }else{
                        throw new \Exception($v['role'] . ' specified a not valid master value:' . $v['master']);
                    }
                    break;
                default:
                    throw new \Exception('Not supported role:' . $v['role']);
            }
        }
        return $parsedNodes;
    }

    /**
     * @param array $nodes
     *  in config file: 'weight' => 1/'weight' => 2
     *  wo need parse it to a percentage scope. eg: 1~33/34~100
     * @comment 不设weight参数，则默认都是1，即为不权重策略，所以无需把有权重跟无权重分开
     */
    private function _parseWeight(Array &$nodes){
        //parse first level
        foreach ($nodes as $k => &$v) {
            $v[self::WEIGHT] = isset($v[$k][self::WEIGHT]) ? $v[$k][self::WEIGHT] : self::WEIGHT_DEFAULT;
        }
        $this->_calcUpperLowerBound($nodes);
        foreach ($nodes as &$subNodes) {
            $this->_calcUpperLowerBound($subNodes);
        }
    }

    private function _calcUpperLowerBound(Array &$nodes){
        $sum_weight = 0;
        foreach ($nodes as &$v) {
            if(!is_array($v)) continue; //weight/upper_bound等刚增加上去的附加信息不能解析
            if(!isset($v[self::WEIGHT])){
                $v[self::WEIGHT] = self::WEIGHT_DEFAULT;
            }
            $sum_weight += $v[self::WEIGHT];
        }
        $lastUpperBound = 0;
        $last_remainder = 0;
        foreach ($nodes as &$v) {
            if(!is_array($v)) continue; //同上
            /**
             * 这里处理余数是保证所有的权重范围加起来能充满1～100。
             * 如果使用floor/round之类的直接处理，最终必然会有1～N的数值消失
             */
            $remainder = ($v[self::WEIGHT] * self::WEIGHT_CEILING + $last_remainder) % $sum_weight;
            if($remainder === 0){ //余数=0,说明能整除
                $upperBound = ($v[self::WEIGHT] * self::WEIGHT_CEILING + $last_remainder)
                    / $sum_weight;
            }else{ //不能整除，则减去余数即可整除
                $upperBound = ($v[self::WEIGHT] * self::WEIGHT_CEILING + $last_remainder - $remainder)
                    / $sum_weight;
            }
            $last_remainder = $remainder;
            $v[self::LOWER_BOUND] = $lastUpperBound + 1;
            $v[self::UPPER_BOUND] = $lastUpperBound + $upperBound;
            $lastUpperBound = $v[self::UPPER_BOUND];
        }
    }

    protected function getOneAvailable(Array $nodes, $step){
        $targetKey = self::INVALIDE_KEY;
        foreach ($nodes as $k => $v) {
            if($v[self::LOWER_BOUND] <= $step && $step <= $v[self::UPPER_BOUND]){
                $targetKey = $k;
                break;
            }
        }
        if($targetKey === self::INVALIDE_KEY){
            throw new \Exception("Step[{$step}] Is Out Of Bound!");
        }
        $available = XArray::fetchItem($nodes[$targetKey], self::AVAILABLE, true);
        if($available === false){
            $oldKey = $targetKey;
            foreach ($nodes as $k => $v) {
                if($v[self::AVAILABLE]){
                    $targetKey = $k;
                    break;
                }
            }
            if($targetKey === $oldKey){
                //that's mean there is no available node anylonger
                throw new \Exception('No Available Nodes!');
            }
        }
        return $targetKey;
    }

    /**
     * @param $op_type
     * @param int $step
     * @param int $step2
     * @return int
     * @throws \Exception
     * @todo 这里包含两个功能：1读写分离，2写后读。理应分开，但是两个功能强相关，实在分不开。
     */
    protected function getTargetKey($op_type, $step, $step2 = 0){
        if(!$step2){ //步长2=步长1,会有第一次分布在前面的，第二次也会分布在前面的bug！后面的也是
            //所以步长2不设置则随机取值
            $step2 = Tools::getRandom(self::WEIGHT_CEILING);
        }
        $key1 = $this->getOneAvailable($this->_parsedNodes, $step);
        $key2 = $key1;
        if ($op_type == self::OP_READ) { //如果是读操作，可以选择slave
            $timestamp_of_write = $this->_session->get(self::TIMESTAMP_OF_WRITE);

            $interval = intval(XArray::fetchItem($this->_config, self::MASTER_READ_INTERVAL, 0));
            if ($timestamp_of_write && (time() - $timestamp_of_write) > $interval) {
            //如果在MASTER_READ_TIME时间内更新了数据，则强制从master读数据
                $key2 = $this->getOneAvailable($this->_parsedNodes[$key1], $step2);
            }
        } else { //默认是写
            $this->_session->set(self::TIMESTAMP_OF_WRITE, time());

        }
        return $key2;
    }

    /**
     * @param $key
     * @param $factors
     * @return mixed
     */
    protected function tryConnect($key, $factors){
        try{
            $driver = $this->_connector->getConnection($this->_nodes[$key]);
            $options = $this->_nodes[$key];
            $dbname = XArray::fetchItem($factors, self::FACTOR_DB_NAME);
            $origin_dbname = XArray::fetchItem($options, 'dbname');
            if($dbname && $dbname != $origin_dbname){
                $driver->switchDb($dbname);
            }
            $table = XArray::fetchItem($factors, self::FACTOR_TABLE_NAME);
            if($table){
                $driver->switchTable($table);
            }
            return $driver;
        }catch (\Exception $e){
            //log it
            $this->setUnavailable($key);
            return false;
        }
    }

    /**
     * @param $key
     */
    protected function setUnavailable($key){
        foreach ($this->_parsedNodes as &$subNodes) {
            if(!isset($subNodes[$key])){
                continue;
            }
            $subNodes[$key][self::AVAILABLE] = false;
            $subNodes[$key]['timestamp'] = time();
            if($subNodes[$key]['role'] == self::ROLE_MASTER){
                /**
                 * master crashed, and
                 * TODO do not support promote standby to master,
                 * then mask all subnodes in this node!
                 */
                $subNodes[self::AVAILABLE] = false;
            }
            $hasAvailable = false;
            foreach ($subNodes as &$v) {
                if($v[self::AVAILABLE]){
                    $hasAvailable = true;
                    break;
                }
            }
            if(!$hasAvailable){
                $subNodes[self::AVAILABLE] = false;
            }
            break;
        }
    }

    public function __destruct(){
        Tools::cacheSet($this->_uniqueKey, $this->_parsedNodes, $this->_config['lifetime']);
    }

} 