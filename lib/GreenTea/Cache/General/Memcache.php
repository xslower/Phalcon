<?php
/**
 * @name Memcache.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-16
 * @version 0.01
 */

namespace GreenTea\Cache\General;

use GreenTea\Cache\Connector;
use GreenTea\Cache\Convertor;
use GreenTea\Utility\XArray;
use GreenTea\Cache\General;

class Memcache extends General{

    /**
     * @var \Memcache
     */
    protected $_mem;

    /**
     * @param Convertor $convertor
     * @param Array $options
     * @throws \Exception
     */
    public function __construct(Convertor $convertor, Array $options){
        parent::__construct($convertor, $options);
        $this->_mem = \GreenTea\Cache\Connector\Memcache::getInstance()->connect($options);
    }

    protected function _set($key, $value, $lifetime = null){
        return $this->_mem->set($this->getPrefixedKey($key), $value, 0, $lifetime);
    }

    protected function _get($key){
        return $this->_mem->get($this->getPrefixedKey($key));
    }

    protected function _delete($key){
        return $this->_mem->delete($this->getPrefixedKey($key));
    }

    /**
     * Memcache()封装中没有查找key的方法，
     * Phalcon的queryKeys的实现就是自己把所有的key保存在一个特殊的specialKey中，
     * 查找时先取出key的值，然后遍历查找。
     * 这里不支持，需要请使用MemcacheSQ
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
        foreach ($items as $key => $content) {
            $this->_set($key, $content, $lifetime);
        }
        return true;
    }

    protected function _multiGet(Array $keys){
        $items = [];
        foreach ($keys as $key) {
            $ret = $this->_get($key);
            if($ret === null) continue;
            $items[$key] = $ret;
        }
        return $items;
    }

    protected function _multiDel(Array $keys){
        foreach ($keys as $key) {
            $this->_delete($key);
        }
        return true;
    }

    protected function _switchDb($dbname){}

    protected function _switchTable($table){}

    protected function getPrefixedKey($key){
        return $this->_db_name . $this->_table_name . $key;
    }

    protected function getOriginKey($key){
        return \GreenTea\Utility\String::strip($key, $this->_db_name . $this->_table_name);
    }
} 