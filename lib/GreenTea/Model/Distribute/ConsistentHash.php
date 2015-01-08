<?php
/**
 * @name ConsistentHash.php
 * @desc 见Hash.php
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-31
 * @version 0.01
 * @comment the Hash can emulate ConsistentHash by setting weight
 */

namespace GreenTea\Model\Distribute;


use GreenTea\Model\Distribute;

class ConsistentHash extends Distribute{
    public function getDistributedDriver(Array $factors){
        return null;
    }
} 