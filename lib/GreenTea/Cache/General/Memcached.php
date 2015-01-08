<?php
/**
 * @name Memcached.php
 * @desc Phalcon的LibMemcached竟然不使用长连接！很不爽给他重新封装下。
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-3
 * @version 0.01
 */

namespace GreenTea\Cache\General;


use GreenTea\Cache\Connector;
use GreenTea\Cache\Convertor;
use GreenTea\Utility\XArray;
use GreenTea\Cache\General;

class Memcached extends General{

    /**
     * @var \Memcached
     */
    protected $_mem;

    /**
     * @param Convertor $convertor
     * @param array $options
     * @throws \Exception
     */
    public function __construct(Convertor $convertor, Array $options){
        parent::__construct($convertor, $options);
        $this->_mem = \GreenTea\Cache\Connector\Memcached::getInstance()->connect($options);
    }

    protected function _set($key, $value, $lifetime = null){
        return $this->_mem->set($key, $value, $lifetime);

    }

    protected function _get($key){
        return $this->_mem->get($key);
    }

    protected function _delete($key){
        return $this->_mem->delete($key);
    }

    /**
     * 不支持query key，需要请使用MemcachedSQ
     * @param string $prefix
     * @return array
     */
    protected function _queryKeys($prefix = ''){
        return [];
    }

    /**
     * 不支持范围删除
     * @param $scope
     * @return bool
     */
    protected function _flush($scope){
        return $this->_mem->flush();

    }

    protected function _multiSet(Array $items, $lifetime = null){
        return $this->_mem->setMulti($items, $lifetime);
    }

    protected function _multiGet(Array $keys){
        return $this->_mem->getMulti($keys);
    }

    protected function _multiDel(Array $keys){
        return $this->_mem->deleteMulti($keys);

    }

    protected function _switchDb($dbname){
        $this->_mem->setOption(\Memcached::OPT_PREFIX_KEY, $dbname . $this->_table_name);
    }

    protected function _switchTable($table){
        $this->_mem->setOption(\Memcached::OPT_PREFIX_KEY, $this->_db_name . $table);
    }

}