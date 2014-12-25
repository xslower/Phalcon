<?php
/**
 * @name SupportQueryKey.php
 * @desc = 这个一个装饰者，使用时需要包裹一个cache/general(主要是memcache，其它的都支持query keys)
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-16
 * @version 0.01
 */

namespace GreenTea\Cache\General;

use GreenTea\Cache\Connector;
use GreenTea\Cache\Convertor;
use GreenTea\Utility\XArray;
use GreenTea\Cache\General;

class SupportQueryKey extends General{
    /**
     * 因为memcache不支持key查找，所以如果要查找key需要先把key保存一份
     */
    const KEY_INDEX = '_INDEX_';

    protected $_general;

    public function __construct(General $general){
        parent::__construct(new Convertor\None(), []);
        $this->_general = $general;
    }

    public function set($key, $value, $lifetime = null){
        return $this->_set($key, $value, $lifetime);
    }
    protected function _set($key, $value, $lifetime = null){
        $index_key = $this->_getIndexKeys();
        $index_key[$key] = 1;
        $this->_setIndexKeys($index_key);

        return $this->_general->set($key, $value, $lifetime);

    }

    public function get($key){
        return $this->_get($key);
    }
    protected function _get($key){
        return $this->_general->get($key);
    }

    protected function _delete($key){
        $index_key = $this->_getIndexKeys();
        unset($index_key[$key]);
        $this->_setIndexKeys($index_key);

        return $this->_general->delete($key);
    }

    /**
     * 也是通过保存所有key在specialKey中实现的
     * @param string $prefix
     * @return array
     */
    protected function _queryKeys($prefix = ''){
        $index_key = $this->_getIndexKeys();
        $ret = [];
        $c = strlen($prefix);
        foreach ($index_key as $k => $v) {
            if (substr($k, 0, $c) == $prefix) $ret[] = $k;
        }
        return $ret;
    }

    /**
     * 不支持范围删除
     * @param $scope
     * @return bool
     */
    protected function _flush($scope = self::SCOPE_TABLE){
        switch ($scope) {
            case self::SCOPE_TABLE:
                $all_keys = $this->_getIndexKeys();
                $all_keys[self::KEY_INDEX] = 1;
                return $this->_multiDel(array_keys($all_keys));

            default: //不支持DB级别删除，除了table都是all
                break;
        }
        return $this->_general->flush($scope);
    }

    public function multiSet(Array $items, $lifetime = null){
        return $this->_multiSet($items, $lifetime);
    }
    protected function _multiSet(Array $items, $lifetime = null){
        $key_index = $this->_getIndexKeys();
        $keys = array_flip(array_keys($items));
        $key_index = array_replace($key_index, $keys);
        $this->_setIndexKeys($key_index);

        return $this->_general->multiSet($items, $lifetime);
    }

    public function multiGet(Array $keys){
        return $this->_multiGet($keys);
    }
    protected function _multiGet(Array $keys){
        return $this->_general->multiGet($keys);
    }

    protected function _multiDel(Array $keys){
        $index_key = $this->_getIndexKeys();
        foreach ($keys as $k) {
            unset($index_key[$k]);
        }
        $this->_setIndexKeys($index_key);

        return $this->_general->multiDel($keys);

    }

    protected function _switchDb($db){
        $this->_general->switchDb($db);
    }

    protected function _switchTable($table){
        $this->_general->switchTable($table);
    }

    private function _getIndexKeys(){
        $all_keys = $this->_general->get(self::KEY_INDEX);
        if (!is_array($all_keys)) {
            return [];
        }
        return $all_keys;
    }

    private function _setIndexKeys($content){
        return $this->_general->set(self::KEY_INDEX, $content);
    }
} 