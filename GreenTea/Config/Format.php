<?php
/**
 * @name Format.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-22
 * @version 0.01
 */

namespace GreenTea\Config;


use GreenTea\Vector;

abstract class Format extends Vector {
    protected $_path;

    public function __construct($path){
        if(!file_exists($path)){
            throw new \Exception('file is not exist in [' . $path . ']');
        }
        $this->_path = $path;
        $this->load();
    }

    abstract public function load();

    abstract public function save();
} 