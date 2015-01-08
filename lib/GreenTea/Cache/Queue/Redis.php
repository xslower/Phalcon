<?php
/**
 * @name Redis.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-23
 * @version 0.01
 */

namespace GreenTea\Cache\Queue;


use GreenTea\Cache\Convertor;
use GreenTea\Cache\Queue;
use GreenTea\Utility\XArray;

class Redis extends Queue{
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

    protected function _push($key, $item){
        $this->_redis->lPush($key, $item);
    }

    protected function _pop($key){
        return $this->_redis->lPop($key);
    }

    protected function _length($key){
        return $this->_redis->lLen($key);
    }

    protected function _clear($key){
        $this->_redis->lTrim($key, 1, 0);
    }
} 