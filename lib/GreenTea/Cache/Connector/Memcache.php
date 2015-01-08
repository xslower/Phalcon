<?php
/**
 * @name Memcache.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-23
 * @version 0.01
 */

namespace GreenTea\Cache\Connector;


use GreenTea\Cache\Connector;
use GreenTea\Utility\XArray;

class Memcache extends Connector{

    public function connect(Array $options){
        $mem = new \Memcache();
        $host = XArray::fetchItem($options, self::HOST, '127.0.0.1');
        $port = XArray::fetchItem($options, self::PORT, 11211);
        $persistent = XArray::fetchItem($options, self::PERSISTENT, false);
        if($persistent){
            $mem->pconnect($host, $port, 3600);
        }else{
            $mem->connect($host, $port, 3600);
        }
        return $mem;
    }
} 