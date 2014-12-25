<?php
/**
 * @name ConRedisQ.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-23
 * @version 0.01
 */

namespace GreenTea\Model\Connector;

use GreenTea\Model\Connector;

class ConRedisQueue extends Connector{
    protected function _connect(Array $options){
        $convertor = new \GreenTea\Cache\Convertor\Json();
        return new \GreenTea\Cache\Queue\Redis($convertor, $options);
    }
} 