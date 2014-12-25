<?php
/**
 * @name Php.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-10-22
 * @version 0.01
 */

namespace GreenTea\Config\Format;

use GreenTea\Config\Format;

class Php extends Format{
    public function load(){
        $config = require($this->_path);
        $this->init($config);
    }
    public function save(){
        return file_put_contents($this->_path,
            "<?php \nreturn " . var_export($this->toArray(), true) . ";\n");
    }
} 