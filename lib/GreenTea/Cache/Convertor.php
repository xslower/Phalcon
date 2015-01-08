<?php
/**
 * @name Convertor.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-23
 * @version 0.01
 */

namespace GreenTea\Cache;


use GreenTea\Utility\XArray;

abstract class Convertor {

    protected $_options;
    public function __construct(Array $options = []){
        $this->_options = $options;
    }

    abstract public function beforeStore($data);

    abstract public function afterRetrieve($data);
} 