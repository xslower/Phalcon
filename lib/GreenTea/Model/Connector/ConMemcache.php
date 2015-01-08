<?php
/**
 * @name Memcache.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-31
 * @version 0.01
 */

namespace GreenTea\Model\Connector;


use GreenTea\Cache\General\Memcache;
use GreenTea\Cache\General\SupportQueryKey;
use GreenTea\Model\Connector;
use GreenTea\Utility\XArray;


class ConMemcache extends Connector{
    const SUPPORT_QUERY_KEY = 'support_query_key';
    /**
     * @param array $options
     * @return \GreenTea\Cache\General
     */
    protected function _connect(Array $options){
        $convertor = new \GreenTea\Cache\Convertor\Json();
        if(XArray::fetchItem($options, self::SUPPORT_QUERY_KEY)){
            $mem = new Memcache($convertor, $options);
            $backend = new SupportQueryKey($mem);
        }else{
            $backend = new Memcache($convertor, $options);
        }
        return $backend;
    }

} 