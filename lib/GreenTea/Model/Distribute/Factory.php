<?php
/**
 * @name Factory.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-31
 * @version 0.01
 */

namespace GreenTea\Model\Distribute;


class Factory extends \GreenTea\Factory{
    public function getClassname($target){
        return 'GreenTea\Model\Distribute\\' . $target;
    }
} 