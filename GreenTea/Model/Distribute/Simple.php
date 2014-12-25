<?php
/**
 * @name Simple.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-12
 * @version 0.01
 * TODO 写个无需分发的策略，直接使用第一个host信息
 */

namespace GreenTea\Model\Distribute;


use GreenTea\Model\Distribute;

class Simple extends Distribute{
    public function getDistributedDriver(Array $factors){
        return null;
    }
} 