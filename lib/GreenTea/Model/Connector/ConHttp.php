<?php
/**
 * @name ConHttp.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-5
 * @version 0.01
 */

namespace GreenTea\Model\Connector;


use GreenTea\Model\Connector;
use GreenTea\Utility\XArray;

class ConHttp extends Connector{
    /**
     * @param array $options
     * @return \GreenTea\Cache\General
     */
    protected function _connect(Array $options){
        $convertor = new \GreenTea\Cache\Convertor\Json();
        return new \GreenTea\Cache\General\Http($convertor, $options);


    }

} 