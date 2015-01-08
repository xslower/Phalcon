<?php
/**
 * @name MemcachedHash.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-31
 * @version 0.01
 */

namespace GreenTea\Model\Distribute;


use GreenTea\Model\Distribute;
use GreenTea\Utility\Tools;

class MemcachedHash extends Distribute{
    const CACHE_KEY = 'MemcachedHash-001';
    public function getDistributedDriver(Array $factors){
        $options['servers'] = $this->_nodes;
        $oldKey = Tools::cacheGet(self::CACHE_KEY);
        $newKey = $this->getUniqueKey($this->_nodes);
        if($oldKey != $newKey){
            $options['options']['reset_server'] = true;
            Tools::cacheSet(self::CACHE_KEY, $newKey);
        }
        return $this->_connector->getConnection($options);
    }
} 