<?php
/**
 * @name Manager.php
 * @desc cache system, encapsulate distributing strategy and cache operate behavior
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-30
 * @version 0.01
 * @comment 如果想要在一次请求中使用多种分发策略，则:
 *  1.在DI中注册不同的配置文件
 *  2.new NoSql('service_name')时传入注册的配置文件的服务名
 * If u want use multiple distribute strategy in one request, then:
 *  1.register a new config file service in di;
 *  2.get in service name when instantiate the NoSql('service_name')
 * @derived 继承时覆盖db_name、table_name属性，以标识为不同的model。
 */

namespace GreenTea\Model;


use GreenTea\Cache\General;
use \GreenTea\DI\Services;
use GreenTea\DI\Web as DI;
use GreenTea\Model\DataBase\Strategy;
use GreenTea\Utility\XArray;
use GreenTea\Vector;
use PhalconEx\Mvc\Dispatcher;

class NoSql extends Strategy{

    const DEFAULT_DB = 1;
    const SCOPE_ALL = 'all';
    const SCOPE_DB = 'db';
    const SCOPE_TABLE = 'table';

    /**
     * @var string
     * @override
     */
    protected $_config_service = Services::CONFIG_NOSQL;

    protected $_db_name = 'Db';
    protected $_table_name = 'Table';
    protected $_key;
    protected $_content;

    public function __construct($config_service = ''){
        parent::__construct($config_service);
        $this->onConstruct();
    }

    protected function onConstruct(){

    }

    public function set($key, $value, $lifetime = null){
        $driver = $this->_getDriverByOpType(Distribute::OP_WRITE);
        return $driver->set($key, $value, $lifetime);
    }

    public function get($key){
        $driver = $this->_getDriverByOpType(Distribute::OP_READ);
        return $driver->get($key);
    }

    public function del($key){
        $driver = $this->_getDriverByOpType(Distribute::OP_WRITE);
        return $driver->delete($key);
    }

    /**
     * @param array $items
     * @param null $lifetime
     */
    public function multiSet(Array $items, $lifetime = null){
        $driver = $this->_getDriverByOpType(Distribute::OP_WRITE);
        return $driver->multiSet($items, $lifetime);
    }

    public function multiGet(Array $keys){
        $driver = $this->_getDriverByOpType(Distribute::OP_READ);
        return $driver->multiGet($keys);
    }

    public function multiDel(Array $keys){
        $driver = $this->_getDriverByOpType(Distribute::OP_WRITE);
        return $driver->multiDel($keys);
    }

    public function flush($scope = self::SCOPE_TABLE){
        return $this->_getDriverByOpType(Distribute::OP_WRITE)->flush($scope);
    }

    /**
     * 考虑到multiGet/Set，所以规定只使用db/table来影响分布策略
     * @param $op_type
     * @return \GreenTea\Cache\General
     */
    protected function _getDriverByOpType($op_type){
        $factors = parent::_assembleFactors($this->_db_name, $this->_table_name, $op_type);
        return $this->_getDriver($factors);
    }

    public function setDbName($name){
        $this->_db_name = $name;
    }

    public function setTableName($name){
        $this->_table_name = $name;
    }

    public function setKey($key){
        $this->_key = $key;
    }

    public function getKey(){
        return $this->_key;
    }

    /**
     * OO形式使用的接口，先load/然后访问和修改content/然后save
     * @param $content
     */
    private function setContent($content){
        $this->_content = $content;
    }

    /**
     * OO形式使用的接口，先load/然后访问和修改content/然后save
     * TODO content跟vector还是不统一，有违和感
     */
    private function getContent(){
        if(!$this->_content){
            return $this->toArray();
        }else{
            return $this->_content;
        }
    }

    private function load($key){
        $ret = $this->get($key);
        $this->setKey($key);
        if(is_array($ret)) $this->init($ret);
        else $this->setContent($ret);
    }

    private function save(){
        self::set($this->_key, $this->getContent());
    }
}