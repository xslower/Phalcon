<?php
/**
 * @name None.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/12/15 0015
 * @version 0.01
 */

namespace GreenTea\Cache\Convertor;


use GreenTea\Cache\Convertor;

class None extends Convertor{
    public function beforeStore($data){
        return $data;
    }
    public function afterRetrieve($data){
        return $data;
    }
} 