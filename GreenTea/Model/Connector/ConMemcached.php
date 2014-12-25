<?php
/**
 * @name ConMemcached.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-3
 * @version 0.01
 */

namespace GreenTea\Model\Connector;


use GreenTea\Cache\General\Memcached;
use GreenTea\Cache\General\SupportQueryKey;
use GreenTea\Model\Connector;
use GreenTea\Utility\XArray;


class ConMemcached extends Connector{
    const SUPPORT_QUERY_KEY = 'support_query_key';

    public function _connect(Array $options){
        $frontend = new \GreenTea\Cache\Convertor\Json();
        if(XArray::fetchItem($options, self::SUPPORT_QUERY_KEY)){
            $mem = new Memcached($frontend, $options);
            $backend = new SupportQueryKey($mem);
        }else{
            $backend = new Memcached($frontend, $options);
        }

        return $backend;
    }

} 