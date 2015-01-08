<?php
/**
 * @name Apcu.php
 * @desc
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-16
 * @version 0.01
 */

namespace GreenTea\Cache\General;

use GreenTea\Cache\General;

class Apcu extends General{

    protected function _set($key, $value, $lifetime = null){
        return apc_store($this->getPrefixedKey($key), $value, $lifetime);
    }

    protected function _get($key){
        return apc_fetch($this->getPrefixedKey($key));
    }

    protected function _delete($key){
        return apc_delete($this->getPrefixedKey($key));
    }

    /**
     * 通过APCIterator实现。
     * @param string $prefix
     * @return array
     */
    protected function _queryKeys($prefix = ''){
        $pattern = '/^' . $prefix . '.*';
        $it = new \APCIterator('user', $pattern);
        $keys = [];
        foreach ($it as $k => $v) {
            $keys[] = $k;
        }
        return $keys;
    }

    /**
     * 不支持范围删除
     * @param $scope
     * @return bool
     */
    protected function _flush($scope){
        return apc_clear_cache('user');
    }

    protected function _multiSet(Array $items, $lifetime = null){
        //貌似apc_store可以直接multiSet，但是考虑到增加key的前缀还得循环
        foreach ($items as $key => $content) {
            $this->_set($key, $content, $lifetime);
        }
        return true;
    }

    protected function _multiGet(Array $keys){
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->_get($key);
        }
        return $items;
    }

    protected function _multiDel(Array $keys){
        foreach ($keys as $k) {
            $this->_delete($k);
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