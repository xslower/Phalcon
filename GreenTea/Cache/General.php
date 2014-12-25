<?php
/**
 * @name Backend.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-21
 * @version 0.01
 * TODO 关于OO式使用，需要仔细思考下用户行为才能增加，暂时不支持
 */

namespace GreenTea\Cache;

use GreenTea\Utility\XArray;

abstract class General{

    const SCOPE_ALL = 'all';
    const SCOPE_DB = 'db';
    const SCOPE_TABLE = 'table';

    protected $_convertor;
    protected $_options;
    protected $_lifetime;
    protected $_db_name = '';
    protected $_table_name = '';

    public function __construct(Convertor $convertor, Array $options){
        $this->_convertor = $convertor;
        $this->_options = $options;
        $this->_lifetime = XArray::fetchItem($options, 'lifetime', 1800);
    }

    /**
     * @param  string     $key
     * @return mixed|null
     */
    public function get($key) {
        $value = $this->_get($key);
        if ($value === false) {
            return null;
        }
        $convertor = $this->getConvertor();
        return $convertor->afterRetrieve($value);
    }
    abstract protected function _get($key);

    /**
     * @param  string $key
     * @param  mixed $content
     * @param  integer $lifetime
     * @throws \Exception
     */
    public function set($key, $content, $lifetime = null){
        $convertor = $this->getConvertor();
        if ($lifetime === null) {
            $lifetime = $this->_lifetime;
        }
        return $this->_set($key, $convertor->beforeStore($content), $lifetime);
    }
    abstract protected function _set($key, $value, $lifetime = 0);

    /**
     * @param $key
     * @return boolean
     */
    public function delete($key){
        return $this->_delete($key);
    }
    abstract protected function _delete($key);

    /**
     * @param  string $prefix
     * @return array
     */
    public function queryKeys($prefix = ''){
        return $this->_queryKeys($prefix);
    }
    abstract protected function _queryKeys($prefix = '');

    public function flush($scope = self::SCOPE_TABLE){
        return $this->_flush($scope);
    }
    abstract protected function _flush($scope);

    public function multiSet(Array $items, $lifetime = null){
        if(empty($items)){
            return true;
        }
        $convertor = $this->getConvertor();
        if ($lifetime === null) {
            $lifetime = $this->_lifetime;
        }
        foreach ($items as $k => &$content) {
            $content = $convertor->beforeStore($content);
        }
        return $this->_multiSet($items, $lifetime);
    }
    abstract protected function _multiSet(Array $items, $lifetime = 0);

    public function multiGet(Array $keys){
        $items = $this->_multiGet($keys);

        $convertor = $this->getConvertor();
        foreach ($items as $k => &$content) {
            $content = $convertor->afterRetrieve($content);
        }
        return $items;
    }
    abstract protected function _multiGet(Array $keys);

    public function multiDel(Array $keys){
        return $this->_multiDel($keys);
    }
    abstract protected function _multiDel(Array $keys);

    public function switchDb($dbname){
        $this->_db_name = $dbname;
        $this->_switchDb($dbname);
    }
    abstract protected function _switchDb($dbname);

    public function switchTable($table){
        $this->_table_name = $table;
        $this->_switchTable($table);
    }
    abstract protected function _switchTable($table);

    /**
     * @return \GreenTea\Cache\Convertor
     */
    public function getConvertor(){
        return $this->_convertor;
    }

    //for compatible with phalcon\cache\backend
    public function save($key, $value, $lifetime = null){
        $this->set($key, $value, $lifetime);
    }

} 