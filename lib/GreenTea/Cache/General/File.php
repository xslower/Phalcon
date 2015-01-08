<?php
/**
 * @name File .php
 * @desc
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/11/13 0013
 * @version 0.01
 */
namespace GreenTea\Cache\General;

use GreenTea\Cache\Convertor;
use GreenTea\Cache\General;
use GreenTea\Utility\XArray;

class File extends General{
    protected $_cache_dir;

    public function __construct(Convertor $convertor, Array $options){
        parent::__construct($convertor, $options);
        $this->_cache_dir = XArray::fetchItem($options, 'cache_dir', '/tmp/php/cache');
        $this->_makedir();
    }

    protected function _set($key, $value, $lifetime = null){
        return file_put_contents($this->getPrefixedKey($key), $value);
    }

    protected function _get($key) {
        $path = $this->getPrefixedKey($key);
        if(file_exists($path)){
            return file_get_contents($this->getPrefixedKey($key));
        }else{
            return null;
        }

    }

    protected function _delete($key) {
        return unlink($this->getPrefixedKey($key));
    }

    /**
     * @param string $prefix
     * @return array
     */
    protected function _queryKeys($prefix = '') {
        $files = glob($this->getPrefixedKey($prefix) . '*');
        foreach ($files as &$f) {
            $f = basename($f);
        }
        return $files;
    }

    /**
     * @param string $scope
     * @return bool
     */
    protected function _flush($scope = self::SCOPE_TABLE) {
        $command = 'rm -rf ' . $this->_cache_dir . '/';
        switch($scope){
            case self::SCOPE_TABLE:
                $command .= $this->_db_name . '/' . $this->_table_name . '/*';
                break;
            case self::SCOPE_DB:
                $command .= $this->_db_name . '/*';
                break;
            case self::SCOPE_ALL:
                $command .= '*';
                break;
            default:
                return false;
        }
        exec($command);
        return true;
    }

    protected function _multiSet(Array $items, $lifetime = null) {
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

    protected function _switchDb($dbname) {
        $this->_makedir();
    }

    protected function _switchTable($table) {
        $this->_makedir();
    }

    protected function getPrefixedKey($key) {
        return $this->_getCacheDir() . '/' . $key;
    }

    private function _getCacheDir(){
        return $this->_cache_dir . '/' . $this->_db_name . '/' . $this->_table_name;
    }

    private function _makedir(){
        $path = $this->_getCacheDir();
        if(!is_dir($path)){
            mkdir($path, 0777, true);
        }
    }

//    protected function getOriginKey($key) {
//        return ltrim($key, $this->_db_name . $this->_table_name);
//    }
}