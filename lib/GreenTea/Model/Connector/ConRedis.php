<?php
/**
 * @name Redis.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-30
 * @version 0.01
 * TODO phalcon的cache层封装的又屎，又简单。考虑自己封装。
 */

namespace GreenTea\Model\Connector;


use GreenTea\Model\Connector;
use GreenTea\Utility\XArray;

class ConRedis extends Connector{

    /**
     * @param array $options
     * @return \Phalcon\Cache\Backend
     */
    protected function _connect(Array $options){
        $convertor = new \GreenTea\Cache\Convertor\Json();
        $driver = new \GreenTea\Cache\General\Redis($convertor, $options);
        return $driver;
    }
}