<?php
/**
 * @name Redis.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-23
 * @version 0.01
 */

namespace GreenTea\Cache\Connector;


use GreenTea\Cache\Connector;
use GreenTea\Utility\XArray;

class Redis extends Connector{

    public function connect(Array $options){
        $redis = new \Redis();
        $host = XArray::fetchItem($options, self::HOST, '127.0.0.1');
        $port = XArray::fetchItem($options, self::PORT, 6379);
        $persistent = XArray::fetchItem($options, self::PERSISTENT);
        if($persistent){
            $redis->pconnect($host, $port, 3600);
        }else{
            $redis->connect($host, $port, 3600);
        }
        return $redis;
    }
} 