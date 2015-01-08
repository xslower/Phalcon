<?php
/**
 * @name Connector.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-30
 * @version 0.01
 * TODO do not support retry, it's a ugly function
 */

namespace GreenTea\Model;


use GreenTea\Utility\XArray;

abstract class Connector{
    const LIFE_TIME = 'lifetime';
    const PERSISTENT = 'persistent';

    protected $_instances = array();
    /**
     * @param array $options
     * @return \GreenTea\Cache\General|\Phalcon\Db\Adapter\Pdo
     */
    public function getConnection(Array $options){
        $key = $this->getUniqueKey($options);
        if(isset($this->_instances[$key])){
            return $this->_instances[$key];
        }
        if(!isset($options[self::LIFE_TIME])){
            $options[self::LIFE_TIME] = 3600;
        }
        $cache = $this->_connect($options);
        $this->_instances[$key] = $cache;
        return $cache;
    }

    abstract protected function _connect(Array $options);

    protected function getUniqueKey(Array $options){
        $host = XArray::fetchItem($options, 'host', 'localhost');
        $port = XArray::fetchItem($options, 'port', 'port');
        return $host . $port;
    }
} 