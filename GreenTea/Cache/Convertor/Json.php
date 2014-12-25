<?php
/**
 * @name Json.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-23
 * @version 0.01
 */

namespace GreenTea\Cache\Convertor;


use GreenTea\Cache\Convertor;

class Json extends Convertor{
    public function beforeStore($data){
        return json_encode($data);
    }
    public function afterRetrieve($data){
        return json_decode($data, true);
    }
} 