<?php
/**
 * @name Handler.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-31
 * @version 0.01
 */

namespace GreenTea\Model\Connector;

class Factory extends \GreenTea\Factory{
    public function getClassname($target){
        return 'GreenTea\Model\Connector\Con' . $target;
    }
} 