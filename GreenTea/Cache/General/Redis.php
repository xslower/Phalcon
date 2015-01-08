<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2012 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  |          Nikita Vershinin <endeveit@gmail.com>                         |
  +------------------------------------------------------------------------+
*/
namespace GreenTea\Cache\General;

use GreenTea\Cache\Convertor;
use GreenTea\Utility\XArray;
use GreenTea\Cache\General;

class Redis extends General{


    /**
     * @var \Redis
     */
    protected $_redis;

    /**
     * Class constructor.
     *
     * @param \GreenTea\Cache\Convertor $convertor
     * @param  array $options
     * @throws \Exception
     */
    public function __construct(Convertor $convertor, Array $options) {
        parent::__construct($convertor, $options);
        $this->_redis = \GreenTea\Cache\Connector\Redis::getInstance()->connect($options);
    }

    protected function _get($key){
        return $this->_redis->get($key);
    }

    protected function _set($key, $value, $lifetime = 0){
        if($lifetime === 0){
            return $this->_redis->set($key, $value);
        }else{
            return $this->_redis->setex($key, $lifetime, $value);
        }
    }

    /**
     * @param $keys string
     * @return bool
     */
    protected function _delete($key) {
        return $this->_redis->del($key);
    }

    /**
     * redis->keys()据说容易hang住整个服务器，但是返回的是当前table(prefix)下的keys
     * scan会包含当前db所有的key，而且不会返回准确信息
     * @param string $prefix 前缀
     * 只支持*作为通配符 例如'user*'
     * @return array|bool
     */
    protected function _queryKeys($prefix = '') {
        //$this->_redis->getKeys($prefix . '*');
        $it = null; //莫名其妙，这个值只能设null
        $this->_redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY); // retry when we get no keys back
        $arr_keys = $this->_redis->scan($it, $this->_table_name . $prefix . '*', 1000);
        foreach ($arr_keys as &$k) {
            $k = \GreenTea\Utility\String::strip($k, $this->_table_name);
        }
        return $arr_keys;
    }

    /**
     * @param string $scope
     * @return bool
     */
    protected function _flush($scope = self::SCOPE_TABLE) {
        switch($scope){
            case self::SCOPE_ALL:
                return $this->_redis->flushAll();
            case self::SCOPE_DB:
                return $this->_redis->flushDB();
            case self::SCOPE_TABLE:
                $keys = $this->_queryKeys();
                return $this->_multiDel($keys);
            default:
                return false;
        }
    }

    protected function _multiSet(Array $items, $lifetime = 0){
        $this->_redis->mset($items);
        return true;
    }

    protected function _multiGet(Array $keys){
        $ret = $this->_redis->mget($keys);
        $items = [];
        foreach ($ret as $i => $v) {
            $items[$keys[$i]] = $v;
        }
        return $items;
    }

    protected function _multiDel(Array $keys){
        return $this->_redis->del($keys);
    }

    protected function _switchDb($dbname){
        if(!is_int($dbname))
            $dbname = crc32($dbname) % 16 + 1;
        $this->_redis->select($dbname);
    }

    protected function _switchTable($table){
        $this->_redis->setOption(\Redis::OPT_PREFIX, $table);
    }

}
